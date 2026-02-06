<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class AcademicYearsController
{
  public function index(): void
  {
    $pdo = Db::pdo();
    $years = $pdo->query('SELECT * FROM academic_years ORDER BY start_date DESC')->fetchAll();

    (new Response())->view('academic_years/index.php', [
      'years' => $years,
    ]);
  }

  public function create(): void
  {
    (new Response())->view('academic_years/create.php');
  }

  public function store(): void
  {
    $label = trim($_POST['label'] ?? '');
    $startDate = $this->parseDate($_POST['start_date'] ?? '');
    $endDate = $this->parseDate($_POST['end_date'] ?? '');
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    $errors = [];
    if ($label === '') $errors[] = 'Label is required.';
    if (!$startDate || !$endDate) $errors[] = 'Start and end date are required.';
    if ($startDate && $endDate && $startDate > $endDate) $errors[] = 'Start date must be before end date.';

    if ($errors) {
      (new Response())->view('academic_years/create.php', ['errors' => $errors]);
      return;
    }

    $pdo = Db::pdo();
    $pdo->beginTransaction();
    try {
      if ($isActive) {
        $pdo->exec('UPDATE academic_years SET is_active = 0');
      }

      $stmt = $pdo->prepare('INSERT INTO academic_years (label, start_date, end_date, is_active) VALUES (?,?,?,?)');
      $stmt->execute([$label, $startDate, $endDate, $isActive]);

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/academic-years');
  }

  public function edit(Request $request): void
  {
    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM academic_years WHERE id = ?');
    $stmt->execute([$id]);
    $year = $stmt->fetch();

    if (!$year) {
      (new Response())->status(404)->html('Academic year not found');
      return;
    }

    $year['start_date_display'] = $this->formatDate($year['start_date'] ?? null);
    $year['end_date_display'] = $this->formatDate($year['end_date'] ?? null);

    (new Response())->view('academic_years/edit.php', ['year' => $year]);
  }

  public function update(Request $request): void
  {
    $id = (int)$request->param('id');
    $label = trim($_POST['label'] ?? '');
    $startDate = $this->parseDate($_POST['start_date'] ?? '');
    $endDate = $this->parseDate($_POST['end_date'] ?? '');
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    $errors = [];
    if ($label === '') $errors[] = 'Label is required.';
    if (!$startDate || !$endDate) $errors[] = 'Start and end date are required.';
    if ($startDate && $endDate && $startDate > $endDate) $errors[] = 'Start date must be before end date.';

    if ($errors) {
      (new Response())->view('academic_years/edit.php', [
        'errors' => $errors,
        'year' => ['id' => $id, 'label' => $label, 'start_date_display' => $_POST['start_date'] ?? '', 'end_date_display' => $_POST['end_date'] ?? '', 'is_active' => $isActive],
      ]);
      return;
    }

    $pdo = Db::pdo();
    $pdo->beginTransaction();
    try {
      if ($isActive) {
        $pdo->exec('UPDATE academic_years SET is_active = 0');
      }

      $stmt = $pdo->prepare('UPDATE academic_years SET label=?, start_date=?, end_date=?, is_active=? WHERE id=?');
      $stmt->execute([$label, $startDate, $endDate, $isActive, $id]);

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    (new Response())->redirect('/academic-years');
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
}
