<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Rbac\Rbac;
use App\Core\Audit\Audit;

final class ClassesController
{
  private array $programs = ['ENGLISH','KUBM','MANDARIN','TAMIL','RCIC','CONFIRMANDS'];
  private array $streams = ['PAUL','PETER','SINGLE'];
  private array $statuses = ['DRAFT','ACTIVE','INACTIVE'];

  public function index(Request $request): void
  {
    if (!$this->guard('classes.view')) return;
    $pdo = Db::pdo();
    $teacherId = (int)$request->input('teacher_id', 0);

    $filters = [];
    $params = [];
    if ($teacherId > 0) {
      $filters[] = 'c.id IN (SELECT class_id FROM class_teacher_assignments WHERE user_id = ? AND (end_date IS NULL OR end_date >= CURDATE()))';
      $params[] = $teacherId;
    }

    $sql = 'SELECT c.*, ay.label AS academic_year_label, s.name AS session_name FROM classes c LEFT JOIN academic_years ay ON ay.id = c.academic_year_id LEFT JOIN sessions s ON s.id = c.session_id';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY c.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $classes = $stmt->fetchAll();

    $assignments = [];
    if ($classes) {
      $classIds = array_map('intval', array_column($classes, 'id'));
      $placeholders = implode(',', array_fill(0, count($classIds), '?'));
      $assignStmt = $pdo->prepare("SELECT cta.class_id, cta.assignment_role, u.full_name
        FROM class_teacher_assignments cta
        JOIN users u ON u.id = cta.user_id
        WHERE cta.class_id IN ($placeholders) AND (cta.end_date IS NULL OR cta.end_date >= CURDATE())
        ORDER BY FIELD(cta.assignment_role, 'MAIN', 'ASSISTANT'), u.full_name ASC");
      $assignStmt->execute($classIds);
      foreach ($assignStmt->fetchAll() as $row) {
        $assignments[$row['class_id']][] = $row;
      }
    }

    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();
    $teachers = $this->getTeachers($pdo);

    (new Response())->view('classes/index.php', [
      'classes' => $classes,
      'years' => $years,
      'sessions' => $sessions,
      'teachers' => $teachers,
      'teacherId' => $teacherId,
      'assignments' => $assignments,
    ]);
  }

  public function bulk(): void
  {
    if (!$this->guard('classes.manage')) return;
    $ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
    $action = $_POST['bulk_action'] ?? '';

    if (!$ids || $action === '') {
      (new Response())->redirect('/classes');
      return;
    }

    $pdo = Db::pdo();

    if ($action === 'set_status') {
      $status = $_POST['status'] ?? '';
      if (!in_array($status, $this->statuses, true)) {
        (new Response())->redirect('/classes');
        return;
      }
      $in = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $pdo->prepare("UPDATE classes SET status=? WHERE id IN ($in)");
      $stmt->execute(array_merge([$status], $ids));
      Audit::log('classes.bulk_status', 'classes', implode(',', $ids), null, ['status' => $status]);
      (new Response())->redirect('/classes');
      return;
    }

    if ($action === 'set_session') {
      $sessionId = (int)($_POST['session_id'] ?? 0);
      if ($sessionId <= 0) {
        (new Response())->redirect('/classes');
        return;
      }
      $in = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $pdo->prepare("UPDATE classes SET session_id=? WHERE id IN ($in)");
      $stmt->execute(array_merge([$sessionId], $ids));
      Audit::log('classes.bulk_session', 'classes', implode(',', $ids), null, ['session_id' => $sessionId]);
      (new Response())->redirect('/classes');
      return;
    }

    if ($action === 'set_academic_year') {
      $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
      if ($academicYearId <= 0) {
        (new Response())->redirect('/classes');
        return;
      }
      $in = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $pdo->prepare("UPDATE classes SET academic_year_id=? WHERE id IN ($in)");
      $stmt->execute(array_merge([$academicYearId], $ids));
      Audit::log('classes.bulk_year', 'classes', implode(',', $ids), null, ['academic_year_id' => $academicYearId]);
      (new Response())->redirect('/classes');
      return;
    }

    (new Response())->redirect('/classes');
  }

  public function create(): void
  {
    if (!$this->guard('classes.manage')) return;
    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();
    $teachers = $this->getTeachers($pdo);

    (new Response())->view('classes/create.php', [
      'years' => $years,
      'sessions' => $sessions,
      'teachers' => $teachers,
      'assignments' => [],
      'programs' => $this->programs,
      'streams' => $this->streams,
      'statuses' => $this->statuses,
    ]);
  }

  public function store(): void
  {
    if (!$this->guard('classes.manage')) return;
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
      $teachers = $this->getTeachers($pdo);

      (new Response())->view('classes/create.php', [
        'errors' => $errors,
        'years' => $years,
        'sessions' => $sessions,
        'teachers' => $teachers,
        'assignments' => [],
        'programs' => $this->programs,
        'streams' => $this->streams,
        'statuses' => $this->statuses,
        'class' => $_POST,
      ]);
      return;
    }

    $pdo = Db::pdo();
    $teacherIds = $_POST['teacher_id'] ?? [];
    $teacherRoles = $_POST['teacher_role'] ?? [];

    $pdo->beginTransaction();
    try {
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
      $classId = (int)$pdo->lastInsertId();
      $this->syncAssignments($pdo, $classId, $teacherIds, $teacherRoles);
      $pdo->commit();
      Audit::log('classes.create', 'classes', (string)$classId, null, [
        'name' => $name,
        'program' => $program,
        'session_id' => $sessionId,
      ]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/classes');
  }

  public function edit(Request $request): void
  {
    if (!$this->guard('classes.manage')) return;
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
    $teachers = $this->getTeachers($pdo);
    $assignments = $this->getAssignments($pdo, $id);

    (new Response())->view('classes/edit.php', [
      'class' => $class,
      'years' => $years,
      'sessions' => $sessions,
      'teachers' => $teachers,
      'assignments' => $assignments,
      'programs' => $this->programs,
      'streams' => $this->streams,
      'statuses' => $this->statuses,
    ]);
  }

  public function update(Request $request): void
  {
    if (!$this->guard('classes.manage')) return;
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
      $teachers = $this->getTeachers($pdo);

      (new Response())->view('classes/edit.php', [
        'errors' => $errors,
        'years' => $years,
        'sessions' => $sessions,
        'teachers' => $teachers,
        'assignments' => [],
        'programs' => $this->programs,
        'streams' => $this->streams,
        'statuses' => $this->statuses,
        'class' => array_merge(['id' => $id], $_POST),
      ]);
      return;
    }

    $pdo = Db::pdo();
    $before = $pdo->prepare('SELECT * FROM classes WHERE id = ?');
    $before->execute([$id]);
    $beforeRow = $before->fetch() ?: null;
    $teacherIds = $_POST['teacher_id'] ?? [];
    $teacherRoles = $_POST['teacher_role'] ?? [];

    $pdo->beginTransaction();
    try {
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
      $this->syncAssignments($pdo, $id, $teacherIds, $teacherRoles);
      $pdo->commit();
      Audit::log('classes.update', 'classes', (string)$id, $beforeRow ?: null, [
        'name' => $name,
        'program' => $program,
        'session_id' => $sessionId,
      ]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/classes');
  }

  private function getTeachers(\PDO $pdo): array
  {
    $stmt = $pdo->prepare("SELECT u.id, u.full_name, u.email
      FROM users u
      JOIN user_roles ur ON ur.user_id = u.id
      JOIN roles r ON r.id = ur.role_id
      WHERE r.code = 'TEACHER' AND u.status = 'ACTIVE'
      ORDER BY u.full_name ASC");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  private function getAssignments(\PDO $pdo, int $classId): array
  {
    $stmt = $pdo->prepare("SELECT cta.user_id, cta.assignment_role, u.full_name, u.email
      FROM class_teacher_assignments cta
      JOIN users u ON u.id = cta.user_id
      WHERE cta.class_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE())
      ORDER BY FIELD(cta.assignment_role, 'MAIN', 'ASSISTANT'), u.full_name ASC");
    $stmt->execute([$classId]);
    return $stmt->fetchAll();
  }

  private function syncAssignments(\PDO $pdo, int $classId, array $teacherIds, array $teacherRoles): void
  {
    $pdo->prepare('DELETE FROM class_teacher_assignments WHERE class_id = ?')->execute([$classId]);
    if (!$teacherIds) return;

    $seen = [];
    $insert = $pdo->prepare('INSERT INTO class_teacher_assignments (class_id, user_id, assignment_role, start_date) VALUES (?,?,?,?)');
    foreach ($teacherIds as $idx => $teacherIdRaw) {
      $teacherId = (int)$teacherIdRaw;
      if ($teacherId <= 0 || isset($seen[$teacherId])) {
        continue;
      }
      $role = strtoupper(trim($teacherRoles[$idx] ?? 'ASSISTANT'));
      if (!in_array($role, ['MAIN','ASSISTANT'], true)) {
        $role = 'ASSISTANT';
      }
      $insert->execute([$classId, $teacherId, $role, date('Y-m-d')]);
      $seen[$teacherId] = true;
    }
  }

  private function guard(string $permission): bool
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
      (new Response())->redirect('/login');
      return false;
    }
    $rbac = new Rbac();
    if (!$rbac->can($userId, $permission)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => $permission]);
      return false;
    }
    return true;
  }
}
