<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class PasswordController
{
  public function forgot(): void
  {
    (new Response())->view('auth/forgot_password.php');
  }

  public function sendReset(Request $request): void
  {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $message = 'If the account exists, a reset link has been generated.';
    $resetLink = null;

    if ($email !== '') {
      $pdo = Db::pdo();
      $stmt = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1');
      $stmt->execute([$email]);
      $userId = (int)$stmt->fetchColumn();
      if ($userId > 0) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)')
          ->execute([$userId, $token, $expiresAt]);
        $resetLink = '/password/reset?token=' . urlencode($token);
      }
    }

    (new Response())->view('auth/forgot_password.php', [
      'success' => $message,
      'resetLink' => $resetLink,
    ]);
  }

  public function reset(Request $request): void
  {
    $token = trim((string)$request->input('token', ''));
    if ($token === '') {
      (new Response())->view('auth/reset_password.php', ['error' => 'Invalid or missing token.']);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT id, expires_at, used_at FROM password_resets WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row || !empty($row['used_at']) || strtotime($row['expires_at']) < time()) {
      (new Response())->view('auth/reset_password.php', ['error' => 'Reset token has expired or is invalid.']);
      return;
    }

    (new Response())->view('auth/reset_password.php', ['token' => $token]);
  }

  public function updateReset(Request $request): void
  {
    $token = trim((string)($_POST['token'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');

    $errors = [];
    if ($token === '') $errors[] = 'Reset token is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if ($errors) {
      (new Response())->view('auth/reset_password.php', ['errors' => $errors, 'token' => $token]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT user_id, expires_at, used_at FROM password_resets WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row || !empty($row['used_at']) || strtotime($row['expires_at']) < time()) {
      (new Response())->view('auth/reset_password.php', ['error' => 'Reset token has expired or is invalid.']);
      return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
      $pdo->prepare('UPDATE users SET password_hash=?, must_change_password=0 WHERE id=?')->execute([$hash, (int)$row['user_id']]);
      $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token = ?')->execute([$token]);
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->view('auth/reset_password.php', [
      'success' => 'Password updated. You can sign in now.',
    ]);
  }

  public function show(): void
  {
    (new Response())->view('auth/change_password.php');
  }

  public function update(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
      (new Response())->redirect('/login');
      return;
    }

    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');

    $errors = [];
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if ($errors) {
      (new Response())->view('auth/change_password.php', ['errors' => $errors]);
      return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE users SET password_hash=?, must_change_password=0 WHERE id=?');
    $stmt->execute([$hash, $userId]);

    $_SESSION['must_change_password'] = false;

    (new Response())->redirect('/dashboard');
  }
}
