<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;
use App\Core\Rbac\Rbac;
use App\Core\Audit\Audit;

final class ImportsController
{
  private array $types = ['STUDENTS','TEACHERS','CLASSES'];
  private string $defaultPassword = 'CDM2026!';

  public function index(): void
  {
    if (!$this->guard('imports.view')) return;
    $pdo = Db::pdo();
    $jobs = $pdo->query('SELECT * FROM import_jobs ORDER BY created_at DESC LIMIT 50')->fetchAll();

    (new Response())->view('imports/index.php', [
      'jobs' => $jobs,
    ]);
  }

  public function create(): void
  {
    if (!$this->guard('imports.run')) return;
    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY name ASC')->fetchAll();

    (new Response())->view('imports/create.php', [
      'types' => $this->types,
      'years' => $years,
      'classes' => $classes,
      'sessions' => $sessions,
    ]);
  }

  public function template(Request $request): void
  {
    if (!$this->guard('imports.view')) return;
    $type = strtoupper((string)$request->param('type'));
    if (!in_array($type, $this->types, true)) {
      (new Response())->status(404)->html('Template not found');
      return;
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="template_' . strtolower($type) . '.csv"');
    $out = fopen('php://output', 'w');

    if ($type === 'STUDENTS') {
      fputcsv($out, ['first_name','last_name','dob','class_name','identity_number','status','address','notes','is_rcic']);
    } elseif ($type === 'TEACHERS') {
      fputcsv($out, ['full_name','email','role']);
    } else {
      fputcsv($out, ['name','program','grade_level','stream','session_name','academic_year_label','status','room','max_students']);
    }

    fclose($out);
    exit;
  }

  public function preview(): void
  {
    if (!$this->guard('imports.run')) return;
    $type = strtoupper(trim($_POST['job_type'] ?? ''));
    if (!in_array($type, $this->types, true)) {
      (new Response())->status(400)->html('Invalid import type.');
      return;
    }

    if (empty($_FILES['csv_file']['tmp_name'])) {
      Flash::set('error', 'CSV file is required for preview.');
      (new Response())->redirect('/imports/create');
      return;
    }
    $fileErrors = $this->validateUploadedCsv($_FILES['csv_file']);
    if ($fileErrors) {
      Flash::set('error', implode(' ', $fileErrors));
      (new Response())->redirect('/imports/create');
      return;
    }

    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
    $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY name ASC')->fetchAll();

    $storedPath = $this->storeFile($_FILES['csv_file'], time(), 'preview');

    $preview = $this->buildPreview($type, $storedPath);
    $summary = $this->buildImportSummary($type, $storedPath, $preview['mapping']);
    $isSysAdmin = $this->isSysAdmin((int)($_SESSION['user_id'] ?? 0));

    (new Response())->view('imports/preview.php', [
      'type' => $type,
      'storedPath' => $storedPath,
      'headers' => $preview['headers'],
      'mapping' => $preview['mapping'],
      'rows' => $preview['rows'],
      'rowIssues' => $preview['row_issues'],
      'summary' => $summary,
      'isSysAdmin' => $isSysAdmin,
      'years' => $years,
      'classes' => $classes,
      'sessions' => $sessions,
      'defaults' => [
        'duplicate_mode' => $_POST['duplicate_mode'] ?? 'update',
        'dry_run' => !empty($_POST['dry_run']),
        'academic_year_id' => (int)($_POST['academic_year_id'] ?? 0),
        'default_class_id' => (int)($_POST['default_class_id'] ?? 0),
        'default_session_id' => (int)($_POST['default_session_id'] ?? 0),
      ],
    ]);
  }

  public function store(Request $request): void
  {
    if (!$this->guard('imports.run')) return;
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $type = strtoupper(trim($_POST['job_type'] ?? ''));
    $duplicateMode = $_POST['duplicate_mode'] ?? 'update';
    $dryRun = !empty($_POST['dry_run']);
    $yearId = (int)($_POST['academic_year_id'] ?? 0);
    $defaultClassId = (int)($_POST['default_class_id'] ?? 0);
    $defaultSessionId = (int)($_POST['default_session_id'] ?? 0);
    $storedPath = $_POST['stored_path'] ?? '';
    $mapping = [];
    if (!empty($_POST['mapping_json'])) {
      $decoded = json_decode((string)$_POST['mapping_json'], true);
      if (is_array($decoded)) $mapping = $decoded;
    }

    $errors = [];
    if (!in_array($type, $this->types, true)) $errors[] = 'Invalid import type.';
    if (!in_array($duplicateMode, ['update','skip'], true)) $errors[] = 'Invalid duplicate handling.';
    if (empty($_FILES['csv_file']['tmp_name']) && $storedPath === '') $errors[] = 'CSV file is required.';
    if (!empty($_FILES['csv_file']['tmp_name'])) {
      $fileErrors = $this->validateUploadedCsv($_FILES['csv_file']);
      if ($fileErrors) $errors = array_merge($errors, $fileErrors);
    }

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
      $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
      $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY name ASC')->fetchAll();
      (new Response())->view('imports/create.php', [
        'types' => $this->types,
        'years' => $years,
        'classes' => $classes,
        'sessions' => $sessions,
        'errors' => $errors,
      ]);
      return;
    }

    $pdo = Db::pdo();
    if (empty($storedPath)) {
      $storedPath = $this->storeFile($_FILES['csv_file'], time());
    } else {
      $storedPath = $this->sanitizeStoredPath($storedPath);
    }

    if ($storedPath === '' || !is_file($storedPath)) {
      Flash::set('error', 'Stored file path is invalid.');
      (new Response())->redirect('/imports/create');
      return;
    }

    $requiredIssues = $this->validateCsvRequireds($type, $storedPath, $mapping);
    if ($requiredIssues['missing'] > 0) {
      $overridePass = trim($_POST['override_password'] ?? '');
      $isSysAdmin = $this->isSysAdmin($userId);
      $canOverride = $isSysAdmin && $this->verifyPassword($userId, $overridePass);

      if (!$canOverride) {
        $preview = $this->buildPreviewWithMapping($type, $storedPath, $mapping);
        $summary = $this->buildImportSummary($type, $storedPath, $mapping);
        $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
        $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
        $sessions = $pdo->query('SELECT id, name FROM sessions ORDER BY name ASC')->fetchAll();

        (new Response())->view('imports/preview.php', [
          'type' => $type,
          'storedPath' => $storedPath,
          'headers' => $preview['headers'],
          'mapping' => $preview['mapping'],
          'rows' => $preview['rows'],
          'rowIssues' => $preview['row_issues'],
          'summary' => $summary,
          'isSysAdmin' => $isSysAdmin,
          'errors' => ['Fix required fields before importing.'],
          'years' => $years,
          'classes' => $classes,
          'sessions' => $sessions,
          'defaults' => [
            'duplicate_mode' => $duplicateMode,
            'dry_run' => $dryRun,
            'academic_year_id' => $yearId,
            'default_class_id' => $defaultClassId,
            'default_session_id' => $defaultSessionId,
          ],
        ]);
        return;
      }
    }

    $stmt = $pdo->prepare('INSERT INTO import_jobs (job_type, status, original_filename, created_by, started_at, total_rows, success_rows, failed_rows, error_summary) VALUES (?,?,?,?,NOW(),0,0,0,?)');
    $stmt->execute([
      $type,
      'RUNNING',
      $_FILES['csv_file']['name'] ?? basename($storedPath),
      $userId,
      $dryRun ? 'Dry run' : null,
    ]);
    $jobId = (int)$pdo->lastInsertId();
    Audit::log('imports.start', 'import_jobs', (string)$jobId, null, [
      'type' => $type,
      'dry_run' => $dryRun,
    ]);

    $pdo->prepare('UPDATE import_jobs SET stored_file_path = ? WHERE id = ?')->execute([$storedPath, $jobId]);

    $result = $this->processCsv($type, $storedPath, [
      'job_id' => $jobId,
      'duplicate_mode' => $duplicateMode,
      'dry_run' => $dryRun,
      'year_id' => $yearId,
      'default_class_id' => $defaultClassId,
      'default_session_id' => $defaultSessionId,
      'user_id' => $userId,
      'mapping' => $mapping,
    ]);

    $pdo->prepare('UPDATE import_jobs SET status = ?, finished_at = NOW(), total_rows = ?, success_rows = ?, failed_rows = ?, error_summary = ? WHERE id = ?')->execute([
      'COMPLETED',
      $result['total'],
      $result['success'],
      $result['failed'],
      $result['summary'],
      $jobId,
    ]);

    Flash::set('success', $dryRun ? 'Dry run completed.' : 'Import completed.');
    (new Response())->redirect('/imports/' . $jobId);
  }

