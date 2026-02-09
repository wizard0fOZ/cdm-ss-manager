<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;

final class LessonsController extends BaseController
{
  private array $statuses = ['DRAFT','PUBLISHED'];

  public function index(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $classId = (int)($request->input('class_id', 0));
    $status = trim((string)$request->input('status', ''));
    $from = $this->normalizeDate($request->input('from', ''));
    $to = $this->normalizeDate($request->input('to', ''));
    $q = trim((string)$request->input('q', ''));

    $pdo = Db::pdo();
    $filters = [];
    $params = [];

    if ($classId > 0) {
      $filters[] = 'lp.class_id = ?';
      $params[] = $classId;
    }
    if ($status !== '') {
      $filters[] = 'lp.status = ?';
      $params[] = $status;
    }
    if ($from) {
      $filters[] = 'lp.session_date >= ?';
      $params[] = $from;
    }
    if ($to) {
      $filters[] = 'lp.session_date <= ?';
      $params[] = $to;
    }
    if ($q !== '') {
      $filters[] = '(lp.title LIKE ? OR lp.description LIKE ? OR lp.content LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    $sql = "SELECT lp.*, c.name AS class_name, s.name AS session_name
            FROM lesson_plans lp
            JOIN classes c ON c.id = lp.class_id
            LEFT JOIN sessions s ON s.id = c.session_id";

    if (!$isAdmin) {
      $sql .= " JOIN class_teacher_assignments cta ON cta.class_id = c.id AND cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE())";
      array_unshift($params, $userId);
    }

    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }

    $sql .= ' ORDER BY lp.session_date DESC, lp.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lessons = $stmt->fetchAll();

    $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);
    $classTeachers = $this->getClassTeachers($pdo, array_column($lessons, 'class_id'));

