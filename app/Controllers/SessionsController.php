<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class SessionsController
{
  public function index(): void
  {
    $pdo = Db::pdo();
    $sessions = $pdo->query('SELECT * FROM sessions ORDER BY sort_order ASC')->fetchAll();

    (new Response())->view('sessions/index.php', [
      'sessions' => $sessions,
    ]);
  }

  public function create(): void
  {
    (new Response())->view('sessions/create.php');
  }

  public function store(): void
  {
    $name = trim($_POST['name'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 1);

    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!$this->isTime($startTime) || !$this->isTime($endTime)) $errors[] = 'Start and end time are required.';

    if ($errors) {
      (new Response())->view('sessions/create.php', ['errors' => $errors]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO sessions (name, start_time, end_time, sort_order) VALUES (?,?,?,?)');
    $stmt->execute([$name, $startTime, $endTime, $sortOrder]);

    (new Response())->redirect('/sessions');
  }

  public function edit(Request $request): void
  {
    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM sessions WHERE id = ?');
    $stmt->execute([$id]);
    $session = $stmt->fetch();

    if (!$session) {
      (new Response())->status(404)->html('Session not found');
      return;
    }

    (new Response())->view('sessions/edit.php', ['session' => $session]);
  }

  public function update(Request $request): void
  {
    $id = (int)$request->param('id');
    $name = trim($_POST['name'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 1);

    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!$this->isTime($startTime) || !$this->isTime($endTime)) $errors[] = 'Start and end time are required.';

    if ($errors) {
      (new Response())->view('sessions/edit.php', [
        'errors' => $errors,
        'session' => ['id' => $id, 'name' => $name, 'start_time' => $startTime, 'end_time' => $endTime, 'sort_order' => $sortOrder],
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE sessions SET name=?, start_time=?, end_time=?, sort_order=? WHERE id=?');
    $stmt->execute([$name, $startTime, $endTime, $sortOrder, $id]);

    (new Response())->redirect('/sessions');
  }

  private function isTime(string $value): bool
  {
    return (bool)preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value);
  }
}