  public function show(Request $request): void
  {
    if (!$this->guard('imports.view')) return;
    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM import_jobs WHERE id = ?');
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    if (!$job) {
      (new Response())->status(404)->html('Import job not found');
      return;
    }

    $rows = $pdo->prepare('SELECT * FROM import_job_rows WHERE job_id = ? ORDER BY row_num ASC LIMIT 500');
    $rows->execute([$id]);
    $items = $rows->fetchAll();

    (new Response())->view('imports/show.php', [
      'job' => $job,
      'rows' => $items,
    ]);
  }

  private function storeFile(array $file, int $jobId, string $prefix = 'import'): string
  {
    $dir = __DIR__ . '/../../storage/imports';
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }
    $ext = pathinfo($file['name'] ?? 'import.csv', PATHINFO_EXTENSION);
    $safe = $prefix . '_' . $jobId . '_' . time() . '.' . ($ext ?: 'csv');
    $target = $dir . '/' . $safe;
    move_uploaded_file($file['tmp_name'], $target);
    return $target;
  }

  private function validateUploadedCsv(array $file): array
  {
    $errors = [];
    if (!empty($file['error'])) {
      $errors[] = 'Upload failed.';
      return $errors;
    }

    $name = $file['name'] ?? '';
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
      $errors[] = 'Only CSV files are allowed.';
    }

    $maxBytes = 5 * 1024 * 1024;
    if (!empty($file['size']) && $file['size'] > $maxBytes) {
      $errors[] = 'CSV file must be under 5MB.';
    }

    if (!empty($file['tmp_name']) && is_file($file['tmp_name'])) {
      $finfo = new \finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($file['tmp_name']);
      $allowed = ['text/plain','text/csv','application/csv','application/vnd.ms-excel'];
      if ($mime && !in_array($mime, $allowed, true)) {
        $errors[] = 'CSV file type is not valid.';
      }
    }

    return $errors;
  }

  private function processCsv(string $type, string $path, array $options): array
  {
    $pdo = Db::pdo();
    $handle = fopen($path, 'r');
    if (!$handle) {
      return ['total' => 0, 'success' => 0, 'failed' => 0, 'summary' => 'Failed to open file.'];
    }

    $header = fgetcsv($handle);
    if (!$header) {
      fclose($handle);
      return ['total' => 0, 'success' => 0, 'failed' => 0, 'summary' => 'Empty CSV.'];
    }

    $map = $this->normalizeHeaders($header);
    $rowNum = 1;
    $total = 0;
    $success = 0;
    $failed = 0;
    $summary = [];

    while (($row = fgetcsv($handle)) !== false) {
      $rowNum++;
      $total++;
      $payload = $this->mapRowWithMapping($map, $row, $options['mapping'] ?? []);

      $result = $this->importRow($type, $payload, $options);
      $status = $result['status'];

      $pdo->prepare('INSERT INTO import_job_rows (job_id, row_num, status, error_message, payload_json) VALUES (?,?,?,?,?)')
        ->execute([
          $options['job_id'] ?? null,
          $rowNum,
          $status,
          $result['error'] ?? null,
          json_encode($payload),
        ]);

      if ($status === 'SUCCESS') $success++;
      if ($status === 'FAILED') $failed++;
      if (!empty($result['error'])) $summary[] = "Row $rowNum: " . $result['error'];
    }

    fclose($handle);

    return [
      'total' => $total,
      'success' => $success,
      'failed' => $failed,
      'summary' => $summary ? implode("\n", array_slice($summary, 0, 20)) : null,
    ];
  }

  private function importRow(string $type, array $payload, array $options): array
  {
    $pdo = Db::pdo();
    $duplicateMode = $options['duplicate_mode'] ?? 'update';
    $dryRun = !empty($options['dry_run']);
    $yearId = (int)($options['year_id'] ?? 0);
    if ($yearId <= 0) {
      $yearId = $this->getActiveAcademicYearId($pdo);
    }
    $defaultClassId = (int)($options['default_class_id'] ?? 0);
    $defaultSessionId = (int)($options['default_session_id'] ?? 0);
    $userId = (int)($options['user_id'] ?? 0);

    if ($type === 'STUDENTS') {
      $first = trim($payload['first_name'] ?? '');
      $last = trim($payload['last_name'] ?? '');
      $dob = $this->parseDate($payload['dob'] ?? '');
      if ($first === '' || $last === '') {
        return ['status' => 'FAILED', 'error' => 'Missing first/last name.'];
      }
      $fullName = trim($first . ' ' . $last);
      $identity = trim($payload['identity_number'] ?? '');
      $status = strtoupper(trim($payload['status'] ?? 'ACTIVE'));
      if (!in_array($status, ['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'], true)) $status = 'ACTIVE';
      $address = trim($payload['address'] ?? '');
      $notes = trim($payload['notes'] ?? '');
      $isRcic = !empty($payload['is_rcic']) ? 1 : 0;

      $existing = null;
      if ($identity !== '') {
        $stmt = $pdo->prepare('SELECT id FROM students WHERE identity_number = ? LIMIT 1');
        $stmt->execute([$identity]);
        $existing = $stmt->fetchColumn();
      }
      if (!$existing && $dob) {
        $stmt = $pdo->prepare('SELECT id FROM students WHERE first_name = ? AND last_name = ? AND dob = ? LIMIT 1');
        $stmt->execute([$first, $last, $dob]);
        $existing = $stmt->fetchColumn();
      }

      if ($existing) {
        if ($duplicateMode === 'skip') {
          return ['status' => 'SKIPPED'];
        }
        if (!$dryRun) {
          $stmt = $pdo->prepare('UPDATE students SET first_name=?, last_name=?, full_name=?, dob=?, identity_number=?, status=?, address=?, notes=?, is_rcic=? WHERE id=?');
          $stmt->execute([$first, $last, $fullName, $dob, $identity ?: null, $status, $address ?: null, $notes ?: null, $isRcic, $existing]);
        }
        $studentId = (int)$existing;
      } else {
        if (!$dryRun) {
          $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, full_name, dob, identity_number, status, address, notes, is_rcic) VALUES (?,?,?,?,?,?,?,?,?)');
          $stmt->execute([$first, $last, $fullName, $dob, $identity ?: null, $status, $address ?: null, $notes ?: null, $isRcic]);
          $studentId = (int)$pdo->lastInsertId();
        } else {
          $studentId = 0;
        }
      }

      $className = trim($payload['class_name'] ?? '');
      $classId = $defaultClassId;
      if ($className !== '') {
        $stmt = $pdo->prepare('SELECT id FROM classes WHERE name = ? LIMIT 1');
        $stmt->execute([$className]);
        $classId = (int)$stmt->fetchColumn();
      }

      if ($yearId > 0 && $classId > 0 && !$dryRun) {
        $stmt = $pdo->prepare('SELECT id FROM student_class_enrollments WHERE student_id = ? AND class_id = ? AND end_date IS NULL LIMIT 1');
        $stmt->execute([$studentId, $classId]);
        $exists = $stmt->fetchColumn();
        if (!$exists) {
          $stmt = $pdo->prepare('INSERT INTO student_class_enrollments (student_id, class_id, academic_year_id, start_date, status, created_by) VALUES (?,?,?,?,?,?)');
          $stmt->execute([$studentId, $classId, $yearId, date('Y-m-d'), 'ACTIVE', $userId]);
        }
      }

      return ['status' => 'SUCCESS'];
    }

    if ($type === 'TEACHERS') {
      $name = trim($payload['full_name'] ?? '');
      $email = strtolower(trim($payload['email'] ?? ''));
      if ($name === '' || $email === '') {
        return ['status' => 'FAILED', 'error' => 'Missing full_name or email.'];
      }
      $role = strtoupper(str_replace(' ', '_', trim($payload['role'] ?? 'TEACHER')));
      if (!in_array($role, ['TEACHER','COORDINATOR','OFFICE_ADMIN','CORE_TEAM','STAFF_ADMIN'], true)) {
        $role = 'TEACHER';
      }
      $roleCode = in_array($role, ['COORDINATOR','OFFICE_ADMIN','CORE_TEAM','STAFF_ADMIN'], true) ? 'STAFF_ADMIN' : 'TEACHER';

      $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
      $stmt->execute([$email]);
      $existing = $stmt->fetchColumn();
      if ($existing) {
        if ($duplicateMode === 'skip') {
          return ['status' => 'SKIPPED'];
        }
        if (!$dryRun) {
          $pdo->prepare('UPDATE users SET full_name = ? WHERE id = ?')->execute([$name, $existing]);
        }
        $userIdRow = (int)$existing;
      } else {
        if (!$dryRun) {
          $hash = password_hash($this->defaultPassword, PASSWORD_DEFAULT);
          $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, status, must_change_password) VALUES (?,?,?,?,?)');
          $stmt->execute([$name, $email, $hash, 'ACTIVE', 1]);
          $userIdRow = (int)$pdo->lastInsertId();
        } else {
          $userIdRow = 0;
        }
      }

      if (!$dryRun && $userIdRow > 0) {
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE code = ? LIMIT 1');
        $stmt->execute([$roleCode]);
        $roleId = (int)$stmt->fetchColumn();
        if ($roleId > 0) {
          $stmt = $pdo->prepare('SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = ?');
          $stmt->execute([$userIdRow, $roleId]);
          if (!$stmt->fetchColumn()) {
            $pdo->prepare('INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES (?,?,?)')->execute([$userIdRow, $roleId, $userId]);
          }
        }
      }

      return ['status' => 'SUCCESS'];
    }

    if ($type === 'CLASSES') {
      $name = trim($payload['name'] ?? '');
      if ($name === '') return ['status' => 'FAILED', 'error' => 'Missing class name.'];
      $program = strtoupper(trim($payload['program'] ?? ''));
      if ($program === '') $program = null;
      $grade = trim($payload['grade_level'] ?? '');
      $grade = $grade !== '' ? (int)$grade : null;
      $stream = strtoupper(trim($payload['stream'] ?? 'SINGLE'));
      if (!in_array($stream, ['PAUL','PETER','SINGLE'], true)) $stream = 'SINGLE';
      $sessionName = trim($payload['session_name'] ?? '');
      $yearLabel = trim($payload['academic_year_label'] ?? '');
      $status = strtoupper(trim($payload['status'] ?? 'ACTIVE'));
      if (!in_array($status, ['DRAFT','ACTIVE','INACTIVE'], true)) $status = 'ACTIVE';
      $room = trim($payload['room'] ?? '');
      $maxStudents = trim($payload['max_students'] ?? '');
      $maxStudents = $maxStudents !== '' ? (int)$maxStudents : null;

      $sessionId = $defaultSessionId;
      if ($sessionName !== '') {
        $stmt = $pdo->prepare('SELECT id FROM sessions WHERE name = ? LIMIT 1');
        $stmt->execute([$sessionName]);
        $sessionId = (int)$stmt->fetchColumn();
      }
      if ($sessionId <= 0) {
        return ['status' => 'FAILED', 'error' => 'Session not found.'];
      }

      $academicYearId = $yearId;
      if ($yearLabel !== '') {
        $stmt = $pdo->prepare('SELECT id FROM academic_years WHERE label = ? LIMIT 1');
        $stmt->execute([$yearLabel]);
        $academicYearId = (int)$stmt->fetchColumn();
      }

      $stmt = $pdo->prepare('SELECT id FROM classes WHERE name = ? AND session_id = ? LIMIT 1');
      $stmt->execute([$name, $sessionId]);
      $existing = $stmt->fetchColumn();
      if ($existing) {
        if ($duplicateMode === 'skip') {
          return ['status' => 'SKIPPED'];
        }
        if (!$dryRun) {
          $pdo->prepare('UPDATE classes SET academic_year_id = ?, name = ?, program = ?, grade_level = ?, stream = ?, room = ?, status = ?, max_students = ? WHERE id = ?')
            ->execute([$academicYearId ?: null, $name, $program, $grade, $stream, $room ?: null, $status, $maxStudents, $existing]);
        }
      } else {
        if (!$dryRun) {
          $pdo->prepare('INSERT INTO classes (academic_year_id, name, program, grade_level, stream, room, session_id, status, max_students) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$academicYearId ?: null, $name, $program, $grade, $stream, $room ?: null, $sessionId, $status, $maxStudents]);
        }
      }
      return ['status' => 'SUCCESS'];
    }

    return ['status' => 'FAILED', 'error' => 'Unknown import type.'];
  }

  private function normalizeHeaders(array $header): array
  {
    $map = [];
    foreach ($header as $idx => $value) {
      $key = strtolower(trim((string)$value));
      $map[$key] = $idx;
    }
    return $map;
  }

  private function mapRow(array $map, array $row): array
  {
    return $this->mapRowWithMapping($map, $row, []);
  }

  private function mapRowWithMapping(array $map, array $row, array $mapping): array
  {
    $payload = [];
    if ($mapping) {
      foreach ($mapping as $target => $header) {
        if ($header === '' || $header === '__ignore__') {
          $payload[$target] = '';
          continue;
        }
        $key = strtolower(trim($header));
        $idx = $map[$key] ?? null;
        $payload[$target] = $idx !== null ? ($row[$idx] ?? '') : '';
      }
      return $payload;
    }

    foreach ($map as $key => $idx) {
      $payload[$key] = $row[$idx] ?? '';
    }
    return $payload;
  }

  private function parseDate(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;
    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) return $value;
    if (preg_match('/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/', $value, $m)) {
      return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return null;
  }

  private function buildPreview(string $type, string $path): array
  {
    $handle = fopen($path, 'r');
    if (!$handle) {
      return ['headers' => [], 'mapping' => [], 'rows' => [], 'row_issues' => []];
    }

    $header = fgetcsv($handle);
    if (!$header) {
      fclose($handle);
      return ['headers' => [], 'mapping' => [], 'rows' => [], 'row_issues' => []];
    }

    $map = $this->normalizeHeaders($header);
    $fields = $this->getFieldsForType($type);
    $mapping = [];
    foreach ($fields as $field) {
      $mapping[$field] = $this->guessMapping($field, $header);
    }

    $rows = [];
    $rowIssues = [];
    $count = 0;
    while (($row = fgetcsv($handle)) !== false && $count < 5) {
      $payload = $this->mapRowWithMapping($map, $row, $mapping);
      $rows[] = $payload;
      $rowIssues[] = $this->validatePayload($type, $payload);
      $count++;
    }
    fclose($handle);

    return [
      'headers' => $header,
      'mapping' => $mapping,
      'rows' => $rows,
      'row_issues' => $rowIssues,
    ];
  }

  private function buildPreviewWithMapping(string $type, string $path, array $mapping): array
  {
    $handle = fopen($path, 'r');
    if (!$handle) {
      return ['headers' => [], 'mapping' => $mapping, 'rows' => [], 'row_issues' => []];
    }

    $header = fgetcsv($handle);
    if (!$header) {
      fclose($handle);
      return ['headers' => [], 'mapping' => $mapping, 'rows' => [], 'row_issues' => []];
    }

    $map = $this->normalizeHeaders($header);
    $rows = [];
    $rowIssues = [];
    $count = 0;
    while (($row = fgetcsv($handle)) !== false && $count < 5) {
      $payload = $this->mapRowWithMapping($map, $row, $mapping);
      $rows[] = $payload;
      $rowIssues[] = $this->validatePayload($type, $payload);
      $count++;
    }
    fclose($handle);

    return [
      'headers' => $header,
      'mapping' => $mapping,
      'rows' => $rows,
      'row_issues' => $rowIssues,
    ];
  }

  private function buildImportSummary(string $type, string $path, array $mapping): array
  {
    $handle = fopen($path, 'r');
    if (!$handle) {
      return ['total' => 0, 'existing' => 0, 'new' => 0, 'unknown' => 0];
    }

    $header = fgetcsv($handle);
    if (!$header) {
      fclose($handle);
      return ['total' => 0, 'existing' => 0, 'new' => 0, 'unknown' => 0];
    }

    $map = $this->normalizeHeaders($header);
    $pdo = Db::pdo();
    $existing = [];
    $existingAlt = [];

    if ($type === 'TEACHERS') {
      $rows = $pdo->query('SELECT email FROM users')->fetchAll();
      foreach ($rows as $row) {
        $email = strtolower(trim((string)$row['email']));
        if ($email !== '') $existing[$email] = true;
      }
    } elseif ($type === 'STUDENTS') {
      $rows = $pdo->query('SELECT identity_number, full_name, dob FROM students')->fetchAll();
      foreach ($rows as $row) {
        $idn = strtolower(trim((string)$row['identity_number']));
        if ($idn !== '') $existing[$idn] = true;
        $name = strtolower(trim((string)$row['full_name']));
        $dob = trim((string)$row['dob']);
        if ($name !== '' && $dob !== '') {
          $existingAlt[$name . '|' . $dob] = true;
        }
      }
    } elseif ($type === 'CLASSES') {
      $rows = $pdo->query('SELECT name FROM classes')->fetchAll();
      foreach ($rows as $row) {
        $name = strtolower(trim((string)$row['name']));
        if ($name !== '') $existing[$name] = true;
      }
    }

    $total = 0;
    $existingCount = 0;
    $newCount = 0;
    $unknown = 0;
    while (($row = fgetcsv($handle)) !== false) {
      $payload = $this->mapRowWithMapping($map, $row, $mapping);
      $total++;

      if ($type === 'TEACHERS') {
        $email = strtolower(trim((string)($payload['email'] ?? '')));
        if ($email === '') { $unknown++; continue; }
        if (isset($existing[$email])) $existingCount++; else $newCount++;
      } elseif ($type === 'STUDENTS') {
        $idn = strtolower(trim((string)($payload['identity_number'] ?? '')));
        if ($idn !== '' && isset($existing[$idn])) { $existingCount++; continue; }
        $full = strtolower(trim((string)(($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''))));
        $dob = $this->parseDate((string)($payload['dob'] ?? ''));
        if ($full !== '' && $dob !== '' && isset($existingAlt[$full . '|' . $dob])) { $existingCount++; continue; }
        if ($full === '' && $idn === '') { $unknown++; continue; }
        $newCount++;
      } else {
        $name = strtolower(trim((string)($payload['name'] ?? '')));
        if ($name === '') { $unknown++; continue; }
        if (isset($existing[$name])) $existingCount++; else $newCount++;
      }
    }

    fclose($handle);
    return [
      'total' => $total,
      'existing' => $existingCount,
      'new' => $newCount,
      'unknown' => $unknown,
    ];
  }

  private function guessMapping(string $field, array $headers): string
  {
    $needle = strtolower($field);
    foreach ($headers as $header) {
      $key = strtolower(trim((string)$header));
      if ($key === $needle) return $header;
    }
    return '';
  }

  private function getFieldsForType(string $type): array
  {
    if ($type === 'STUDENTS') {
      return ['first_name','last_name','dob','class_name','identity_number','status','address','notes','is_rcic'];
    }
    if ($type === 'TEACHERS') {
      return ['full_name','email','role'];
    }
    return ['name','program','grade_level','stream','session_name','academic_year_label','status','room','max_students'];
  }

  private function validatePayload(string $type, array $payload): array
  {
    $missing = [];
    $warnings = [];

    if ($type === 'STUDENTS') {
      if (trim($payload['first_name'] ?? '') === '') $missing[] = 'first_name';
      if (trim($payload['last_name'] ?? '') === '') $missing[] = 'last_name';
      $dob = trim($payload['dob'] ?? '');
      if ($dob !== '' && !$this->parseDate($dob)) {
        $warnings[] = 'DOB format should be YYYY-MM-DD or DD/MM/YYYY.';
      }
    } elseif ($type === 'TEACHERS') {
      if (trim($payload['full_name'] ?? '') === '') $missing[] = 'full_name';
      if (trim($payload['email'] ?? '') === '') $missing[] = 'email';
    } elseif ($type === 'CLASSES') {
      if (trim($payload['name'] ?? '') === '') $missing[] = 'name';
      if (trim($payload['session_name'] ?? '') === '') $missing[] = 'session_name';
    }

    if ($missing) {
      $warnings[] = 'Missing required: ' . implode(', ', $missing);
    }

    return [
      'missing_fields' => $missing,
      'warnings' => $warnings,
    ];
  }

  private function validateCsvRequireds(string $type, string $path, array $mapping): array
  {
    $handle = fopen($path, 'r');
    if (!$handle) {
      return ['missing' => 0];
    }

    $header = fgetcsv($handle);
    if (!$header) {
      fclose($handle);
      return ['missing' => 0];
    }

    $map = $this->normalizeHeaders($header);
    $missingCount = 0;
    while (($row = fgetcsv($handle)) !== false) {
      $payload = $this->mapRowWithMapping($map, $row, $mapping);
      $issue = $this->validatePayload($type, $payload);
      if (!empty($issue['missing_fields'])) {
        $missingCount++;
      }
    }
    fclose($handle);

    return ['missing' => $missingCount];
  }

  private function isSysAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code = ? LIMIT 1');
    $stmt->execute([$userId, 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  private function verifyPassword(int $userId, string $password): bool
  {
    if ($userId <= 0 || $password === '') return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();
    if (!$hash) return false;
    return password_verify($password, $hash);
  }

  private function sanitizeStoredPath(string $path): string
  {
    $base = realpath(__DIR__ . '/../../storage/imports');
    $real = realpath($path);
    if ($real && $base && strpos($real, $base) === 0) {
      return $real;
    }
    return '';
  }

  private function getActiveAcademicYearId(\PDO $pdo): int
  {
    $stmt = $pdo->query('SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1');
    return (int)$stmt->fetchColumn();
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
