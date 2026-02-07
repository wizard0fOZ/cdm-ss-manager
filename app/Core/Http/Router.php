<?php
declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\Middleware;
use App\Core\Rbac\Rbac;

final class Router
{
    private Request $request;
    private Response $response;
    private Middleware $middleware;

    /** @var RouteDef[] */
    private array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->middleware = new Middleware();
        $this->registerDefaultMiddleware();
    }

    /* ------------------------------------------------------------
     | Route registration
     * ------------------------------------------------------------ */

    public function get(string $path, mixed $handler): RouteDef
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): RouteDef
    {
        return $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, mixed $handler): RouteDef
    {
        $route = new RouteDef($method, $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    /* ------------------------------------------------------------
     | Dispatch
     * ------------------------------------------------------------ */

    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route->method !== $this->request->method) {
                continue;
            }

            $params = [];
            if (!$this->matchPath($route->path, $this->request->path, $params)) {
                continue;
            }

            $this->request->params = $params;

            // Always run web middleware first
            $middlewareStack = array_merge(['web'], $route->middleware);
            if ($this->request->method === 'POST' && !in_array('csrf', $middlewareStack, true)) {
                $middlewareStack[] = 'csrf';
            }

            $this->runMiddlewareStack($middlewareStack, function () use ($route) {
                $this->runHandler($route->handler);
            });

            return;
        }

        $this->response->status(404)->view('errors/error.php', [
            'title' => 'Page not found',
            'message' => 'We could not find the page you were looking for.',
            'details' => 'Route not found: ' . $this->request->path,
        ]);
    }

    private function matchPath(string $routePath, string $requestPath, array &$params): bool
    {
        if ($routePath === $requestPath) {
            return true;
        }

        if (!str_contains($routePath, '{')) {
            return false;
        }

        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        if (!$pattern) {
            return false;
        }

        $pattern = '#^' . $pattern . '$#';
        if (!preg_match($pattern, $requestPath, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return true;
    }

    /* ------------------------------------------------------------
     | Middleware execution
     * ------------------------------------------------------------ */

    private function runMiddlewareStack(array $stack, callable $final): void
    {
        $runner = function (int $index) use (&$runner, $stack, $final) {
            if (!isset($stack[$index])) {
                $final();
                return;
            }

            $name = $stack[$index];
            $params = [];

            if (str_contains($name, ':')) {
                [$name, $paramStr] = explode(':', $name, 2);
                $params = explode(',', $paramStr);
            }

            $middleware = $this->middleware->get($name);

            $middleware(
                $this->request,
                $this->response,
                fn () => $runner($index + 1),
                $params
            );
        };

        $runner(0);
    }

    /* ------------------------------------------------------------
     | Handler execution
     * ------------------------------------------------------------ */

    private function runHandler(mixed $handler): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $ref = new \ReflectionMethod($controller, $method);
            $count = $ref->getNumberOfParameters();

            if ($count >= 2) {
                $controller->$method($this->request, $this->response);
            } elseif ($count === 1) {
                $controller->$method($this->request);
            } else {
                $controller->$method();
            }
            return;
        }

        if (is_callable($handler)) {
            $handler($this->request, $this->response);
            return;
        }

        throw new \RuntimeException('Invalid route handler');
    }

    /* ------------------------------------------------------------
     | Default middleware
     * ------------------------------------------------------------ */

    private function registerDefaultMiddleware(): void
    {
        // web: start session + CSRF seed
        $this->middleware->register('web', function (
            Request $req,
            Response $res,
            callable $next
        ) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            if (empty($_SESSION['_csrf'])) {
                $_SESSION['_csrf'] = bin2hex(random_bytes(32));
            }

            // Security headers (baseline)
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('Referrer-Policy: no-referrer');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://unpkg.com; font-src 'self' data: https://fonts.gstatic.com; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

            // Session idle timeout (1 hour)
            $timeout = 60 * 60;
            $now = time();
            if (!empty($_SESSION['user_id'])) {
                $last = (int)($_SESSION['_last_activity'] ?? 0);
                if ($last > 0 && ($now - $last) > $timeout) {
                    \App\Core\Support\Flash::set('error', 'Session expired. Please sign in again.');
                    \App\Core\Auth\Auth::logout();
                    $res->redirect('/login');
                    return;
                }
                $_SESSION['_last_activity'] = $now;
            }

            // Maintenance mode (system_settings: maintenance_mode, maintenance_message)
            try {
                $mode = null;
                $message = null;
                $pdo = \App\Core\Db\Db::pdo();
                $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN (?, ?)');
                $stmt->execute(['maintenance_mode', 'maintenance_message']);
                $rows = $stmt->fetchAll();
                foreach ($rows as $row) {
                    if ($row['setting_key'] === 'maintenance_mode') $mode = strtoupper(trim((string)$row['setting_value']));
                    if ($row['setting_key'] === 'maintenance_message') $message = (string)$row['setting_value'];
                }

                $path = $req->path ?? '';
                $isLogin = $path === '/login';
                $isLogout = $path === '/logout';
                $isHealth = $path === '/health';

                if ($mode && $mode !== 'OFF' && !$isHealth) {
                    $userId = (int)($_SESSION['user_id'] ?? 0);
                    $isSysAdmin = false;
                    if ($userId > 0) {
                        $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code = ? LIMIT 1');
                        $stmt->execute([$userId, 'SYSADMIN']);
                        $isSysAdmin = (bool)$stmt->fetchColumn();
                    }

                    if (!$isSysAdmin) {
                        $apply = false;
                        if ($mode === 'BOTH') $apply = true;
                        if ($mode === 'PUBLIC' && $userId <= 0) $apply = true;
                        if ($mode === 'STAFF' && $userId > 0) $apply = true;

                        if ($apply && !$isLogin && !$isLogout) {
                            $res->status(503)->view('errors/maintenance.php', [
                                'message' => $message ?: 'We are currently performing maintenance. Please check back shortly.',
                            ]);
                            return;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Fail open on maintenance checks
            }

            $next();
        });

        // auth: must be logged in
        $this->middleware->register('auth', function (
            Request $req,
            Response $res,
            callable $next
        ) {
            if (!isset($_SESSION['user_id'])) {
                $res->redirect('/login');
                return;
            }

            if (!empty($_SESSION['must_change_password']) && $req->path !== '/password/change' && $req->path !== '/logout') {
                $res->redirect('/password/change');
                return;
            }

            $next();
        });

        // csrf: POST protection
        $this->middleware->register('csrf', function (
            Request $req,
            Response $res,
            callable $next
        ) {
            if ($req->method !== 'POST') {
                $next();
                return;
            }

            $token = $_POST['_csrf'] ?? '';
            $sessionToken = $_SESSION['_csrf'] ?? '';

            if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
                $res->status(419)->view('errors/error.php', [
                    'title' => 'Session expired',
                    'message' => 'Your session has expired. Please refresh and try again.',
                    'details' => 'CSRF token mismatch',
                ]);
                return;
            }

            $next();
        });

        // perm: RBAC permission check
        $this->middleware->register('perm', function (
            Request $req,
            Response $res,
            callable $next,
            array $params
        ) {
            $perm = $params[0] ?? null;
            if (!$perm) {
                $res->status(500)->html('Permission middleware missing code');
                return;
            }

            $userId = (int)($_SESSION['user_id'] ?? 0);
            if ($userId <= 0) {
                $res->redirect('/login');
                return;
            }

            $rbac = new Rbac();
            if (!$rbac->can($userId, $perm)) {
                $res->status(403)->view('errors/403.php', ['code' => $perm]);
                return;
            }

            $next();
        });
    }
}

/* ------------------------------------------------------------
 | Route Definition
 * ------------------------------------------------------------ */

final class RouteDef
{
    public array $middleware = [];

    public readonly string $method;
    public readonly string $path;
    public mixed $handler;

    public function __construct(string $method, string $path, mixed $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function middleware(string ...$names): self
    {
        foreach ($names as $n) {
            $this->middleware[] = $n;
        }
        return $this;
    }
}
