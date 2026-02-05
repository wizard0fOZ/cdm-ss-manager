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

            if ($route->path !== $this->request->path) {
                continue;
            }

            // Always run web middleware first
            $middlewareStack = array_merge(['web'], $route->middleware);

            $this->runMiddlewareStack($middlewareStack, function () use ($route) {
                $this->runHandler($route->handler);
            });

            return;
        }

        $this->response->status(404)->html('404 Not Found');
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
            $controller->$method();
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
                $res->status(419)->html('CSRF token mismatch');
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
