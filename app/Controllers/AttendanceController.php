<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;

final class AttendanceController
{
  public function index(Request $request): void
  {
    $date = $this->normalizeDate($request->input('date', date('Y-m-d')));
    $sessionId = (int)($request->input('session_id', 0));
    $program = trim((string)$request->input('program', ''));
    $classId = (int)($request->input('class_id', 0));
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();

    $filters = [];
    $params = [$date];

    if ($sessionId > 0) {
      $filters[] = 'c.session_id = ?';
      $params[] = $sessionId;
    }
    if ($program !== '') {
      $filters[] = 'c.program = ?';
      $params[] = $program;
    }
    if ($classId > 0) {
      $filters[] = 'c.id = ?';
      $params[] = $classId;
    }

    if ($isAdmin) {
      $sql = "SELECT c.*, s.name AS session_name, ay.label AS academic_year_label,
                     cs.id AS class_session_id, cs.status AS session_status
              FROM classes c
              LEFT JOIN sessions s ON s.id = c.session_id
              LEFT JOIN academic_years ay ON ay.id = c.academic_year_id
              LEFT JOIN class_sessions cs ON cs.class_id = c.id AND cs.session_date = ?";
      if ($filters) {
        $sql .= ' WHERE ' . implode(' AND ', $filters);
      }
      $sql .= ' ORDER BY c.name ASC';
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
    } else {
      $sql = "SELECT c.*, s.name AS session_name, ay.label AS academic_year_label,
                     cs.id AS class_session_id, cs.status AS session_status
              FROM class_teacher_assignments cta
              JOIN classes c ON c.id = cta.class_id
              LEFT JOIN sessions s ON s.id = c.session_id
              LEFT JOIN academic_years ay ON ay.id = c.academic_year_id
              LEFT JOIN class_sessions cs ON cs.class_id = c.id AND cs.session_date = ?
              WHERE cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= ?)";
      $params = array_merge([$date, $userId, $date], array_slice($params, 1));
      if ($filters) {
        $sql .= ' AND ' . implode(' AND ', $filters);
      }
      $sql .= ' ORDER BY c.name ASC';
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
    }

    $classes = $stmt->fetchAll();
    foreach ($classes as &$class) {
      $classSession = [
        'status' => $class['session_status'] ?? null,
      ];
      $class['is_locked_display'] = $this->isLocked($classSession, $date);
    }
    unset($class);

    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY sort_order ASC')->fetchAll();
    $programs = $pdo->query('SELECT DISTINCT program FROM classes WHERE program IS NOT NULL ORDER BY program ASC')->fetchAll();