    (new Response())->view('lessons/index.php', [
      'lessons' => $lessons,
      'classTeachers' => $classTeachers,
      'classes' => $classes,
      'classId' => $classId,
      'status' => $status,
      'from' => $from,
      'to' => $to,
      'q' => $q,
      'statuses' => $this->statuses,
    ]);
  }

  public function create(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);

    (new Response())->view('lessons/create.php', [
      'classes' => $classes,
      'statuses' => $this->statuses,
    ]);
  }

  public function copy(Request $request): void
  {
    $id = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $lesson = $this->getLesson($pdo, $id);
    if (!$lesson) {
      (new Response())->status(404)->html('Lesson not found');
      return;
    }

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, (int)$lesson['class_id'])) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'lesson']);
      return;
    }

    $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);
    $lesson['session_date_display'] = '';
    $lesson['status'] = 'DRAFT';

    (new Response())->view('lessons/create.php', [
      'classes' => $classes,
      'statuses' => $this->statuses,
      'lesson' => $lesson,
      'copyMode' => true,
    ]);
  }

  public function store(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $classId = (int)($_POST['class_id'] ?? 0);
    $sessionDate = $this->parseDate($_POST['session_date'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $status = $_POST['status'] ?? 'DRAFT';

    $errors = $this->validate($classId, $sessionDate, $title, $content, $status);

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, $classId)) {
      $errors[] = 'You do not have access to that class.';
    }

    if ($errors) {
      $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);
      (new Response())->view('lessons/create.php', [
        'errors' => $errors,
        'classes' => $classes,
        'statuses' => $this->statuses,
        'lesson' => $_POST,
      ]);
      return;
    }

    try {
      $stmt = $pdo->prepare('INSERT INTO lesson_plans (class_id, session_date, title, description, content, url, status, created_by, updated_by) VALUES (?,?,?,?,?,?,?,?,?)');
      $stmt->execute([
        $classId,
        $sessionDate,
        $title,
        $description ?: null,
        $content,
        $url ?: null,
        $status,
        $userId,
        $userId,
      ]);
    } catch (\PDOException $e) {
      if ($e->getCode() === '23000') {
        $errors[] = 'A lesson already exists for that class and date.';
      } else {
        throw $e;
      }

      $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);
      (new Response())->view('lessons/create.php', [
        'errors' => $errors,
        'classes' => $classes,
        'statuses' => $this->statuses,
        'lesson' => $_POST,
      ]);
      return;
    }

    Flash::set('success', 'Lesson plan created.');
    (new Response())->redirect('/lessons');
  }

  public function show(Request $request): void
  {
    $id = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $lesson = $this->getLesson($pdo, $id);
    if (!$lesson) {
      (new Response())->status(404)->html('Lesson not found');
      return;
    }

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, (int)$lesson['class_id'])) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'lesson']);
      return;
    }

    $teachers = $this->getClassTeachers($pdo, [(int)($lesson['class_id'] ?? 0)]);
    (new Response())->view('lessons/show.php', [
      'lesson' => $lesson,
      'classTeachers' => $teachers[(int)($lesson['class_id'] ?? 0)] ?? [],
    ]);
  }

  public function print(Request $request): void
  {
    $id = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $lesson = $this->getLesson($pdo, $id);
    if (!$lesson) {
      (new Response())->status(404)->html('Lesson not found');
      return;
    }

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, (int)$lesson['class_id'])) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'lesson']);
      return;
    }

    (new Response())->view('lessons/print.php', [
      'lesson' => $lesson,
    ]);
  }

  public function edit(Request $request): void
  {
    $id = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $lesson = $this->getLesson($pdo, $id);
    if (!$lesson) {
      (new Response())->status(404)->html('Lesson not found');
      return;
    }

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, (int)$lesson['class_id'])) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'lesson']);
      return;
    }

    $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);

    $lesson['session_date_display'] = $this->formatDate($lesson['session_date'] ?? null);

    (new Response())->view('lessons/edit.php', [
      'lesson' => $lesson,
      'classes' => $classes,
      'statuses' => $this->statuses,
    ]);
  }

  public function update(Request $request): void
  {
    $id = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $lesson = $this->getLesson($pdo, $id);
    if (!$lesson) {
      (new Response())->status(404)->html('Lesson not found');
      return;
    }

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, (int)$lesson['class_id'])) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'lesson']);
      return;
    }

    $classId = (int)($_POST['class_id'] ?? 0);
    $sessionDate = $this->parseDate($_POST['session_date'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $status = $_POST['status'] ?? 'DRAFT';

    $errors = $this->validate($classId, $sessionDate, $title, $content, $status);

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, $classId)) {
      $errors[] = 'You do not have access to that class.';
    }

    if ($errors) {
      $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);
      (new Response())->view('lessons/edit.php', [
        'errors' => $errors,
        'classes' => $classes,
        'statuses' => $this->statuses,
        'lesson' => array_merge($lesson, $_POST),
      ]);
      return;
    }

    try {
      $stmt = $pdo->prepare('UPDATE lesson_plans SET class_id=?, session_date=?, title=?, description=?, content=?, url=?, status=?, updated_by=? WHERE id=?');
      $stmt->execute([
        $classId,
        $sessionDate,
        $title,
        $description ?: null,
        $content,
        $url ?: null,
        $status,
        $userId,
        $id,
      ]);
    } catch (\PDOException $e) {
      if ($e->getCode() === '23000') {
        $errors[] = 'A lesson already exists for that class and date.';
      } else {
        throw $e;
      }

      $classes = $this->getAvailableClasses($pdo, $userId, $isAdmin);
      (new Response())->view('lessons/edit.php', [
        'errors' => $errors,
        'classes' => $classes,
        'statuses' => $this->statuses,
        'lesson' => array_merge($lesson, $_POST),
      ]);
      return;
    }

    Flash::set('success', 'Lesson plan updated.');
    (new Response())->redirect('/lessons');
  }

  private function validate(int $classId, ?string $sessionDate, string $title, string $content, string $status): array
  {
    $errors = [];
    if ($classId <= 0) $errors[] = 'Class is required.';
    if (!$sessionDate) $errors[] = 'Session date is required.';
    if ($title === '') $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';
    if (!in_array($status, $this->statuses, true)) $errors[] = 'Status is invalid.';
    return $errors;
  }

  private function getLesson(\PDO $pdo, int $id): ?array
  {
    $stmt = $pdo->prepare('SELECT lp.*, c.name AS class_name, s.name AS session_name FROM lesson_plans lp JOIN classes c ON c.id = lp.class_id LEFT JOIN sessions s ON s.id = c.session_id WHERE lp.id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  private function getAvailableClasses(\PDO $pdo, int $userId, bool $isAdmin): array
  {
    if ($isAdmin) {
      return $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
    }

    $stmt = $pdo->prepare('SELECT c.id, c.name FROM class_teacher_assignments cta JOIN classes c ON c.id = cta.class_id WHERE cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE()) ORDER BY c.name ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }

  private function getClassTeachers(\PDO $pdo, array $classIds): array
  {
    $ids = array_filter(array_map('intval', $classIds));
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT cta.class_id, cta.assignment_role, u.full_name
      FROM class_teacher_assignments cta
      JOIN users u ON u.id = cta.user_id
      WHERE cta.class_id IN ($placeholders) AND (cta.end_date IS NULL OR cta.end_date >= CURDATE())
      ORDER BY FIELD(cta.assignment_role, 'MAIN', 'ASSISTANT'), u.full_name ASC");
    $stmt->execute($ids);
    $map = [];
    foreach ($stmt->fetchAll() as $row) {
      $map[$row['class_id']][] = $row;
    }
    return $map;
  }

  private function canAccessClass(\PDO $pdo, int $userId, int $classId): bool
  {
    $stmt = $pdo->prepare('SELECT 1 FROM class_teacher_assignments WHERE user_id = ? AND class_id = ? AND (end_date IS NULL OR end_date >= CURDATE()) LIMIT 1');
    $stmt->execute([$userId, $classId]);
    return (bool)$stmt->fetchColumn();
  }

}
