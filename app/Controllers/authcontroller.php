<?php
namespace App\Controllers;

use App\Core\Auth\Auth;
use App\Core\Http\Response;

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

    if (!Auth::attempt($email, $password)) {
      (new Response())->view('auth/login.php', ['error' => 'Invalid credentials or account disabled']);
      return;
    }

    (new Response())->redirect('/dashboard');
  }

  public function logout(): void
  {
    Auth::logout();
    (new Response())->redirect('/login');
  }
}
