<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class StudentsController
{
  public function index(): void
  {
    $pdo = Db::pdo();

    $search = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $classId = trim($_GET['class_id'] ?? '');

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

    $sql = "SELECT s.*, c.name AS class_name
            FROM students s
            LEFT JOIN student_class_enrollments ce
              ON ce.student_id = s.id AND ce.end_date IS NULL
            LEFT JOIN classes c ON c.id = ce.class_id";

    if ($where) {
      $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY s.created_at DESC LIMIT 200';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    foreach ($students as &$student) {
      if (!empty($student['dob'])) {
        $student['dob_display'] = $this->formatDate($student['dob']);
        $student['age_display'] = $this->calcAge($student['dob']);
      }
    }
    unset($student);

    $classRows = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('students/index.php', [
      'students' => $students,
      'search' => $search,
      'status' => $status,
      'classId' => $classId,
      'classes' => $classRows,
    ]);
  }

  public function bulk(): void
  {
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
      $stmt = $pdo->prepare(\"UPDATE students SET status=? WHERE id IN ($in)\");
      $stmt->execute(array_merge([$status], $ids));
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
        $pdo->prepare(\"UPDATE student_class_enrollments SET end_date = ? WHERE student_id IN ($in) AND end_date IS NULL\")
          ->execute(array_merge([date('Y-m-d')], $ids));

        $insert = $pdo->prepare('INSERT INTO student_class_enrollments (student_id, class_id, academic_year_id, start_date) VALUES (?,?,?,?)');
        foreach ($ids as $studentId) {
          $insert->execute([$studentId, $class['id'], $class['academic_year_id'] ?? null, date('Y-m-d')]);
        }

        $pdo->commit();
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
    $pdo = Db::pdo();
    $classRows = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('students/create.php', [
      'classes' => $classRows,
    ]);
  }

  public function store(): void
  {
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

    $errors = [];
    if ($firstName === '' || $lastName === '') {
      $errors[] = 'First name and last name are required.';
    }

    if ($errors) {
      $this->renderFormWithErrors('students/create.php', $errors);
      return;
    }

    $fullName = trim($firstName . ' ' . $lastName);

    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, full_name, dob, identity_number, is_rcic, address, status, notes) VALUES (?,?,?,?,?,?,?,?,?)');
      $stmt->execute([$firstName, $lastName, $fullName, $dob, $identityNumber ?: null, $isRcic, $address ?: null, $status, $notes ?: null]);
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
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/students');
  }

  public function show(Request $request): void
  {
    $id = (int)$request->param('id');
    $pdo = Db::pdo();

    $student = $this->fetchStudent($pdo, $id);
    if (!$student) {
      (new Response())->status(404)->html('Student not found');
      return;
    }

    $sacrament = $this->fetchSacraments($pdo, $id);
    $guardians = $this->fetchGuardians($pdo, $id);

    (new Response())->view('students/show.php', [
      'student' => $student,
      'sacrament' => $sacrament,
      'guardians' => $guardians,
    ]);
  }

  public function edit(Request $request): void
  {
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
    $id = (int)$request->param('id');
    $pdo = Db::pdo();

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

    $errors = [];
    if ($firstName === '' || $lastName === '') {
      $errors[] = 'First name and last name are required.';
    }

    if ($errors) {
      $this->renderFormWithErrors('students/edit.php', $errors, ['student' => $student]);
      return;
    }

    $fullName = trim($firstName . ' ' . $lastName);

    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('UPDATE students SET first_name=?, last_name=?, full_name=?, dob=?, identity_number=?, is_rcic=?, address=?, status=?, notes=? WHERE id=?');
      $stmt->execute([$firstName, $lastName, $fullName, $dob, $identityNumber ?: null, $isRcic, $address ?: null, $status, $notes ?: null, $id]);

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

  private function parseDate(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
      return $value;
    }

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
