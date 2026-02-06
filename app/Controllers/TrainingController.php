<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;

final class TrainingController
{
  private array $types = ['PSO','FORMATION','OTHER'];

  public function index(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'training']);
      return;
    }

    $userFilter = (int)($request->input('user_id', 0));
    $type = trim((string)$request->input('type', ''));
    $q = trim((string)$request->input('q', ''));

    $pdo = Db::pdo();

    $filters = [];
    $params = [];
    if ($userFilter > 0) {
      $filters[] = 't.user_id = ?';
      $params[] = $userFilter;
    }
    if ($type !== '') {
      $filters[] = 't.type = ?';
      $params[] = $type;
    }
    if ($q !== '') {
      $filters[] = '(t.title LIKE ? OR u.full_name LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    $sql = "SELECT t.*, u.full_name FROM teacher_training_records t JOIN users u ON u.id = t.user_id";
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY t.attended_date DESC, t.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    $users = $pdo->query('SELECT id, full_name FROM users ORDER BY full_name ASC')->fetchAll();

    (new Response())->view('training/index.php', [
      'records' => $records,
      'users' => $users,
      'types' => $this->types,
      'userFilter' => $userFilter,
      'type' => $type,
      'q' => $q,
    ]);
  }

  public function create(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'training']);
      return;
    }

    $pdo = Db::pdo();
    $users = $pdo->query('SELECT id, full_name FROM users ORDER BY full_name ASC')->fetchAll();

    (new Response())->view('training/create.php', [
      'users' => $users,
      'types' => $this->types,
    ]);
  }

  public function store(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'training']);
      return;
    }

    $teacherId = (int)($_POST['user_id'] ?? 0);
    $type = $_POST['type'] ?? 'OTHER';
    $title = trim($_POST['title'] ?? '');
    $provider = trim($_POST['provider'] ?? '');
    $attendedDate = $this->parseDate($_POST['attended_date'] ?? '');
    $hours = trim($_POST['hours_fulfilled'] ?? '');
    $issueDate = $this->parseDate($_POST['issue_date'] ?? '');
    $expiryDate = $this->parseDate($_POST['expiry_date'] ?? '');
    $evidenceUrl = trim($_POST['evidence_url'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    $errors = [];
    if ($teacherId <= 0) $errors[] = 'Teacher is required.';
    if (!in_array($type, $this->types, true)) $errors[] = 'Type is invalid.';
    if ($title === '') $errors[] = 'Title is required.';

    if ($errors) {
      $pdo = Db::pdo();
      $users = $pdo->query('SELECT id, full_name FROM users ORDER BY full_name ASC')->fetchAll();
      (new Response())->view('training/create.php', [
        'errors' => $errors,
        'users' => $users,
        'types' => $this->types,
        'record' => $_POST,
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO teacher_training_records (user_id, type, title, provider, attended_date, hours_fulfilled, issue_date, expiry_date, evidence_url, remarks, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
      $teacherId,
      $type,
      $title,
      $provider ?: null,
      $attendedDate,
      $hours !== '' ? $hours : null,
      $issueDate,
      $expiryDate,
      $evidenceUrl ?: null,
      $remarks ?: null,
      $userId,
    ]);

    Flash::set('success', 'Training record added.');
    (new Response())->redirect('/training');
  }

  public function edit(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'training']);
      return;
    }

    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM teacher_training_records WHERE id = ?');
    $stmt->execute([$id]);
    $record = $stmt->fetch();

    if (!$record) {
      (new Response())->status(404)->html('Training record not found');
      return;
    }

    $record['attended_date_display'] = $this->formatDate($record['attended_date'] ?? null);
    $record['issue_date_display'] = $this->formatDate($record['issue_date'] ?? null);
    $record['expiry_date_display'] = $this->formatDate($record['expiry_date'] ?? null);

    $users = $pdo->query('SELECT id, full_name FROM users ORDER BY full_name ASC')->fetchAll();

    (new Response())->view('training/edit.php', [
      'record' => $record,
      'users' => $users,
      'types' => $this->types,
    ]);
  }

  public function update(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'training']);
      return;
    }

    $id = (int)$request->param('id');

    $teacherId = (int)($_POST['user_id'] ?? 0);
    $type = $_POST['type'] ?? 'OTHER';
    $title = trim($_POST['title'] ?? '');
    $provider = trim($_POST['provider'] ?? '');
    $attendedDate = $this->parseDate($_POST['attended_date'] ?? '');
    $hours = trim($_POST['hours_fulfilled'] ?? '');
    $issueDate = $this->parseDate($_POST['issue_date'] ?? '');
    $expiryDate = $this->parseDate($_POST['expiry_date'] ?? '');
    $evidenceUrl = trim($_POST['evidence_url'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    $errors = [];
    if ($teacherId <= 0) $errors[] = 'Teacher is required.';
    if (!in_array($type, $this->types, true)) $errors[] = 'Type is invalid.';
    if ($title === '') $errors[] = 'Title is required.';

    if ($errors) {
      $pdo = Db::pdo();
      $users = $pdo->query('SELECT id, full_name FROM users ORDER BY full_name ASC')->fetchAll();
      (new Response())->view('training/edit.php', [
        'errors' => $errors,
        'users' => $users,
        'types' => $this->types,
        'record' => array_merge(['id' => $id], $_POST),
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE teacher_training_records SET user_id=?, type=?, title=?, provider=?, attended_date=?, hours_fulfilled=?, issue_date=?, expiry_date=?, evidence_url=?, remarks=? WHERE id=?');
    $stmt->execute([
      $teacherId,
      $type,
      $title,
      $provider ?: null,
      $attendedDate,
      $hours !== '' ? $hours : null,
      $issueDate,
      $expiryDate,
      $evidenceUrl ?: null,
      $remarks ?: null,
      $id,
    ]);

    Flash::set('success', 'Training record updated.');
    (new Response())->redirect('/training');
  }

  private function isStaffAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code IN (?, ?) LIMIT 1');
    $stmt->execute([$userId, 'STAFF_ADMIN', 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  private function parseDate(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
      return $m[3] . '-' . $m[2] . '-' . $m[1];
    }

    return null;
  }

  private function formatDate(?string $value): ?string
  {
    if (!$value) return null;
    $parts = explode('-', $value);
    if (count($parts) === 3) {
      return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
    }
    return $value;
  }
}
