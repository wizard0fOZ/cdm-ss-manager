<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;

final class TermsController extends BaseController
{
  public function index(): void
  {
    $pdo = Db::pdo();
    $terms = $pdo->query('SELECT t.*, ay.label AS academic_year_label FROM terms t JOIN academic_years ay ON ay.id = t.academic_year_id ORDER BY ay.start_date DESC, t.term_number ASC')->fetchAll();
    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();

    (new Response())->view('terms/index.php', [
      'terms' => $terms,
      'years' => $years,
    ]);
  }

  public function create(): void
  {
    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();

    (new Response())->view('terms/create.php', [
      'years' => $years,
    ]);
  }

  public function store(): void
  {
    $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
    $termNumber = (int)($_POST['term_number'] ?? 0);
    $label = trim($_POST['label'] ?? '');
    $startDate = $this->parseDate($_POST['start_date'] ?? '');
    $endDate = $this->parseDate($_POST['end_date'] ?? '');

    $errors = [];
    if ($academicYearId <= 0) $errors[] = 'Academic year is required.';
    if ($termNumber <= 0) $errors[] = 'Term number is required.';
    if ($label === '') $errors[] = 'Label is required.';
    if (!$startDate || !$endDate) $errors[] = 'Start and end date are required.';
    if ($startDate && $endDate && $startDate > $endDate) $errors[] = 'Start date must be before end date.';

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
      (new Response())->view('terms/create.php', ['errors' => $errors, 'years' => $years]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO terms (academic_year_id, term_number, label, start_date, end_date) VALUES (?,?,?,?,?)');
    $stmt->execute([$academicYearId, $termNumber, $label, $startDate, $endDate]);

    (new Response())->redirect('/terms');
  }

  public function edit(Request $request): void
  {
    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM terms WHERE id = ?');
    $stmt->execute([$id]);
    $term = $stmt->fetch();

    if (!$term) {
      (new Response())->status(404)->html('Term not found');
      return;
    }

    $term['start_date_display'] = $this->formatDate($term['start_date'] ?? null);
    $term['end_date_display'] = $this->formatDate($term['end_date'] ?? null);
    $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();

    (new Response())->view('terms/edit.php', [
      'term' => $term,
      'years' => $years,
    ]);
  }

  public function update(Request $request): void
  {
    $id = (int)$request->param('id');
    $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
    $termNumber = (int)($_POST['term_number'] ?? 0);
    $label = trim($_POST['label'] ?? '');
    $startDate = $this->parseDate($_POST['start_date'] ?? '');
    $endDate = $this->parseDate($_POST['end_date'] ?? '');

    $errors = [];
    if ($academicYearId <= 0) $errors[] = 'Academic year is required.';
    if ($termNumber <= 0) $errors[] = 'Term number is required.';
    if ($label === '') $errors[] = 'Label is required.';
    if (!$startDate || !$endDate) $errors[] = 'Start and end date are required.';
    if ($startDate && $endDate && $startDate > $endDate) $errors[] = 'Start date must be before end date.';

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label FROM academic_years ORDER BY start_date DESC')->fetchAll();
      (new Response())->view('terms/edit.php', [
        'errors' => $errors,
        'term' => ['id' => $id, 'academic_year_id' => $academicYearId, 'term_number' => $termNumber, 'label' => $label, 'start_date_display' => $_POST['start_date'] ?? '', 'end_date_display' => $_POST['end_date'] ?? ''],
        'years' => $years,
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE terms SET academic_year_id=?, term_number=?, label=?, start_date=?, end_date=? WHERE id=?');
    $stmt->execute([$academicYearId, $termNumber, $label, $startDate, $endDate, $id]);

    (new Response())->redirect('/terms');
  }

}
