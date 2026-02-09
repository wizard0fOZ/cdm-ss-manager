<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Audit\Audit;

final class StudentsController extends BaseController
{
  public function index(): void
  {
    if (!$this->guard('students.view')) return;
    $search = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $classId = trim($_GET['class_id'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50;

    $pdo = Db::pdo();
    [$students, $classTeachers, $pagination] = $this->loadStudents($pdo, $search, $status, $classId, $page, $perPage);
    $classRows = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
    $query = $this->buildQuery([
      'q' => $search,
      'status' => $status,
      'class_id' => $classId,
    ]);

    (new Response())->view('students/index.php', [
      'students' => $students,
      'search' => $search,
      'status' => $status,
      'classId' => $classId,
      'classes' => $classRows,
      'classTeachers' => $classTeachers,
      'pagination' => $pagination,
      'query' => $query,
    ]);
  }

  public function partial(): void
  {
    if (!$this->guard('students.view')) return;
    $search = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $classId = trim($_GET['class_id'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50;

    $pdo = Db::pdo();
    [$students, $classTeachers, $pagination] = $this->loadStudents($pdo, $search, $status, $classId, $page, $perPage);
    $query = $this->buildQuery([
      'q' => $search,
      'status' => $status,
      'class_id' => $classId,
    ]);

    (new Response())->view('students/_table.php', [
      'students' => $students,
      'classTeachers' => $classTeachers,
      'pagination' => $pagination,
      'query' => $query,
    ]);
  }

  public function updateStatus(Request $request): void
  {
    if (!$this->guard('students.edit')) return;
    $id = (int)$request->param('id');
    $status = strtoupper(trim($_POST['status'] ?? ''));
    $allowed = ['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'];
    if ($id <= 0 || !in_array($status, $allowed, true)) {
      (new Response())->status(422)->html('Invalid status');
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE students SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    Audit::log('students.status_update', 'students', (string)$id, null, ['status' => $status]);

    (new Response())->view('students/_status_cell.php', [
      'student' => ['id' => $id, 'status' => $status],
    ]);
  }

  public function bulk(): void
  {
    if (!$this->guard('students.edit')) return;
    $ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
    $action = $_POST['bulk_action'] ?? '';

    if (!$ids || $action === '') {
      (new Response())->redirect('/students');
      return;
    }

    $pdo = Db::pdo();

    if ($action === 'set_status') {
      $status = $_POST['status'] ?? '';
      $allowed = ['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'];
      if (!in_array($status, $allowed, true)) {
        (new Response())->redirect('/students');
        return;
      }

      $in = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $pdo->prepare("UPDATE students SET status=? WHERE id IN ($in)");
      $stmt->execute(array_merge([$status], $ids));
      Audit::log('students.bulk_status', 'students', implode(',', $ids), null, ['status' => $status]);
      (new Response())->redirect('/students');
      return;
    }

    if ($action === 'assign_class') {
      $classId = (int)($_POST['class_id'] ?? 0);
      if ($classId <= 0) {
        (new Response())->redirect('/students');
        return;
      }

      $classStmt = $pdo->prepare('SELECT id, academic_year_id FROM classes WHERE id = ?');
      $classStmt->execute([$classId]);
      $class = $classStmt->fetch();
      if (!$class) {
        (new Response())->redirect('/students');
        return;
      }

      $pdo->beginTransaction();
      try {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("UPDATE student_class_enrollments SET end_date = ? WHERE student_id IN ($in) AND end_date IS NULL")
          ->execute(array_merge([date('Y-m-d')], $ids));

        $insert = $pdo->prepare('INSERT INTO student_class_enrollments (student_id, class_id, academic_year_id, start_date) VALUES (?,?,?,?)');
        foreach ($ids as $studentId) {
          $insert->execute([$studentId, $class['id'], $class['academic_year_id'] ?? null, date('Y-m-d')]);
        }

        $pdo->commit();
        Audit::log('students.bulk_assign_class', 'students', implode(',', $ids), null, ['class_id' => $classId]);
      } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
      }

      (new Response())->redirect('/students');
      return;
    }

    (new Response())->redirect('/students');
  }

  public function create(): void
  {
    if (!$this->guard('students.create')) return;
    $pdo = Db::pdo();
    $classRows = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('students/create.php', [
      'classes' => $classRows,
    ]);
  }

  public function store(): void
  {
    if (!$this->guard('students.create')) return;
    $pdo = Db::pdo();

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $dob = $this->parseDate($_POST['dob'] ?? '');
    $identityNumber = trim($_POST['identity_number'] ?? '');
    $status = $_POST['status'] ?? 'ACTIVE';
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $classId = trim($_POST['class_id'] ?? '');
    $isRcic = !empty($_POST['is_rcic']) ? 1 : 0;
    $admissionType = strtoupper(trim($_POST['admission_type'] ?? 'NEW'));
    if (!in_array($admissionType, ['NEW','TRANSFER'], true)) {
      $admissionType = 'NEW';
    }
    $docBirthUrl = trim($_POST['doc_birth_cert_url'] ?? '');
    $docBaptismUrl = trim($_POST['doc_baptism_cert_url'] ?? '');
    $docTransferUrl = trim($_POST['doc_transfer_letter_url'] ?? '');
    $docFhcUrl = trim($_POST['doc_fhc_cert_url'] ?? '');
    $docBirth = $docBirthUrl !== '' ? 1 : 0;
    $docBaptism = $docBaptismUrl !== '' ? 1 : 0;
    $docTransfer = $docTransferUrl !== '' ? 1 : 0;
    $docFhc = $docFhcUrl !== '' ? 1 : 0;

    $errors = [];
    if ($firstName === '' || $lastName === '') {
      $errors[] = 'First name and last name are required.';
    }
    if ($docBirthUrl !== '' && !filter_var($docBirthUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'Birth certificate link must be a valid URL.';
    }
    if ($docBaptismUrl !== '' && !filter_var($docBaptismUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'Baptism certificate link must be a valid URL.';
    }
    if ($docTransferUrl !== '' && !filter_var($docTransferUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'Transfer letter link must be a valid URL.';
    }
    if ($docFhcUrl !== '' && !filter_var($docFhcUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'FHC certificate link must be a valid URL.';
    }
    if ($admissionType === 'TRANSFER') {
      if ($docBirthUrl === '') $errors[] = 'Birth certificate link is required for transfer students.';
      if ($docBaptismUrl === '') $errors[] = 'Baptism certificate link is required for transfer students.';
      if ($docTransferUrl === '') $errors[] = 'Transfer letter link is required for transfer students.';
    } else {
      if ($docBirthUrl === '') $errors[] = 'Birth certificate link is required for new students.';
      if ($docBaptismUrl === '') $errors[] = 'Baptism certificate link is required for new students.';
    }

    if ($errors) {
      $this->renderFormWithErrors('students/create.php', $errors);
      return;
    }

    $fullName = trim($firstName . ' ' . $lastName);

    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, full_name, dob, identity_number, is_rcic, address, status, notes, admission_type, doc_birth_cert, doc_baptism_cert, doc_transfer_letter, doc_fhc_cert, doc_birth_cert_url, doc_baptism_cert_url, doc_transfer_letter_url, doc_fhc_cert_url) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
      $stmt->execute([$firstName, $lastName, $fullName, $dob, $identityNumber ?: null, $isRcic, $address ?: null, $status, $notes ?: null, $admissionType, $docBirth, $docBaptism, $docTransfer, $docFhc, $docBirthUrl ?: null, $docBaptismUrl ?: null, $docTransferUrl ?: null, $docFhcUrl ?: null]);
      $studentId = (int)$pdo->lastInsertId();

      if ($classId !== '') {
        $classStmt = $pdo->prepare('SELECT id, academic_year_id FROM classes WHERE id = ?');
        $classStmt->execute([$classId]);
        $class = $classStmt->fetch();
        if ($class) {
          $insertEnroll = $pdo->prepare('INSERT INTO student_class_enrollments (student_id, class_id, academic_year_id, start_date) VALUES (?,?,?,?)');
          $insertEnroll->execute([$studentId, $class['id'], $class['academic_year_id'] ?? null, date('Y-m-d')]);
        }
      }

      if ($isRcic) {
        $pdo->prepare('DELETE FROM student_sacrament_info WHERE student_id = ?')->execute([$studentId]);
      } else {
        $this->upsertSacraments($pdo, $studentId, $_POST);
      }
      $this->syncGuardians($pdo, $studentId, $_POST['guardians'] ?? []);

      $pdo->commit();
      Audit::log('students.create', 'students', (string)$studentId, null, [
        'full_name' => $fullName,
        'status' => $status,
        'class_id' => $classId !== '' ? (int)$classId : null,
      ]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/students');
  }

  public function show(Request $request): void
  {
    if (!$this->guard('students.view')) return;
    $id = (int)$request->param('id');
    $pdo = Db::pdo();

    $student = $this->fetchStudent($pdo, $id);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $sacrament = $this->fetchSacraments($pdo, $id);
    $guardians = $this->fetchGuardians($pdo, $id);
    $teachers = [];
    if (!empty($student['class_id'])) {
      $teachers = $this->getClassTeachers($pdo, [(int)$student['class_id']]);
      $teachers = $teachers[(int)$student['class_id']] ?? [];
    }

    (new Response())->view('students/show.php', [
      'student' => $student,
      'sacrament' => $sacrament,
      'guardians' => $guardians,
      'teachers' => $teachers,
    ]);
  }

  public function edit(Request $request): void
  {
    if (!$this->guard('students.edit')) return;
    $id = (int)$request->param('id');
    $pdo = Db::pdo();

    $student = $this->fetchStudent($pdo, $id);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $sacrament = $this->fetchSacraments($pdo, $id);
    $guardians = $this->fetchGuardians($pdo, $id);
    $classRows = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('students/edit.php', [
      'student' => $student,
      'sacrament' => $sacrament,
      'guardians' => $guardians,
      'classes' => $classRows,
    ]);
  }

  public function update(Request $request): void
  {
    if (!$this->guard('students.edit')) return;
    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $before = $this->fetchStudent($pdo, $id) ?: null;

    $student = $this->fetchStudent($pdo, $id);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $dob = $this->parseDate($_POST['dob'] ?? '');
    $identityNumber = trim($_POST['identity_number'] ?? '');
    $status = $_POST['status'] ?? 'ACTIVE';
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $classId = trim($_POST['class_id'] ?? '');
    $isRcic = !empty($_POST['is_rcic']) ? 1 : 0;
    $admissionType = strtoupper(trim($_POST['admission_type'] ?? 'NEW'));
    if (!in_array($admissionType, ['NEW','TRANSFER'], true)) {
      $admissionType = 'NEW';
    }
    $docBirthUrl = trim($_POST['doc_birth_cert_url'] ?? '');
    $docBaptismUrl = trim($_POST['doc_baptism_cert_url'] ?? '');
    $docTransferUrl = trim($_POST['doc_transfer_letter_url'] ?? '');
    $docFhcUrl = trim($_POST['doc_fhc_cert_url'] ?? '');
    $docBirth = $docBirthUrl !== '' ? 1 : 0;
    $docBaptism = $docBaptismUrl !== '' ? 1 : 0;
    $docTransfer = $docTransferUrl !== '' ? 1 : 0;
    $docFhc = $docFhcUrl !== '' ? 1 : 0;

    $errors = [];
    if ($firstName === '' || $lastName === '') {
      $errors[] = 'First name and last name are required.';
    }
    if ($docBirthUrl !== '' && !filter_var($docBirthUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'Birth certificate link must be a valid URL.';
    }
    if ($docBaptismUrl !== '' && !filter_var($docBaptismUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'Baptism certificate link must be a valid URL.';
    }
    if ($docTransferUrl !== '' && !filter_var($docTransferUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'Transfer letter link must be a valid URL.';
    }
    if ($docFhcUrl !== '' && !filter_var($docFhcUrl, FILTER_VALIDATE_URL)) {
      $errors[] = 'FHC certificate link must be a valid URL.';
    }
    if ($admissionType === 'TRANSFER') {
      if ($docBirthUrl === '') $errors[] = 'Birth certificate link is required for transfer students.';
      if ($docBaptismUrl === '') $errors[] = 'Baptism certificate link is required for transfer students.';
      if ($docTransferUrl === '') $errors[] = 'Transfer letter link is required for transfer students.';
    } else {
      if ($docBirthUrl === '') $errors[] = 'Birth certificate link is required for new students.';
      if ($docBaptismUrl === '') $errors[] = 'Baptism certificate link is required for new students.';
    }

    if ($errors) {
      $this->renderFormWithErrors('students/edit.php', $errors, ['student' => $student]);
      return;
    }

    $fullName = trim($firstName . ' ' . $lastName);

    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('UPDATE students SET first_name=?, last_name=?, full_name=?, dob=?, identity_number=?, is_rcic=?, address=?, status=?, notes=?, admission_type=?, doc_birth_cert=?, doc_baptism_cert=?, doc_transfer_letter=?, doc_fhc_cert=?, doc_birth_cert_url=?, doc_baptism_cert_url=?, doc_transfer_letter_url=?, doc_fhc_cert_url=? WHERE id=?');
      $stmt->execute([$firstName, $lastName, $fullName, $dob, $identityNumber ?: null, $isRcic, $address ?: null, $status, $notes ?: null, $admissionType, $docBirth, $docBaptism, $docTransfer, $docFhc, $docBirthUrl ?: null, $docBaptismUrl ?: null, $docTransferUrl ?: null, $docFhcUrl ?: null, $id]);

      // reset active enrollment
      $pdo->prepare('UPDATE student_class_enrollments SET end_date = ? WHERE student_id = ? AND end_date IS NULL')->execute([date('Y-m-d'), $id]);

      if ($classId !== '') {
        $classStmt = $pdo->prepare('SELECT id, academic_year_id FROM classes WHERE id = ?');
        $classStmt->execute([$classId]);
        $class = $classStmt->fetch();
        if ($class) {
          $insertEnroll = $pdo->prepare('INSERT INTO student_class_enrollments (student_id, class_id, academic_year_id, start_date) VALUES (?,?,?,?)');
          $insertEnroll->execute([$id, $class['id'], $class['academic_year_id'] ?? null, date('Y-m-d')]);
        }
      }

      if ($isRcic) {
        $pdo->prepare('DELETE FROM student_sacrament_info WHERE student_id = ?')->execute([$id]);
      } else {
        $this->upsertSacraments($pdo, $id, $_POST);
      }
      $this->syncGuardians($pdo, $id, $_POST['guardians'] ?? []);

      $pdo->commit();
      Audit::log('students.update', 'students', (string)$id, $before ?: null, [
        'full_name' => $fullName,
        'status' => $status,
        'class_id' => $classId !== '' ? (int)$classId : null,
      ]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/students/' . $id);
  }

  private function renderFormWithErrors(string $view, array $errors, array $extra = []): void
  {
    $pdo = Db::pdo();
    $classRows = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view($view, array_merge($extra, [
      'errors' => $errors,
      'classes' => $classRows,
    ]));
  }

  private function upsertSacraments(\PDO $pdo, int $studentId, array $payload): void
  {
    $church = trim($payload['church_of_baptism'] ?? '');
    $place = trim($payload['place_of_baptism'] ?? '');
    $baptismDate = $this->parseDate($payload['date_of_baptism'] ?? '');
    $godfather = trim($payload['godfather'] ?? '');
    $godmother = trim($payload['godmother'] ?? '');
    $fhcDate = $this->parseDate($payload['date_of_fhc'] ?? '');
    $fhcPlace = trim($payload['place_of_fhc'] ?? '');
    $confirmationDate = $this->parseDate($payload['date_of_confirmation'] ?? '');
    $confirmationPlace = trim($payload['place_of_confirmation'] ?? '');

    $stmt = $pdo->prepare(
      'INSERT INTO student_sacrament_info (student_id, church_of_baptism, place_of_baptism, date_of_baptism, godfather, godmother, date_of_first_holy_communion, place_of_first_holy_communion, date_of_confirmation, place_of_confirmation)
       VALUES (?,?,?,?,?,?,?,?,?,?)
       ON DUPLICATE KEY UPDATE
        church_of_baptism=VALUES(church_of_baptism),
        place_of_baptism=VALUES(place_of_baptism),
        date_of_baptism=VALUES(date_of_baptism),
        godfather=VALUES(godfather),
        godmother=VALUES(godmother),
        date_of_first_holy_communion=VALUES(date_of_first_holy_communion),
        place_of_first_holy_communion=VALUES(place_of_first_holy_communion),
        date_of_confirmation=VALUES(date_of_confirmation),
        place_of_confirmation=VALUES(place_of_confirmation)'
    );

    $stmt->execute([
      $studentId,
      $church ?: null,
      $place ?: null,
      $baptismDate,
      $godfather ?: null,
      $godmother ?: null,
      $fhcDate,
      $fhcPlace ?: null,
      $confirmationDate,
      $confirmationPlace ?: null,
    ]);
  }

  private function syncGuardians(\PDO $pdo, int $studentId, array $guardians): void
  {
    $pdo->prepare('DELETE FROM student_guardians WHERE student_id = ?')->execute([$studentId]);

    foreach ($guardians as $guardian) {
      $name = trim($guardian['full_name'] ?? '');
      if ($name === '') {
        continue;
      }

      $relationship = trim($guardian['relationship_label'] ?? '');
      $phone = trim($guardian['phone'] ?? '');
      $email = trim($guardian['email'] ?? '');
      $isPrimary = !empty($guardian['is_primary']) ? 1 : 0;

      $stmt = $pdo->prepare('INSERT INTO student_guardians (student_id, full_name, relationship_label, phone, email, is_primary) VALUES (?,?,?,?,?,?)');
      $stmt->execute([$studentId, $name, $relationship ?: null, $phone ?: null, $email ?: null, $isPrimary]);
    }
  }

  private function fetchStudent(\PDO $pdo, int $id): ?array
  {
    $stmt = $pdo->prepare("SELECT s.*, c.name AS class_name, ce.class_id
                           FROM students s
                           LEFT JOIN student_class_enrollments ce
                             ON ce.student_id = s.id AND ce.end_date IS NULL
                           LEFT JOIN classes c ON c.id = ce.class_id
                           WHERE s.id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row && isset($row['dob'])) {
      $row['dob_display'] = $this->formatDate($row['dob']);
      $row['age_display'] = $this->calcAge($row['dob']);
    }

    return $row ?: null;
  }

  private function fetchSacraments(\PDO $pdo, int $studentId): ?array
  {
    $stmt = $pdo->prepare('SELECT * FROM student_sacrament_info WHERE student_id = ? LIMIT 1');
    $stmt->execute([$studentId]);
    $row = $stmt->fetch();

    if ($row) {
      $row['date_of_baptism_display'] = $this->formatDate($row['date_of_baptism'] ?? null);
      $row['date_of_first_holy_communion_display'] = $this->formatDate($row['date_of_first_holy_communion'] ?? null);
      $row['date_of_confirmation_display'] = $this->formatDate($row['date_of_confirmation'] ?? null);
    }

    return $row ?: null;
  }

  private function fetchGuardians(\PDO $pdo, int $studentId): array
  {
    $stmt = $pdo->prepare('SELECT * FROM student_guardians WHERE student_id = ? ORDER BY is_primary DESC, id ASC');
    $stmt->execute([$studentId]);
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

  private function loadStudents(\PDO $pdo, string $search, string $status, string $classId, int $page, int $perPage): array
  {
    $where = [];
    $params = [];

    if ($search !== '') {
      $where[] = '(s.full_name LIKE :q OR s.identity_number LIKE :q)';
      $params[':q'] = '%' . $search . '%';
    }

    if ($status !== '') {
      $where[] = 's.status = :status';
      $params[':status'] = $status;
    }

    if ($classId !== '') {
      $where[] = 'ce.class_id = :class_id';
      $params[':class_id'] = $classId;
    }

    $countSql = "SELECT COUNT(*) AS total
            FROM students s
            LEFT JOIN student_class_enrollments ce
              ON ce.student_id = s.id AND ce.end_date IS NULL
            LEFT JOIN classes c ON c.id = ce.class_id";

    if ($where) {
      $countSql .= ' WHERE ' . implode(' AND ', $where);
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT s.*, c.name AS class_name, ce.class_id AS class_id
            FROM students s
            LEFT JOIN student_class_enrollments ce
              ON ce.student_id = s.id AND ce.end_date IS NULL
            LEFT JOIN classes c ON c.id = ce.class_id";

    if ($where) {
      $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY s.created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    foreach ($students as &$student) {
      if (!empty($student['dob'])) {
        $student['dob_display'] = $this->formatDate($student['dob']);
        $student['age_display'] = $this->calcAge($student['dob']);
      }
      $admission = $student['admission_type'] ?? 'NEW';
      $missing = [];
      if ($admission === 'TRANSFER') {
        if (empty($student['doc_birth_cert_url'])) $missing[] = 'Birth';
        if (empty($student['doc_baptism_cert_url'])) $missing[] = 'Baptism';
        if (empty($student['doc_transfer_letter_url'])) $missing[] = 'Transfer';
      } else {
        if (empty($student['doc_birth_cert_url'])) $missing[] = 'Birth';
        if (empty($student['doc_baptism_cert_url'])) $missing[] = 'Baptism';
      }
      $student['docs_missing'] = $missing;
    }
    unset($student);

    $classIds = [];
    foreach ($students as $student) {
      if (!empty($student['class_id'])) {
        $classIds[] = (int)$student['class_id'];
      }
    }
    $classTeachers = $this->getClassTeachers($pdo, array_values(array_unique($classIds)));

    $pagination = [
      'page' => $page,
      'perPage' => $perPage,
      'total' => $total,
      'totalPages' => $totalPages,
      'hasPrev' => $page > 1,
      'hasNext' => $page < $totalPages,
    ];

    return [$students, $classTeachers, $pagination];
  }

  private function buildQuery(array $params): string
  {
    $filtered = [];
    foreach ($params as $key => $value) {
      if ($value === '' || $value === null) continue;
      $filtered[$key] = $value;
    }
    return http_build_query($filtered);
  }

  private function calcAge(?string $dob): ?int
  {
    if (!$dob) return null;
    try {
      $birth = new \DateTime($dob);
      $today = new \DateTime('today');
      return (int)$birth->diff($today)->y;
    } catch (\Throwable $e) {
      return null;
    }
  }

}
