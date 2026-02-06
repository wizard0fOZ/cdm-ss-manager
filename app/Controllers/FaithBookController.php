<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;
use Dompdf\Dompdf;

final class FaithBookController
{
  private array $types = ['NOTE','ACHIEVEMENT','DISCIPLINE','OTHER'];

  public function index(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $q = trim((string)$request->input('q', ''));

    $pdo = Db::pdo();
    $params = [];
    $sql = "SELECT s.id, s.full_name, s.status, c.name AS class_name, sce.class_id AS class_id
            FROM students s
            LEFT JOIN student_class_enrollments sce ON sce.student_id = s.id AND sce.end_date IS NULL
            LEFT JOIN classes c ON c.id = sce.class_id";

    if (!$isAdmin) {
      $sql .= " JOIN class_teacher_assignments cta ON cta.class_id = c.id AND cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE())";
      $params[] = $userId;
    }

    if ($q !== '') {
      $sql .= ($params ? ' AND ' : ' WHERE ') . ' s.full_name LIKE ?';
      $params[] = "%$q%";
    }

    $sql .= ' ORDER BY s.full_name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    $classIds = [];
    foreach ($students as $student) {
      if (!empty($student['class_id'])) {
        $classIds[] = (int)$student['class_id'];
      }
    }
    $classTeachers = $this->getClassTeachers($pdo, array_values(array_unique($classIds)));

    (new Response())->view('faith_book/index.php', [
      'students' => $students,
      'q' => $q,
      'classTeachers' => $classTeachers,
    ]);
  }

