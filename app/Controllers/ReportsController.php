<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use Dompdf\Dompdf;

final class ReportsController extends BaseController
{
  private array $types = [
    'attendance_class' => 'Attendance (by Class)',
    'attendance_student' => 'Attendance (by Student)',
    'faithbook' => 'Faith Book Summary',
    'training' => 'Training Compliance',
    'class_list' => 'Class List',
  ];

  public function index(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'reports']);
      return;
    }

    $type = $request->input('type', 'attendance_class');
    if (!isset($this->types[$type])) $type = 'attendance_class';

    $yearId = (int)$request->input('year_id', 0);
    $termId = (int)$request->input('term_id', 0);
    $classId = (int)$request->input('class_id', 0);
    $studentId = (int)$request->input('student_id', 0);
    $status = trim((string)$request->input('status', ''));
    $from = $this->normalizeDate($request->input('from', ''));
    $to = $this->normalizeDate($request->input('to', ''));

    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
    $students = $pdo->query('SELECT id, full_name FROM students ORDER BY full_name ASC')->fetchAll();

    if ($yearId <= 0) {
      foreach ($years as $year) {
        if ((int)$year['is_active'] === 1) {
          $yearId = (int)$year['id'];
          break;
        }
      }
    }

    $terms = [];
    if ($yearId > 0) {
      $stmt = $pdo->prepare('SELECT id, label, start_date, end_date FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC');
      $stmt->execute([$yearId]);
      $terms = $stmt->fetchAll();
    }

    $term = null;
    if ($termId > 0) {
      foreach ($terms as $t) {
        if ((int)$t['id'] === $termId) {
          $term = $t;
          break;
        }
      }
      if (!$term) $termId = 0;
    }

    $data = $this->buildReport($pdo, $type, [
      'year_id' => $yearId,
      'term_id' => $termId,
      'class_id' => $classId,
      'student_id' => $studentId,
      'term' => $term,
      'status' => $status,
      'from' => $from,
      'to' => $to,
    ]);

    (new Response())->view('reports/index.php', [
      'types' => $this->types,
      'type' => $type,
      'years' => $years,
      'terms' => $terms,
      'classes' => $classes,
      'students' => $students,
      'yearId' => $yearId,
      'termId' => $termId,
      'classId' => $classId,
      'studentId' => $studentId,
      'status' => $status,
      'from' => $from,
      'to' => $to,
      'report' => $data,
    ]);
  }

  public function pdf(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'reports']);
      return;
    }

    $type = $request->input('type', 'attendance_class');
    if (!isset($this->types[$type])) $type = 'attendance_class';

    $yearId = (int)$request->input('year_id', 0);
    $termId = (int)$request->input('term_id', 0);
    $classId = (int)$request->input('class_id', 0);
    $studentId = (int)$request->input('student_id', 0);
    $status = trim((string)$request->input('status', ''));
    $from = $this->normalizeDate($request->input('from', ''));
    $to = $this->normalizeDate($request->input('to', ''));

    $pdo = Db::pdo();
    $term = null;
    if ($termId > 0) {
      $stmt = $pdo->prepare('SELECT id, label, start_date, end_date FROM terms WHERE id = ?');
      $stmt->execute([$termId]);
      $term = $stmt->fetch() ?: null;
    }

    $data = $this->buildReport($pdo, $type, [
      'year_id' => $yearId,
      'term_id' => $termId,
      'class_id' => $classId,
      'student_id' => $studentId,
      'term' => $term,
      'status' => $status,
      'from' => $from,
      'to' => $to,
    ]);

    $html = $this->renderView('reports/pdf.php', [
      'type' => $type,
      'label' => $this->types[$type],
      'report' => $data,
      'generatedAt' => date('Y-m-d H:i'),
    ]);

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = $this->safeFilename($this->types[$type]) . '_' . date('Ymd_His') . '.pdf';
    $filenameStar = rawurlencode($filename);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filenameStar);
    echo $dompdf->output();
    exit;
  }

  public function csv(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'reports']);
      return;
    }

    $type = $request->input('type', 'attendance_class');
    if (!isset($this->types[$type])) $type = 'attendance_class';

    $yearId = (int)$request->input('year_id', 0);
    $termId = (int)$request->input('term_id', 0);
    $classId = (int)$request->input('class_id', 0);
    $studentId = (int)$request->input('student_id', 0);
    $status = trim((string)$request->input('status', ''));
    $from = $this->normalizeDate($request->input('from', ''));
    $to = $this->normalizeDate($request->input('to', ''));

    $pdo = Db::pdo();
    $term = null;
    if ($termId > 0) {
      $stmt = $pdo->prepare('SELECT id, label, start_date, end_date FROM terms WHERE id = ?');
      $stmt->execute([$termId]);
      $term = $stmt->fetch() ?: null;
    }

    $data = $this->buildReport($pdo, $type, [
      'year_id' => $yearId,
      'term_id' => $termId,
      'class_id' => $classId,
      'student_id' => $studentId,
      'term' => $term,
      'status' => $status,
      'from' => $from,
      'to' => $to,
    ]);

    $filename = $this->safeFilename($this->types[$type]) . '_' . date('Ymd_His') . '.csv';
    $filenameStar = rawurlencode($filename);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filenameStar);

    $out = fopen('php://output', 'w');
    $headers = $data['headers'] ?? [];
    if ($headers) {
      fputcsv($out, $headers);
    }
    foreach ($data['rows'] ?? [] as $row) {
      $line = [];
      foreach ($headers as $header) {
        $line[] = $this->mapReportCell($type, $header, $row);
      }
      fputcsv($out, $line);
    }
    fclose($out);
    exit;
  }

  private function buildReport(\PDO $pdo, string $type, array $filters): array
  {
    if ($type === 'attendance_class') {
      return $this->attendanceByClass($pdo, $filters);
    }
    if ($type === 'attendance_student') {
      return $this->attendanceByStudent($pdo, $filters);
    }
    if ($type === 'faithbook') {
      return $this->faithBookSummary($pdo, $filters);
    }
    if ($type === 'training') {
      return $this->trainingCompliance($pdo);
    }
    if ($type === 'class_list') {
      return $this->classList($pdo, $filters);
    }
    return ['title' => 'Report', 'rows' => []];
  }

  private function mapReportCell(string $type, string $header, array $row): string
  {
    if ($type === 'attendance_class') {
      $map = ['Student' => 'student', 'Present' => 'present', 'Absent' => 'absent', 'Late' => 'late', 'Excused' => 'excused', 'Unmarked' => 'unmarked'];
      return (string)($row[$map[$header] ?? ''] ?? '');
    }
    if ($type === 'attendance_student') {
      $map = ['Present' => 'present', 'Absent' => 'absent', 'Late' => 'late', 'Excused' => 'excused', 'Unmarked' => 'unmarked'];
      return (string)($row[$map[$header] ?? ''] ?? '');
    }
    if ($type === 'faithbook') {
      $map = ['Date' => 'entry_date', 'Type' => 'entry_type', 'Title' => 'title', 'Content' => 'content'];
      return (string)($row[$map[$header] ?? ''] ?? '');
    }
    if ($type === 'training') {
      $map = ['Teacher' => 'full_name', 'PSO' => 'pso_date', 'Formation' => 'formation_date'];
      return (string)($row[$map[$header] ?? ''] ?? '');
    }
    if ($type === 'class_list') {
      $map = ['Student' => 'full_name', 'DOB' => 'dob', 'Status' => 'status'];
      return (string)($row[$map[$header] ?? ''] ?? '');
    }
    return '';
  }

  private function attendanceByClass(\PDO $pdo, array $filters): array
  {
    $classId = (int)($filters['class_id'] ?? 0);
    $term = $filters['term'] ?? null;
    $statusFilter = strtoupper(trim((string)($filters['status'] ?? '')));
    $from = $filters['from'] ?? null;
    $to = $filters['to'] ?? null;
    if ($classId <= 0 || !$term) {
      return ['title' => 'Attendance by Class', 'rows' => [], 'note' => 'Select class and term.'];
    }

    $stmt = $pdo->prepare('SELECT name FROM classes WHERE id = ?');
    $stmt->execute([$classId]);
    $className = $stmt->fetchColumn() ?: 'Class';

    $dateStart = $from ?: $term['start_date'];
    $dateEnd = $to ?: $term['end_date'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM class_sessions WHERE class_id = ? AND session_date BETWEEN ? AND ?');
    $stmt->execute([$classId, $dateStart, $dateEnd]);
    $totalSessions = (int)$stmt->fetchColumn();

    $students = $pdo->prepare('SELECT s.id, s.full_name FROM student_class_enrollments sce JOIN students s ON s.id = sce.student_id WHERE sce.class_id = ? AND sce.end_date IS NULL ORDER BY s.full_name ASC');
    $students->execute([$classId]);
    $studentRows = $students->fetchAll();

    $countFilters = ['cs.class_id = ?', 'cs.session_date BETWEEN ? AND ?'];
    $countParams = [$classId, $dateStart, $dateEnd];
    if ($statusFilter !== '') {
      $allowed = ['PRESENT','ABSENT','LATE','EXCUSED','UNMARKED'];
      if (in_array($statusFilter, $allowed, true)) {
        if ($statusFilter === 'UNMARKED') {
          $countFilters[] = 'ar.status IS NULL';
        } else {
          $countFilters[] = 'ar.status = ?';
          $countParams[] = $statusFilter;
        }
      }
    }
    $countsStmt = $pdo->prepare("SELECT ar.student_id, ar.status, COUNT(*) AS c
      FROM attendance_records ar
      JOIN class_sessions cs ON cs.id = ar.class_session_id
      WHERE " . implode(' AND ', $countFilters) . "
      GROUP BY ar.student_id, ar.status");
    $countsStmt->execute($countParams);
    $counts = $countsStmt->fetchAll();

    $map = [];
    foreach ($counts as $row) {
      $map[$row['student_id']][$row['status']] = (int)$row['c'];
    }

    $rows = [];
    foreach ($studentRows as $student) {
      $sid = (int)$student['id'];
      $present = $map[$sid]['PRESENT'] ?? 0;
      $absent = $map[$sid]['ABSENT'] ?? 0;
      $late = $map[$sid]['LATE'] ?? 0;
      $excused = $map[$sid]['EXCUSED'] ?? 0;
      $marked = $present + $absent + $late + $excused;
      $unmarked = max(0, $totalSessions - $marked);
      if ($statusFilter === 'PRESENT') {
        $rows[] = ['student' => $student['full_name'], 'present' => $present, 'absent' => 0, 'late' => 0, 'excused' => 0, 'unmarked' => 0];
      } elseif ($statusFilter === 'ABSENT') {
        $rows[] = ['student' => $student['full_name'], 'present' => 0, 'absent' => $absent, 'late' => 0, 'excused' => 0, 'unmarked' => 0];
      } elseif ($statusFilter === 'LATE') {
        $rows[] = ['student' => $student['full_name'], 'present' => 0, 'absent' => 0, 'late' => $late, 'excused' => 0, 'unmarked' => 0];
      } elseif ($statusFilter === 'EXCUSED') {
        $rows[] = ['student' => $student['full_name'], 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => $excused, 'unmarked' => 0];
      } elseif ($statusFilter === 'UNMARKED') {
        $rows[] = ['student' => $student['full_name'], 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'unmarked' => $unmarked];
      } else {
        $rows[] = [
          'student' => $student['full_name'],
          'present' => $present,
          'absent' => $absent,
          'late' => $late,
          'excused' => $excused,
          'unmarked' => $unmarked,
        ];
      }
    }

    return [
      'title' => 'Attendance by Class',
      'subtitle' => $className . ' • ' . $term['label'],
      'rows' => $rows,
      'headers' => ['Student','Present','Absent','Late','Excused','Unmarked'],
    ];
  }

  private function attendanceByStudent(\PDO $pdo, array $filters): array
  {
    $studentId = (int)($filters['student_id'] ?? 0);
    $term = $filters['term'] ?? null;
    $statusFilter = strtoupper(trim((string)($filters['status'] ?? '')));
    $from = $filters['from'] ?? null;
    $to = $filters['to'] ?? null;
    if ($studentId <= 0 || !$term) {
      return ['title' => 'Attendance by Student', 'rows' => [], 'note' => 'Select student and term.'];
    }

    $stmt = $pdo->prepare('SELECT full_name FROM students WHERE id = ?');
    $stmt->execute([$studentId]);
    $studentName = $stmt->fetchColumn() ?: 'Student';

    $classStmt = $pdo->prepare('SELECT c.id, c.name FROM student_class_enrollments sce JOIN classes c ON c.id = sce.class_id WHERE sce.student_id = ? AND sce.end_date IS NULL LIMIT 1');
    $classStmt->execute([$studentId]);
    $classRow = $classStmt->fetch();

    $classId = $classRow['id'] ?? 0;
    if (!$classId) {
      return ['title' => 'Attendance by Student', 'rows' => [], 'note' => 'Student is not in an active class.'];
    }

    $dateStart = $from ?: $term['start_date'];
    $dateEnd = $to ?: $term['end_date'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM class_sessions WHERE class_id = ? AND session_date BETWEEN ? AND ?');
    $stmt->execute([$classId, $dateStart, $dateEnd]);
    $totalSessions = (int)$stmt->fetchColumn();

    $countFilters = ['ar.student_id = ?', 'cs.session_date BETWEEN ? AND ?'];
    $countParams = [$studentId, $dateStart, $dateEnd];
    if ($statusFilter !== '') {
      $allowed = ['PRESENT','ABSENT','LATE','EXCUSED','UNMARKED'];
      if (in_array($statusFilter, $allowed, true)) {
        if ($statusFilter === 'UNMARKED') {
          $countFilters[] = 'ar.status IS NULL';
        } else {
          $countFilters[] = 'ar.status = ?';
          $countParams[] = $statusFilter;
        }
      }
    }
    $countsStmt = $pdo->prepare("SELECT ar.status, COUNT(*) AS c
      FROM attendance_records ar
      JOIN class_sessions cs ON cs.id = ar.class_session_id
      WHERE " . implode(' AND ', $countFilters) . "
      GROUP BY ar.status");
    $countsStmt->execute($countParams);
    $map = [];
    foreach ($countsStmt->fetchAll() as $row) {
      $map[$row['status']] = (int)$row['c'];
    }

    $present = $map['PRESENT'] ?? 0;
    $absent = $map['ABSENT'] ?? 0;
    $late = $map['LATE'] ?? 0;
    $excused = $map['EXCUSED'] ?? 0;
    $marked = $present + $absent + $late + $excused;
    $unmarked = max(0, $totalSessions - $marked);

    if ($statusFilter === 'PRESENT') {
      $rows = [['present' => $present, 'absent' => 0, 'late' => 0, 'excused' => 0, 'unmarked' => 0]];
    } elseif ($statusFilter === 'ABSENT') {
      $rows = [['present' => 0, 'absent' => $absent, 'late' => 0, 'excused' => 0, 'unmarked' => 0]];
    } elseif ($statusFilter === 'LATE') {
      $rows = [['present' => 0, 'absent' => 0, 'late' => $late, 'excused' => 0, 'unmarked' => 0]];
    } elseif ($statusFilter === 'EXCUSED') {
      $rows = [['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => $excused, 'unmarked' => 0]];
    } elseif ($statusFilter === 'UNMARKED') {
      $rows = [['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'unmarked' => $unmarked]];
    } else {
      $rows = [[
        'present' => $present,
        'absent' => $absent,
        'late' => $late,
        'excused' => $excused,
        'unmarked' => $unmarked,
      ]];
    }

    return [
      'title' => 'Attendance by Student',
      'subtitle' => $studentName . ' • ' . ($classRow['name'] ?? '') . ' • ' . $term['label'],
      'rows' => $rows,
      'headers' => ['Present','Absent','Late','Excused','Unmarked'],
    ];
  }

  private function faithBookSummary(\PDO $pdo, array $filters): array
  {
    $studentId = (int)($filters['student_id'] ?? 0);
    $termId = (int)($filters['term_id'] ?? 0);
    if ($studentId <= 0) {
      return ['title' => 'Faith Book Summary', 'rows' => [], 'note' => 'Select a student.'];
    }

    $stmt = $pdo->prepare('SELECT full_name FROM students WHERE id = ?');
    $stmt->execute([$studentId]);
    $studentName = $stmt->fetchColumn() ?: 'Student';

    $sql = 'SELECT entry_date, entry_type, title, content FROM faith_book_records WHERE student_id = ?';
    $params = [$studentId];
    if ($termId > 0) {
      $sql .= ' AND term_id = ?';
      $params[] = $termId;
    } elseif (!empty($filters['year_id'])) {
      $sql .= ' AND academic_year_id = ?';
      $params[] = (int)$filters['year_id'];
    }
    $sql .= ' ORDER BY entry_date DESC, created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return [
      'title' => 'Faith Book Summary',
      'subtitle' => $studentName,
      'rows' => $rows,
      'headers' => ['Date','Type','Title','Content'],
    ];
  }

  private function trainingCompliance(\PDO $pdo): array
  {
    $sql = "SELECT u.id, u.full_name,
      MAX(CASE WHEN t.type = 'PSO' THEN t.attended_date END) AS pso_date,
      MAX(CASE WHEN t.type = 'FORMATION' THEN t.attended_date END) AS formation_date
      FROM users u
      JOIN user_roles ur ON ur.user_id = u.id
      JOIN roles r ON r.id = ur.role_id
      LEFT JOIN teacher_training_records t ON t.user_id = u.id
      WHERE r.code IN ('TEACHER','STAFF_ADMIN') AND u.status = 'ACTIVE'
      GROUP BY u.id, u.full_name
      ORDER BY u.full_name ASC";
    $rows = $pdo->query($sql)->fetchAll();
    return [
      'title' => 'Training Compliance',
      'rows' => $rows,
      'headers' => ['Teacher','PSO','Formation'],
    ];
  }

  private function classList(\PDO $pdo, array $filters): array
  {
    $classId = (int)($filters['class_id'] ?? 0);
    if ($classId <= 0) {
      return ['title' => 'Class List', 'rows' => [], 'note' => 'Select a class.'];
    }

    $stmt = $pdo->prepare('SELECT name FROM classes WHERE id = ?');
    $stmt->execute([$classId]);
    $className = $stmt->fetchColumn() ?: 'Class';

    $stmt = $pdo->prepare('SELECT s.full_name, s.dob, s.status FROM student_class_enrollments sce JOIN students s ON s.id = sce.student_id WHERE sce.class_id = ? AND sce.end_date IS NULL ORDER BY s.full_name ASC');
    $stmt->execute([$classId]);
    $rows = $stmt->fetchAll();

    return [
      'title' => 'Class List',
      'subtitle' => $className,
      'rows' => $rows,
      'headers' => ['Student','DOB','Status'],
    ];
  }

  private function renderView(string $view, array $data): string
  {
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/../Views/' . $view;
    return ob_get_clean();
  }

  private function safeFilename(string $value): string
  {
    $value = trim($value);
    $value = preg_replace('/[^A-Za-z0-9 _-]+/', '', $value);
    $value = preg_replace('/\\s+/', '_', $value);
    $value = trim($value, ' _');
    return $value !== '' ? $value : 'report';
  }
}
