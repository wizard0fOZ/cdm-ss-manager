<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class ClassesController
{
  private array $programs = ['ENGLISH','KUBM','MANDARIN','TAMIL','RCIC','CONFIRMANDS'];
  private array $streams = ['PAUL','PETER','SINGLE'];
  private array $statuses = ['DRAFT','ACTIVE','INACTIVE'];

  public function index(): void
  {
    $pdo = Db::pdo();
    $classes = $pdo->query('SELECT c.*, ay.label AS academic_year_label, s.name AS session_name FROM classes c LEFT JOIN academic_years ay ON ay.id = c.academic_year_id LEFT JOIN sessions s ON s.id = c.session_id ORDER BY c.created_at DESC')->fetchAll();

    (new Response())->view('classes/index.php', [
      'classes' => $classes,
    ]);
  }

  public function create(): void
  {
    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();

    (new Response())->view('classes/create.php', [
      'years' => $years,
      'sessions' => $sessions,
      'programs' => $this->programs,
      'streams' => $this->streams,
      'statuses' => $this->statuses,
    ]);
  }

  public function store(): void
  {
    $name = trim($_POST['name'] ?? '');
    $program = $_POST['program'] ?? null;
    $gradeLevel = $_POST['grade_level'] ?? null;
    $stream = $_POST['stream'] ?? 'SINGLE';
    $room = trim($_POST['room'] ?? '');
    $sessionId = (int)($_POST['session_id'] ?? 0);
    $status = $_POST['status'] ?? 'DRAFT';
    $maxStudents = $_POST['max_students'] ?? null;
    $academicYearId = (int)($_POST['academic_year_id'] ?? 0);

    $errors = [];
    if ($name === '') $errors[] = 'Class name is required.';
    if (!in_array($program, $this->programs, true)) $errors[] = 'Program is required.';
    if (!in_array($stream, $this->streams, true)) $errors[] = 'Stream is required.';
    if ($sessionId <= 0) $errors[] = 'Session is required.';
    if (!in_array($status, $this->statuses, true)) $errors[] = 'Status is required.';

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
      $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();

      (new Response())->view('classes/create.php', [
        'errors' => $errors,
        'years' => $years,
        'sessions' => $sessions,
        'programs' => $this->programs,
        'streams' => $this->streams,
        'statuses' => $this->statuses,
        'class' => $_POST,
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO classes (academic_year_id, name, program, grade_level, stream, room, session_id, status, max_students) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
      $academicYearId > 0 ? $academicYearId : null,
      $name,
      $program,
      $gradeLevel !== '' ? $gradeLevel : null,
      $stream,
      $room !== '' ? $room : null,
      $sessionId,
      $status,
      $maxStudents !== '' ? $maxStudents : null,
    ]);

    (new Response())->redirect('/classes');
  }

  public function edit(Request $request): void
  {
    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM classes WHERE id = ?');
    $stmt->execute([$id]);
    $class = $stmt->fetch();

    if (!$class) {
      (new Response())->status(404)->html('Class not found');
      return;
    }

    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();

    (new Response())->view('classes/edit.php', [
      'class' => $class,
      'years' => $years,
      'sessions' => $sessions,
      'programs' => $this->programs,
      'streams' => $this->streams,
      'statuses' => $this->statuses,
    ]);
  }

  public function update(Request $request): void
  {
    $id = (int)$request->param('id');
    $name = trim($_POST['name'] ?? '');
    $program = $_POST['program'] ?? null;
    $gradeLevel = $_POST['grade_level'] ?? null;
    $stream = $_POST['stream'] ?? 'SINGLE';
    $room = trim($_POST['room'] ?? '');
    $sessionId = (int)($_POST['session_id'] ?? 0);
    $status = $_POST['status'] ?? 'DRAFT';
    $maxStudents = $_POST['max_students'] ?? null;
    $academicYearId = (int)($_POST['academic_year_id'] ?? 0);

    $errors = [];
    if ($name === '') $errors[] = 'Class name is required.';
    if (!in_array($program, $this->programs, true)) $errors[] = 'Program is required.';
    if (!in_array($stream, $this->streams, true)) $errors[] = 'Stream is required.';
    if ($sessionId <= 0) $errors[] = 'Session is required.';
    if (!in_array($status, $this->statuses, true)) $errors[] = 'Status is required.';

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
      $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();

      (new Response())->view('classes/edit.php', [
        'errors' => $errors,
        'years' => $years,
        'sessions' => $sessions,
        'programs' => $this->programs,
        'streams' => $this->streams,
        'statuses' => $this->statuses,
        'class' => array_merge(['id' => $id], $_POST),
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE classes SET academic_year_id=?, name=?, program=?, grade_level=?, stream=?, room=?, session_id=?, status=?, max_students=? WHERE id=?');
    $stmt->execute([
      $academicYearId > 0 ? $academicYearId : null,
      $name,
      $program,
      $gradeLevel !== '' ? $gradeLevel : null,
      $stream,
      $room !== '' ? $room : null,
      $sessionId,
      $status,
      $maxStudents !== '' ? $maxStudents : null,
      $id,
    ]);

    (new Response())->redirect('/classes');
  }
}