  public function show(Request $request): void
  {
    $studentId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessStudent($pdo, $userId, $studentId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'faithbook']);
      return;
    }

    $student = $this->getStudent($pdo, $studentId);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $entries = $this->getEntries($pdo, $studentId);
    $attendance = $this->getAttendanceSummary($pdo, $studentId);
    $teachers = [];
    if (!empty($student['class_id'])) {
      $teachers = $this->getClassTeachers($pdo, [(int)$student['class_id']]);
      $teachers = $teachers[(int)$student['class_id']] ?? [];
    }

    (new Response())->view('faith_book/show.php', [
      'student' => $student,
      'entries' => $entries,
      'types' => $this->types,
      'attendance' => $attendance,
      'teachers' => $teachers,
    ]);
  }

  public function create(Request $request): void
  {
    $studentId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();
    if (!$isAdmin && !$this->canAccessStudent($pdo, $userId, $studentId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'faithbook']);
      return;
    }

    $student = $this->getStudent($pdo, $studentId);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    (new Response())->view('faith_book/create.php', [
      'student' => $student,
      'types' => $this->types,
    ]);
  }

  public function store(Request $request): void
  {
    $studentId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessStudent($pdo, $userId, $studentId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'faithbook']);
      return;
    }

    $entryDate = $this->parseDate($_POST['entry_date'] ?? '');
    $type = $_POST['entry_type'] ?? 'NOTE';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $errors = [];
    if (!$entryDate) $errors[] = 'Entry date is required.';
    if (!in_array($type, $this->types, true)) $errors[] = 'Entry type is invalid.';
    if ($title === '') $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';

    if ($errors) {
      $student = $this->getStudent($pdo, $studentId);
      (new Response())->view('faith_book/create.php', [
        'errors' => $errors,
        'student' => $student,
        'types' => $this->types,
        'entry' => $_POST,
      ]);
      return;
    }

    $stmt = $pdo->prepare('INSERT INTO faith_book_records (student_id, entry_date, entry_type, title, content, created_by) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$studentId, $entryDate, $type, $title, $content, $userId]);

    Flash::set('success', 'Faith book entry added.');
    (new Response())->redirect('/faith-book/' . $studentId);
  }

  public function export(Request $request): void
  {
    $studentId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessStudent($pdo, $userId, $studentId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'faithbook']);
      return;
    }

    $student = $this->getStudent($pdo, $studentId);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $entries = $this->getEntries($pdo, $studentId);

    $year = date('Y');
    $safeName = $this->safeFilename($student['full_name'] ?? 'student');
    $filename = $safeName . '_FaithBook_' . $year . '.csv';
    $filenameStar = rawurlencode($filename);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filenameStar);

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Student', $student['full_name'] ?? '']);
    fputcsv($out, ['Class', $student['class_name'] ?? '']);
    fputcsv($out, []);
    fputcsv($out, ['Date', 'Type', 'Title', 'Content']);
    foreach ($entries as $e) {
      fputcsv($out, [$e['entry_date'], $e['entry_type'], $e['title'], $e['content']]);
    }
    fclose($out);
    exit;
  }

  public function print(Request $request): void
  {
    $studentId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessStudent($pdo, $userId, $studentId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'faithbook']);
      return;
    }

    $student = $this->getStudent($pdo, $studentId);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $entries = $this->getEntries($pdo, $studentId);
    $attendance = $this->getAttendanceSummary($pdo, $studentId);

    (new Response())->view('faith_book/print.php', [
      'student' => $student,
      'entries' => $entries,
      'attendance' => $attendance,
      'generatedAt' => date('Y-m-d H:i'),
    ]);
  }

  public function pdf(Request $request): void
  {
    $studentId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessStudent($pdo, $userId, $studentId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'faithbook']);
      return;
    }

    $student = $this->getStudent($pdo, $studentId);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $entries = $this->getEntries($pdo, $studentId);
    $attendance = $this->getAttendanceSummary($pdo, $studentId);

    $html = $this->renderView('faith_book/print.php', [
      'student' => $student,
      'entries' => $entries,
      'attendance' => $attendance,
      'generatedAt' => date('Y-m-d H:i'),
    ]);

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $year = date('Y');
    $safeName = $this->safeFilename($student['full_name'] ?? 'student');
    $filename = $safeName . '_FaithBook_' . $year . '.pdf';
    $filenameStar = rawurlencode($filename);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filenameStar);
    echo $dompdf->output();
    exit;
  }

  private function getStudent(\PDO $pdo, int $studentId): ?array
  {
    $stmt = $pdo->prepare('SELECT s.*, c.name AS class_name, sce.class_id FROM students s LEFT JOIN student_class_enrollments sce ON sce.student_id = s.id AND sce.end_date IS NULL LEFT JOIN classes c ON c.id = sce.class_id WHERE s.id = ?');
    $stmt->execute([$studentId]);
    return $stmt->fetch() ?: null;
  }

  private function getEntries(\PDO $pdo, int $studentId): array
  {
    $stmt = $pdo->prepare('SELECT * FROM faith_book_records WHERE student_id = ? ORDER BY entry_date DESC, created_at DESC');
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
  }

  private function getAttendanceSummary(\PDO $pdo, int $studentId): array
  {
    $terms = $pdo->query('SELECT id, label, start_date, end_date FROM terms ORDER BY start_date ASC')->fetchAll();
    $summary = [];

    foreach ($terms as $term) {
      $stmt = $pdo->prepare("SELECT ar.status, COUNT(*) AS c
                             FROM attendance_records ar
                             JOIN class_sessions cs ON cs.id = ar.class_session_id
                             WHERE ar.student_id = ? AND cs.session_date BETWEEN ? AND ?
                             GROUP BY ar.status");
      $stmt->execute([$studentId, $term['start_date'], $term['end_date']]);
      $counts = ['PRESENT'=>0,'ABSENT'=>0,'LATE'=>0,'EXCUSED'=>0];
      foreach ($stmt->fetchAll() as $row) {
        $counts[$row['status']] = (int)$row['c'];
      }
      $summary[] = [
        'term' => $term['label'],
        'start_date' => $term['start_date'],
        'end_date' => $term['end_date'],
        'counts' => $counts,
      ];
    }

    return $summary;
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

  private function canAccessStudent(\PDO $pdo, int $userId, int $studentId): bool
  {
    $stmt = $pdo->prepare('SELECT 1 FROM student_class_enrollments sce JOIN class_teacher_assignments cta ON cta.class_id = sce.class_id WHERE sce.student_id = ? AND cta.user_id = ? AND sce.end_date IS NULL AND (cta.end_date IS NULL OR cta.end_date >= CURDATE()) LIMIT 1');
    $stmt->execute([$studentId, $userId]);
    return (bool)$stmt->fetchColumn();
  }

  private function isStaffAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $override = $_SESSION['_role_override_code'] ?? null;
    if ($override) {
      return in_array($override, ['STAFF_ADMIN','SYSADMIN'], true);
    }
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

  private function normalizeDate(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
      return $m[3] . '-' . $m[2] . '-' . $m[1];
    }

    return null;
  }

  private function safeFilename(string $value): string
  {
    $value = trim($value);
    $value = preg_replace('/[^A-Za-z0-9 _-]+/', '', $value);
    $value = preg_replace('/\\s+/', '_', $value);
    $value = trim($value, " _");
    return $value !== '' ? $value : 'student';
  }

  private function renderView(string $view, array $data): string
  {
    extract($data);
    ob_start();
    require __DIR__ . '/../Views/' . $view;
    return ob_get_clean();
  }
}
