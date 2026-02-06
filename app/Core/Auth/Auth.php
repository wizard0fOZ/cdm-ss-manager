<?php
namespace App\Core\Auth;

use App\Core\Db\Db;

final class Auth
{
  public static function attempt(string $email, string $password): bool
  {
    $pdo = Db::pdo();

    $stmt = $pdo->prepare("SELECT id, password_hash, status, must_change_password FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if (!$u) return false;
    if (($u['status'] ?? '') !== 'ACTIVE') return false;

    if (!password_verify($password, $u['password_hash'])) return false;

    // session
    $_SESSION['user_id'] = (int)$u['id'];
    $_SESSION['must_change_password'] = (int)($u['must_change_password'] ?? 0) === 1;

    // update last_login_at (nice-to-have)
    $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$_SESSION['user_id']]);

    return true;
  }

  public static function logout(): void
  {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"], (bool)$params["secure"], (bool)$params["httponly"]
      );
    }

    session_destroy();
  }
}
