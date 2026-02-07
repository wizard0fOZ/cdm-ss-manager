<?php
namespace App\Controllers;

use App\Core\Auth\Auth;
use App\Core\Http\Response;
use App\Core\Db\Db;
use App\Core\Audit\Audit;

final class AuthController
{
  public function showLogin(): void
  {
    (new Response())->view('auth/login.php');
  }

  public function doLogin(): void
  {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
      (new Response())->view('auth/login.php', ['error' => 'Email and password required']);
      return;
    }

    $emailLower = strtolower($email);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $pdo = Db::pdo();

    $attemptRow = $this->getLoginAttempt($pdo, $emailLower, $ip);
    if ($attemptRow && !empty($attemptRow['locked_until']) && strtotime($attemptRow['locked_until']) > time()) {
      (new Response())->view('auth/login.php', ['error' => 'Too many attempts. Try again later.']);
      return;
    }

    if (!Auth::attempt($email, $password)) {
      $this->recordFailedAttempt($pdo, $emailLower, $ip, $attemptRow);
      (new Response())->view('auth/login.php', ['error' => 'Invalid credentials or account disabled']);
      return;
    }

    $this->clearLoginAttempts($pdo, $emailLower, $ip);
    Audit::log('auth.login.success', 'users', (string)($_SESSION['user_id'] ?? '0'), null, ['ip' => $ip, 'email' => $emailLower]);
    (new Response())->redirect('/dashboard');
  }

  public function logout(): void
  {
    Auth::logout();
    (new Response())->redirect('/login');
  }

  private function getLoginAttempt(\PDO $pdo, string $email, string $ip): ?array
  {
    $stmt = $pdo->prepare('SELECT * FROM login_attempts WHERE email = ? AND ip_address = ? LIMIT 1');
    $stmt->execute([$email, $ip]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  private function recordFailedAttempt(\PDO $pdo, string $email, string $ip, ?array $row): void
  {
    $now = new \DateTime();
    $windowSeconds = 15 * 60;
    $maxAttempts = 5;
    $attempts = 0;

    if ($row) {
      $last = new \DateTime($row['last_attempt_at']);
      if (($now->getTimestamp() - $last->getTimestamp()) <= $windowSeconds) {
        $attempts = (int)$row['attempts'];
      } else {
        $attempts = 0;
      }
      $attempts += 1;
      $lockedUntil = null;
      if ($attempts >= $maxAttempts) {
        $lockedUntil = (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s');
      }
      $stmt = $pdo->prepare('UPDATE login_attempts SET attempts = ?, last_attempt_at = ?, locked_until = ? WHERE id = ?');
      $stmt->execute([$attempts, $now->format('Y-m-d H:i:s'), $lockedUntil, $row['id']]);
      return;
    }

    $attempts = 1;
    $stmt = $pdo->prepare('INSERT INTO login_attempts (email, ip_address, attempts, last_attempt_at) VALUES (?,?,?,?)');
    $stmt->execute([$email, $ip, $attempts, $now->format('Y-m-d H:i:s')]);
  }

  private function clearLoginAttempts(\PDO $pdo, string $email, string $ip): void
  {
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
    $stmt->execute([$email, $ip]);
  }
}
