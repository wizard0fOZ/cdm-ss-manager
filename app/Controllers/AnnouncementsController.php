<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;

final class AnnouncementsController
{
  public function index(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $scope = trim((string)$request->input('scope', ''));
    $classFilter = (int)$request->input('class_id', 0);
    $statusFilter = trim((string)$request->input('status', ''));
    $q = trim((string)$request->input('q', ''));
    $showExpired = (int)$request->input('show_expired', 0) === 1;

    $filters = [];
    $params = [];

    $pdo = Db::pdo();
    $classes = $isAdmin
      ? $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll()
      : $this->getTeacherClasses($pdo, $userId);

    if (!$isAdmin && $classFilter > 0) {
      $allowed = array_map('intval', array_column($classes, 'id'));
      if (!in_array($classFilter, $allowed, true)) {
        $classFilter = 0;
      }
    }

    if ($scope !== '') {
      $filters[] = 'a.scope = ?';
      $params[] = $scope;
    }
    if ($classFilter > 0) {
      $filters[] = 'a.class_id = ?';
      $params[] = $classFilter;
    }
    if ($q !== '') {
      $filters[] = '(a.title LIKE ? OR a.message LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
    }
    if ($statusFilter !== '') {
      if ($statusFilter === 'ACTIVE') {
        $filters[] = 'a.start_at <= NOW() AND a.end_at >= NOW()';
      } elseif ($statusFilter === 'SCHEDULED') {
        $filters[] = 'a.start_at > NOW()';
      } elseif ($statusFilter === 'EXPIRED') {
        $filters[] = 'a.end_at < NOW()';
      }
    }

    if ($statusFilter === 'EXPIRED') {
      $showExpired = true;
    }

    if (!$showExpired) {
      $filters[] = 'a.end_at >= NOW()';
    }

    if ($statusFilter !== '' && in_array($statusFilter, ['DRAFT','PUBLISHED'], true)) {
      $filters[] = 'a.status = ?';
      $params[] = $statusFilter;
    }

    if (!$isAdmin) {
      $filters[] = 'a.status = ?';
      $params[] = 'PUBLISHED';
      $filters[] = 'a.start_at <= NOW() AND a.end_at >= NOW()';

      $teacherClassIds = array_map('intval', array_column($classes, 'id'));
      if ($teacherClassIds) {
        $placeholders = implode(',', array_fill(0, count($teacherClassIds), '?'));
        $filters[] = "(a.scope = 'GLOBAL' OR (a.scope = 'CLASS' AND a.class_id IN ($placeholders)))";
        $params = array_merge($params, $teacherClassIds);
      } else {
        $filters[] = "a.scope = 'GLOBAL'";
      }
    }

    $sql = 'SELECT a.*, c.name AS class_name FROM announcements a LEFT JOIN classes c ON c.id = a.class_id';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY (CASE WHEN a.is_pinned = 1 AND (a.pin_until IS NULL OR a.pin_until >= NOW()) THEN 1 ELSE 0 END) DESC, a.priority DESC, a.start_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    (new Response())->view('announcements/index.php', [
      'items' => $items,
      'classes' => $classes,
      'scope' => $scope,
      'classFilter' => $classFilter,
      'statusFilter' => $statusFilter,
      'q' => $q,
      'showExpired' => $showExpired,
      'isAdmin' => $isAdmin,
    ]);
  }

