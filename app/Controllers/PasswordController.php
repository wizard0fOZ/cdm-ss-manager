<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class PasswordController
{
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