    (new Response())->view('attendance/index.php', [
      'date' => $date,
      'classes' => $classes,
      'isAdmin' => $isAdmin,
      'sessions' => $sessions,
      'programs' => $programs,
      'sessionId' => $sessionId,
      'program' => $program,
      'classId' => $classId,
    ]);
  }

  public function take(Request $request): void
  {
    $date = $this->normalizeDate($request->input('date', date('Y-m-d')));
    $classId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, $classId, $date)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'attendance']);
      return;
    }

    $classStmt = $pdo->prepare('SELECT c.*, s.name AS session_name FROM classes c LEFT JOIN sessions s ON s.id = c.session_id WHERE c.id = ?');
    $classStmt->execute([$classId]);
    $class = $classStmt->fetch();
    if (!$class) {
      (new Response())->status(404)->html('Class not found');
      return;
    }

    $classSession = $this->ensureClassSession($pdo, $classId, $date, $userId);
    $isLocked = $this->isLocked($classSession, $date);

    $summary = $this->buildSummary($pdo, $classSession['id']);
    $this->renderTake($pdo, $class, $classSession, $date, $isLocked, $isAdmin, null, [], $summary);
  }

  public function save(Request $request): void
  {
    $date = $this->normalizeDate($request->input('date', date('Y-m-d')));
    $classId = (int)$request->param('id');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();

    if (!$isAdmin && !$this->canAccessClass($pdo, $userId, $classId, $date)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'attendance']);
      return;
    }

    $classSession = $this->ensureClassSession($pdo, $classId, $date, $userId);
    $isLocked = $this->isLocked($classSession, $date);

    if ($isLocked && !$isAdmin) {
      (new Response())->redirect('/attendance/' . $classId . '?date=' . $date);
      return;
    }

    $statuses = $_POST['status'] ?? [];
    $reasons = $_POST['reason'] ?? [];
    $notes = $_POST['note'] ?? [];

    $errors = [];
    $requiredReason = ['ABSENT','LATE','EXCUSED'];
    foreach ($statuses as $studentId => $status) {
      $status = strtoupper(trim($status));
      if ($status === '') continue;
      if (in_array($status, $requiredReason, true)) {
        $reason = strtoupper(trim($reasons[$studentId] ?? ''));
        if ($reason === '') {
          $errors[] = 'Reason is required for Absent, Late, or Excused.';
          break;
        }
      }
    }

    if ($errors) {
      $classStmt = $pdo->prepare('SELECT c.*, s.name AS session_name FROM classes c LEFT JOIN sessions s ON s.id = c.session_id WHERE c.id = ?');
      $classStmt->execute([$classId]);
      $class = $classStmt->fetch();
      if (!$class) {
        (new Response())->status(404)->html('Class not found');
        return;
      }

      $records = [];
      foreach ($statuses as $studentId => $status) {
        $records[(int)$studentId] = [
          'status' => strtoupper(trim($status)),
          'absence_reason' => strtoupper(trim($reasons[$studentId] ?? '')),
          'note' => trim($notes[$studentId] ?? ''),
          'absence_note' => trim($notes[$studentId] ?? ''),
        ];
      }

      $summary = $this->buildSummary($pdo, $classSession['id']);
      $this->renderTake($pdo, $class, $classSession, $date, $isLocked, $isAdmin, $records, $errors, $summary);
      return;
    }

    $pdo->beginTransaction();
    try {
      foreach ($statuses as $studentId => $status) {
        $studentId = (int)$studentId;
        $status = strtoupper(trim($status));
        $reason = strtoupper(trim($reasons[$studentId] ?? ''));
        $note = trim($notes[$studentId] ?? '');

        if ($status === '') {
          $pdo->prepare('DELETE FROM attendance_records WHERE class_session_id = ? AND student_id = ?')
            ->execute([$classSession['id'], $studentId]);
          continue;
        }

        $allowed = ['PRESENT','ABSENT','LATE','EXCUSED'];
        if (!in_array($status, $allowed, true)) {
          continue;
        }

        $allowedReasons = ['SICK','FAMILY','TRAVEL','OTHER'];
        if (!in_array($reason, $allowedReasons, true)) {
          $reason = null;
        }

        $stmt = $pdo->prepare(
          'INSERT INTO attendance_records (class_session_id, student_id, status, absence_reason, absence_note, note, marked_by)
           VALUES (?,?,?,?,?,?,?)
           ON DUPLICATE KEY UPDATE status=VALUES(status), absence_reason=VALUES(absence_reason), absence_note=VALUES(absence_note), note=VALUES(note), marked_by=VALUES(marked_by), marked_at=NOW()'
        );

        $stmt->execute([
          $classSession['id'],
          $studentId,
          $status,
          $reason,
          $note ?: null,
          $note ?: null,
          $userId,
        ]);
      }

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    Flash::set('success', 'Attendance saved.');
    (new Response())->redirect('/attendance/' . $classId . '?date=' . $date);
  }

  public function lock(Request $request): void
  {
    $classId = (int)$request->param('id');
    $date = $this->normalizeDate($request->input('date', date('Y-m-d')));
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'attendance']);
      return;
    }

    $pdo = Db::pdo();
    $classSession = $this->ensureClassSession($pdo, $classId, $date, $userId);
    $stmt = $pdo->prepare('UPDATE class_sessions SET status="LOCKED", locked_at=NOW(), locked_by=? WHERE id=?');
    $stmt->execute([$userId, $classSession['id']]);

    Flash::set('success', 'Attendance locked.');
    (new Response())->redirect('/attendance/' . $classId . '?date=' . $date);
  }

  public function unlock(Request $request): void
  {
    $classId = (int)$request->param('id');
    $date = $this->normalizeDate($request->input('date', date('Y-m-d')));
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'attendance']);
      return;
    }

    $pdo = Db::pdo();
    $classSession = $this->ensureClassSession($pdo, $classId, $date, $userId);
    $stmt = $pdo->prepare('UPDATE class_sessions SET status="OPEN", locked_at=NULL, locked_by=NULL WHERE id=?');
    $stmt->execute([$classSession['id']]);

    Flash::set('success', 'Attendance unlocked.');
    (new Response())->redirect('/attendance/' . $classId . '?date=' . $date);
  }

  private function ensureClassSession(\PDO $pdo, int $classId, string $date, int $userId): array
  {
    $stmt = $pdo->prepare('SELECT * FROM class_sessions WHERE class_id = ? AND session_date = ? LIMIT 1');
    $stmt->execute([$classId, $date]);
    $row = $stmt->fetch();

    if ($row) {
      return $row;
    }

    $insert = $pdo->prepare('INSERT INTO class_sessions (class_id, session_date, status, created_by) VALUES (?,?,"OPEN",?)');
    $insert->execute([$classId, $date, $userId]);

    $stmt = $pdo->prepare('SELECT * FROM class_sessions WHERE id = ?');
    $stmt->execute([(int)$pdo->lastInsertId()]);
    return $stmt->fetch();
  }

  private function canAccessClass(\PDO $pdo, int $userId, int $classId, string $date): bool
  {
    $stmt = $pdo->prepare('SELECT 1 FROM class_teacher_assignments WHERE user_id = ? AND class_id = ? AND (end_date IS NULL OR end_date >= ?) LIMIT 1');
    $stmt->execute([$userId, $classId, $date]);
    return (bool)$stmt->fetchColumn();
  }

  private function isStaffAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code IN (?, ?) LIMIT 1');
    $stmt->execute([$userId, 'STAFF_ADMIN', 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  private function isLocked(array $classSession, string $date): bool
  {
    if (($classSession['status'] ?? '') === 'LOCKED') return true;

    $sessionDate = new \DateTime($date);
    $today = new \DateTime('today');

    if ($sessionDate < $today) {
      return true;
    }

    if ((int)$sessionDate->format('N') === 7) {
      $deadline = new \DateTime($date . ' 23:59:59');
      $now = new \DateTime();
      return $now > $deadline;
    }

    return false;
  }

  private function normalizeDate(string $value): string
  {
    $value = trim($value);
    if ($value === '') return date('Y-m-d');

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
      return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return date('Y-m-d');
  }

  private function renderTake(\PDO $pdo, array $class, array $classSession, string $date, bool $isLocked, bool $isAdmin, ?array $records = null, array $errors = [], array $summary = []): void
  {
    $studentsStmt = $pdo->prepare("SELECT s.id, s.full_name
                                   FROM student_class_enrollments sce
                                   JOIN students s ON s.id = sce.student_id
                                   WHERE sce.class_id = ? AND sce.end_date IS NULL
                                   ORDER BY s.full_name ASC");
    $studentsStmt->execute([(int)$class['id']]);
    $students = $studentsStmt->fetchAll();

    if ($records === null) {
      $recordsStmt = $pdo->prepare('SELECT * FROM attendance_records WHERE class_session_id = ?');
      $recordsStmt->execute([$classSession['id']]);
      $records = [];
      foreach ($recordsStmt->fetchAll() as $rec) {
        $records[(int)$rec['student_id']] = $rec;
      }
    }

    (new Response())->view('attendance/take.php', [
      'date' => $date,
      'class' => $class,
      'students' => $students,
      'records' => $records,
      'classSession' => $classSession,
      'isLocked' => $isLocked,
      'isAdmin' => $isAdmin,
      'errors' => $errors,
      'summary' => $summary,
    ]);
  }

  private function buildSummary(\PDO $pdo, int $classSessionId): array
  {
    $stmt = $pdo->prepare('SELECT status, COUNT(*) AS c FROM attendance_records WHERE class_session_id = ? GROUP BY status');
    $stmt->execute([$classSessionId]);
    $rows = $stmt->fetchAll();
    $summary = [
      'PRESENT' => 0,
      'ABSENT' => 0,
      'LATE' => 0,
      'EXCUSED' => 0,
    ];
    foreach ($rows as $row) {
      $summary[$row['status']] = (int)$row['c'];
    }
    return $summary;
  }
}