  public function create(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'announcements']);
      return;
    }

    $pdo = Db::pdo();
    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('announcements/create.php', [
      'classes' => $classes,
    ]);
  }

  public function store(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'announcements']);
      return;
    }

    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $scope = $_POST['scope'] ?? 'GLOBAL';
    $classId = (int)($_POST['class_id'] ?? 0);
    $startAt = $this->parseDateTime($_POST['start_at'] ?? '');
    $endAt = $this->parseDateTime($_POST['end_at'] ?? '');
    $pinUntil = $this->parseDateTime($_POST['pin_until'] ?? '');
    $isPinned = !empty($_POST['is_pinned']) ? 1 : 0;
    $priority = (int)($_POST['priority'] ?? 0);
    $status = $_POST['status'] ?? 'DRAFT';

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if ($message === '') $errors[] = 'Message is required.';
    if (!in_array($scope, ['GLOBAL','CLASS'], true)) $errors[] = 'Scope is invalid.';
    if ($scope === 'CLASS' && $classId <= 0) $errors[] = 'Class is required for class scope.';
    if (!$startAt || !$endAt) $errors[] = 'Start and end time are required.';
    if ($startAt && $endAt && $startAt > $endAt) $errors[] = 'Start must be before end.';

    if ($priority < 0 || $priority > 2) $errors[] = 'Priority is invalid.';
    if (!in_array($status, ['DRAFT','PUBLISHED'], true)) $errors[] = 'Status is invalid.';

    if ($errors) {
      $pdo = Db::pdo();
      $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
      (new Response())->view('announcements/create.php', [
        'errors' => $errors,
        'classes' => $classes,
        'announcement' => $_POST,
      ]);
      return;
    }

    $pdo = Db::pdo();
    $publishedAt = $status === 'PUBLISHED' ? date('Y-m-d H:i:s') : null;
    $stmt = $pdo->prepare('INSERT INTO announcements (title, message, scope, class_id, start_at, end_at, status, published_at, pin_until, is_pinned, priority, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
      $title,
      $message,
      $scope,
      $scope === 'CLASS' ? $classId : null,
      $startAt,
      $endAt,
      $status,
      $publishedAt,
      $pinUntil,
      $isPinned,
      $priority,
      $userId,
    ]);

    Flash::set('success', 'Announcement created.');
    (new Response())->redirect('/announcements');
  }

  public function edit(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'announcements']);
      return;
    }

    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
      (new Response())->status(404)->html('Announcement not found');
      return;
    }

    $item['start_at_display'] = $this->formatDateTime($item['start_at'] ?? null);
    $item['end_at_display'] = $this->formatDateTime($item['end_at'] ?? null);
    $item['pin_until_display'] = $this->formatDateTime($item['pin_until'] ?? null);

    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('announcements/edit.php', [
      'announcement' => $item,
      'classes' => $classes,
    ]);
  }

  public function update(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'announcements']);
      return;
    }

    $id = (int)$request->param('id');
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $scope = $_POST['scope'] ?? 'GLOBAL';
    $classId = (int)($_POST['class_id'] ?? 0);
    $startAt = $this->parseDateTime($_POST['start_at'] ?? '');
    $endAt = $this->parseDateTime($_POST['end_at'] ?? '');
    $pinUntil = $this->parseDateTime($_POST['pin_until'] ?? '');
    $isPinned = !empty($_POST['is_pinned']) ? 1 : 0;
    $priority = (int)($_POST['priority'] ?? 0);
    $status = $_POST['status'] ?? 'DRAFT';

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if ($message === '') $errors[] = 'Message is required.';
    if (!in_array($scope, ['GLOBAL','CLASS'], true)) $errors[] = 'Scope is invalid.';
    if ($scope === 'CLASS' && $classId <= 0) $errors[] = 'Class is required for class scope.';
    if (!$startAt || !$endAt) $errors[] = 'Start and end time are required.';
    if ($startAt && $endAt && $startAt > $endAt) $errors[] = 'Start must be before end.';

    if ($priority < 0 || $priority > 2) $errors[] = 'Priority is invalid.';
    if (!in_array($status, ['DRAFT','PUBLISHED'], true)) $errors[] = 'Status is invalid.';

    if ($errors) {
      $pdo = Db::pdo();
      $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
      (new Response())->view('announcements/edit.php', [
        'errors' => $errors,
        'classes' => $classes,
        'announcement' => array_merge(['id' => $id], $_POST),
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT published_at FROM announcements WHERE id = ?');
    $stmt->execute([$id]);
    $currentPublishedAt = $stmt->fetchColumn() ?: null;
    $publishedAt = $status === 'PUBLISHED' ? ($currentPublishedAt ?: date('Y-m-d H:i:s')) : null;

    $stmt = $pdo->prepare('UPDATE announcements SET title=?, message=?, scope=?, class_id=?, start_at=?, end_at=?, status=?, published_at=?, pin_until=?, is_pinned=?, priority=? WHERE id=?');
    $stmt->execute([
      $title,
      $message,
      $scope,
      $scope === 'CLASS' ? $classId : null,
      $startAt,
      $endAt,
      $status,
      $publishedAt,
      $pinUntil,
      $isPinned,
      $priority,
      $id,
    ]);

    Flash::set('success', 'Announcement updated.');
    (new Response())->redirect('/announcements');
  }

  private function isStaffAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code IN (?, ?) LIMIT 1');
    $stmt->execute([$userId, 'STAFF_ADMIN', 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  private function parseDateTime(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;

    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
      return str_replace('T', ' ', $value) . ':00';
    }

    return null;
  }

  private function formatDateTime(?string $value): ?string
  {
    if (!$value) return null;
    return str_replace(' ', 'T', substr($value, 0, 16));
  }

  private function getTeacherClasses(\PDO $pdo, int $userId): array
  {
    if ($userId <= 0) return [];
    $stmt = $pdo->prepare('SELECT c.id, c.name FROM class_teacher_assignments cta JOIN classes c ON c.id = cta.class_id WHERE cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE()) ORDER BY c.name ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }
}
