<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;

final class CalendarController
{
  private array $categories = ['HOLIDAY','NO_CLASS','CAMP','TRAINING','SPECIAL_EVENT','OTHER'];

  public function index(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $classes = $isAdmin ? $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll() : $this->getTeacherClasses($pdo, $userId);

    $activeYearId = 0;
    foreach ($years as $year) {
      if ((int)$year['is_active'] === 1) {
        $activeYearId = (int)$year['id'];
        break;
      }
    }

    $yearFilter = (int)($request->input('year_id', $activeYearId));
    $classFilter = (int)($request->input('class_id', 0));
    $termFilter = (int)($request->input('term_id', 0));
    $category = trim((string)$request->input('category', ''));
    $scope = trim((string)$request->input('scope', ''));
    $q = trim((string)$request->input('q', ''));
    $myClassesOnly = (int)($request->input('my_classes', 0)) === 1;
    $rangeDays = (int)($request->input('range_days', 14));
    if (!in_array($rangeDays, [14, 30], true)) {
      $rangeDays = 14;
    }

    $monthParam = trim((string)$request->input('month', ''));
    if ($monthParam !== '' && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
      $monthStart = new \DateTime($monthParam . '-01');
    } else {
      $monthStart = new \DateTime('first day of this month');
    }
    $monthStart->setTime(0, 0, 0);
    $monthEnd = (clone $monthStart)->modify('last day of this month')->setTime(23, 59, 59);
    $monthLabel = $monthStart->format('F Y');
    $monthPrev = (clone $monthStart)->modify('-1 month')->format('Y-m');
    $monthNext = (clone $monthStart)->modify('+1 month')->format('Y-m');

    $calendarStart = (clone $monthStart)->modify('last sunday')->setTime(0, 0, 0);
    $calendarEnd = (clone $monthEnd)->modify('next saturday')->setTime(23, 59, 59);

    $terms = [];
    if ($yearFilter > 0) {
      $stmt = $pdo->prepare('SELECT id, label, start_date, end_date FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC');
      $stmt->execute([$yearFilter]);
      $terms = $stmt->fetchAll();
    }

    $termRow = null;
    if ($termFilter > 0) {
      foreach ($terms as $term) {
        if ((int)$term['id'] === $termFilter) {
          $termRow = $term;
          break;
        }
      }
      if (!$termRow) {
        $termFilter = 0;
      }
    }

    $filters = [];
    $params = [];
    if ($yearFilter > 0) {
      $filters[] = 'e.academic_year_id = ?';
      $params[] = $yearFilter;
    }
    if (!$isAdmin && $classFilter > 0) {
      $allowedIds = array_map('intval', array_column($classes, 'id'));
      if (!in_array($classFilter, $allowedIds, true)) {
        $classFilter = 0;
      }
    }

    if ($classFilter > 0) {
      $filters[] = 'e.class_id = ?';
      $params[] = $classFilter;
    }
    if ($category !== '') {
      $filters[] = 'e.category = ?';
      $params[] = $category;
    }
    if ($scope !== '') {
      $filters[] = 'e.scope = ?';
      $params[] = $scope;
    }
    if ($q !== '') {
      $filters[] = '(e.title LIKE ? OR e.description LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    if ($termFilter > 0 && $termRow) {
      $filters[] = '(e.start_datetime <= ? AND e.end_datetime >= ?)';
      $params[] = $termRow['end_date'] . ' 23:59:59';
      $params[] = $termRow['start_date'] . ' 00:00:00';
    }

    if (!$isAdmin) {
      $teacherClassIds = array_map('intval', array_column($classes, 'id'));
      if ($teacherClassIds) {
        $placeholders = implode(',', array_fill(0, count($teacherClassIds), '?'));
        if ($myClassesOnly) {
          $filters[] = "(e.scope = 'CLASS' AND e.class_id IN ($placeholders))";
          $params = array_merge($params, $teacherClassIds);
        } else {
          $filters[] = "(e.scope = 'GLOBAL' OR (e.scope = 'CLASS' AND e.class_id IN ($placeholders)))";
          $params = array_merge($params, $teacherClassIds);
        }
      } else {
        $filters[] = "e.scope = 'GLOBAL'";
      }
    }

    $sql = 'SELECT e.*, c.name AS class_name, y.label AS year_label FROM calendar_events e '
      . 'JOIN academic_years y ON y.id = e.academic_year_id '
      . 'LEFT JOIN classes c ON c.id = e.class_id';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY e.start_datetime DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();


    $upcomingStart = new \DateTime('today');
    $upcomingEnd = (clone $upcomingStart)->modify('+' . $rangeDays . ' days')->setTime(23, 59, 59);
    $upcomingFilters = $filters;
    $upcomingParams = $params;
    $upcomingFilters[] = '(e.start_datetime <= ? AND e.end_datetime >= ?)';
    $upcomingParams[] = $upcomingEnd->format('Y-m-d H:i:s');
    $upcomingParams[] = $upcomingStart->format('Y-m-d H:i:s');

    $upcomingSql = 'SELECT e.*, c.name AS class_name FROM calendar_events e LEFT JOIN classes c ON c.id = e.class_id';
    if ($upcomingFilters) {
      $upcomingSql .= ' WHERE ' . implode(' AND ', $upcomingFilters);
    }
    $upcomingSql .= ' ORDER BY e.start_datetime ASC LIMIT 12';
    $upcomingStmt = $pdo->prepare($upcomingSql);
    $upcomingStmt->execute($upcomingParams);
    $upcoming = $upcomingStmt->fetchAll();

    $calendarFilters = $filters;
    $calendarParams = $params;
    $calendarFilters[] = '(e.start_datetime <= ? AND e.end_datetime >= ?)';
    $calendarParams[] = $calendarEnd->format('Y-m-d H:i:s');
    $calendarParams[] = $calendarStart->format('Y-m-d H:i:s');

    $calendarSql = 'SELECT e.*, c.name AS class_name FROM calendar_events e LEFT JOIN classes c ON c.id = e.class_id';
    if ($calendarFilters) {
      $calendarSql .= ' WHERE ' . implode(' AND ', $calendarFilters);
    }
    $calendarSql .= ' ORDER BY e.start_datetime ASC';
    $calendarStmt = $pdo->prepare($calendarSql);
    $calendarStmt->execute($calendarParams);
    $calendarEvents = $calendarStmt->fetchAll();

    $calendarMap = [];
    foreach ($calendarEvents as $event) {
      $start = new \DateTime($event['start_datetime']);
      $end = new \DateTime($event['end_datetime']);
      $day = (clone $start)->setTime(0, 0, 0);
      $endDay = (clone $end)->setTime(0, 0, 0);
      while ($day <= $endDay) {
        $key = $day->format('Y-m-d');
        if (!isset($calendarMap[$key])) $calendarMap[$key] = [];
        $calendarMap[$key][] = $event;
        $day->modify('+1 day');
      }
    }

    (new Response())->view('calendar/index.php', [
      'events' => $events,
      'years' => $years,
      'classes' => $classes,
      'categories' => $this->categories,
      'yearFilter' => $yearFilter,
      'classFilter' => $classFilter,
      'termFilter' => $termFilter,
      'category' => $category,
      'scope' => $scope,
      'q' => $q,
      'myClassesOnly' => $myClassesOnly,
      'terms' => $terms,
      'rangeDays' => $rangeDays,
      'upcoming' => $upcoming,
      'monthLabel' => $monthLabel,
      'monthStart' => $monthStart,
      'monthPrev' => $monthPrev,
      'monthNext' => $monthNext,
      'calendarStart' => $calendarStart,
      'calendarEnd' => $calendarEnd,
      'calendarMap' => $calendarMap,
      'isAdmin' => $isAdmin,
    ]);
  }

  public function export(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $yearFilter = (int)($request->input('year_id', 0));
    $classFilter = (int)($request->input('class_id', 0));
    $termFilter = (int)($request->input('term_id', 0));
    $category = trim((string)$request->input('category', ''));
    $scope = trim((string)$request->input('scope', ''));
    $q = trim((string)$request->input('q', ''));
    $myClassesOnly = (int)($request->input('my_classes', 0)) === 1;

    $classes = $isAdmin ? $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll() : $this->getTeacherClasses($pdo, $userId);
    if (!$isAdmin && $classFilter > 0) {
      $allowedIds = array_map('intval', array_column($classes, 'id'));
      if (!in_array($classFilter, $allowedIds, true)) {
        $classFilter = 0;
      }
    }

    $terms = [];
    if ($yearFilter > 0) {
      $stmt = $pdo->prepare('SELECT id, label, start_date, end_date FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC');
      $stmt->execute([$yearFilter]);
      $terms = $stmt->fetchAll();
    }
    $termRow = null;
    if ($termFilter > 0) {
      foreach ($terms as $term) {
        if ((int)$term['id'] === $termFilter) {
          $termRow = $term;
          break;
        }
      }
      if (!$termRow) {
        $termFilter = 0;
      }
    }

    $filters = [];
    $params = [];
    if ($yearFilter > 0) {
      $filters[] = 'e.academic_year_id = ?';
      $params[] = $yearFilter;
    }
    if ($classFilter > 0) {
      $filters[] = 'e.class_id = ?';
      $params[] = $classFilter;
    }
    if ($category !== '') {
      $filters[] = 'e.category = ?';
      $params[] = $category;
    }
    if ($scope !== '') {
      $filters[] = 'e.scope = ?';
      $params[] = $scope;
    }
    if ($q !== '') {
      $filters[] = '(e.title LIKE ? OR e.description LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
    }
    if ($termFilter > 0 && $termRow) {
      $filters[] = '(e.start_datetime <= ? AND e.end_datetime >= ?)';
      $params[] = $termRow['end_date'] . ' 23:59:59';
      $params[] = $termRow['start_date'] . ' 00:00:00';
    }
    if (!$isAdmin) {
      $teacherClassIds = array_map('intval', array_column($classes, 'id'));
      if ($teacherClassIds) {
        $placeholders = implode(',', array_fill(0, count($teacherClassIds), '?'));
        if ($myClassesOnly) {
          $filters[] = "(e.scope = 'CLASS' AND e.class_id IN ($placeholders))";
          $params = array_merge($params, $teacherClassIds);
        } else {
          $filters[] = "(e.scope = 'GLOBAL' OR (e.scope = 'CLASS' AND e.class_id IN ($placeholders)))";
          $params = array_merge($params, $teacherClassIds);
        }
      } else {
        $filters[] = "e.scope = 'GLOBAL'";
      }
    }

    $sql = 'SELECT e.*, c.name AS class_name FROM calendar_events e LEFT JOIN classes c ON c.id = e.class_id';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY e.start_datetime ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="calendar_events.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Title','Category','Scope','Class','Start','End','All Day','Description']);
    foreach ($events as $event) {
      fputcsv($out, [
        $event['title'],
        $event['category'],
        $event['scope'],
        $event['class_name'] ?? '',
        $event['start_datetime'],
        $event['end_datetime'],
        (int)$event['all_day'] === 1 ? 'Yes' : 'No',
        $event['description'] ?? '',
      ]);
    }
    fclose($out);
    exit;
  }

  public function exportIcal(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);
    $pdo = Db::pdo();

    $yearFilter = (int)($request->input('year_id', 0));
    $classFilter = (int)($request->input('class_id', 0));
    $termFilter = (int)($request->input('term_id', 0));
    $category = trim((string)$request->input('category', ''));
    $scope = trim((string)$request->input('scope', ''));
    $q = trim((string)$request->input('q', ''));
    $myClassesOnly = (int)($request->input('my_classes', 0)) === 1;

    $classes = $isAdmin ? $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll() : $this->getTeacherClasses($pdo, $userId);
    if (!$isAdmin && $classFilter > 0) {
      $allowedIds = array_map('intval', array_column($classes, 'id'));
      if (!in_array($classFilter, $allowedIds, true)) {
        $classFilter = 0;
      }
    }

    $terms = [];
    if ($yearFilter > 0) {
      $stmt = $pdo->prepare('SELECT id, label, start_date, end_date FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC');
      $stmt->execute([$yearFilter]);
      $terms = $stmt->fetchAll();
    }
    $termRow = null;
    if ($termFilter > 0) {
      foreach ($terms as $term) {
        if ((int)$term['id'] === $termFilter) {
          $termRow = $term;
          break;
        }
      }
      if (!$termRow) {
        $termFilter = 0;
      }
    }

    $filters = [];
    $params = [];
    if ($yearFilter > 0) {
      $filters[] = 'e.academic_year_id = ?';
      $params[] = $yearFilter;
    }
    if ($classFilter > 0) {
      $filters[] = 'e.class_id = ?';
      $params[] = $classFilter;
    }
    if ($category !== '') {
      $filters[] = 'e.category = ?';
      $params[] = $category;
    }
    if ($scope !== '') {
      $filters[] = 'e.scope = ?';
      $params[] = $scope;
    }
    if ($q !== '') {
      $filters[] = '(e.title LIKE ? OR e.description LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
    }
    if ($termFilter > 0 && $termRow) {
      $filters[] = '(e.start_datetime <= ? AND e.end_datetime >= ?)';
      $params[] = $termRow['end_date'] . ' 23:59:59';
      $params[] = $termRow['start_date'] . ' 00:00:00';
    }
    if (!$isAdmin) {
      $teacherClassIds = array_map('intval', array_column($classes, 'id'));
      if ($teacherClassIds) {
        $placeholders = implode(',', array_fill(0, count($teacherClassIds), '?'));
        if ($myClassesOnly) {
          $filters[] = "(e.scope = 'CLASS' AND e.class_id IN ($placeholders))";
          $params = array_merge($params, $teacherClassIds);
        } else {
          $filters[] = "(e.scope = 'GLOBAL' OR (e.scope = 'CLASS' AND e.class_id IN ($placeholders)))";
          $params = array_merge($params, $teacherClassIds);
        }
      } else {
        $filters[] = "e.scope = 'GLOBAL'";
      }
    }

    $sql = 'SELECT e.*, c.name AS class_name FROM calendar_events e LEFT JOIN classes c ON c.id = e.class_id';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY e.start_datetime ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    $lines = [
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'PRODID:-//CDM SS Manager//Calendar//EN',
      'CALSCALE:GREGORIAN',
      'METHOD:PUBLISH',
    ];

    $now = new \DateTime();
    $dtstamp = $now->format('Ymd\\THis');
    foreach ($events as $event) {
      $uid = 'cdm-' . $event['id'] . '@cdm-ss-manager';
      $title = $this->icalEscape($event['title']);
      $desc = $this->icalEscape($event['description'] ?? '');
      $scope = $event['scope'] === 'CLASS' ? ('Class • ' . ($event['class_name'] ?? '')) : 'Global';
      $desc = trim($desc . ($scope ? \"\\nScope: $scope\" : ''));

      $lines[] = 'BEGIN:VEVENT';
      $lines[] = 'UID:' . $uid;
      $lines[] = 'DTSTAMP:' . $dtstamp;
      if ((int)$event['all_day'] === 1) {
        $start = (new \DateTime($event['start_datetime']))->format('Ymd');
        $end = (new \DateTime($event['end_datetime']))->modify('+1 day')->format('Ymd');
        $lines[] = 'DTSTART;VALUE=DATE:' . $start;
        $lines[] = 'DTEND;VALUE=DATE:' . $end;
      } else {
        $start = (new \DateTime($event['start_datetime']))->format('Ymd\\THis');
        $end = (new \DateTime($event['end_datetime']))->format('Ymd\\THis');
        $lines[] = 'DTSTART:' . $start;
        $lines[] = 'DTEND:' . $end;
      }
      $lines[] = 'SUMMARY:' . $title;
      if ($desc !== '') {
        $lines[] = 'DESCRIPTION:' . $desc;
      }
      $lines[] = 'END:VEVENT';
    }

    $lines[] = 'END:VCALENDAR';

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=\"calendar_events.ics\"');
    echo implode(\"\\r\\n\", $lines);
    exit;
  }

  public function create(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'calendar']);
      return;
    }

    $pdo = Db::pdo();
    $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('calendar/create.php', [
      'years' => $years,
      'classes' => $classes,
      'categories' => $this->categories,
    ]);
  }

  public function store(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'calendar']);
      return;
    }

    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'OTHER';
    $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
    $scope = $_POST['scope'] ?? 'GLOBAL';
    $classId = (int)($_POST['class_id'] ?? 0);
    $allDay = !empty($_POST['all_day']) ? 1 : 0;
    $startDate = $this->parseDate($_POST['start_date'] ?? '');
    $endDate = $this->parseDate($_POST['end_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($allDay) {
      $startTime = '00:00';
      $endTime = '23:59';
    }

    $startAt = $this->combineDateTime($startDate, $startTime);
    $endAt = $this->combineDateTime($endDate ?: $startDate, $endTime);

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if (!in_array($category, $this->categories, true)) $errors[] = 'Category is invalid.';
    if ($academicYearId <= 0) $errors[] = 'Academic year is required.';
    if (!$startAt || !$endAt) $errors[] = 'Start and end date/time are required.';
    if ($startAt && $endAt && $startAt > $endAt) $errors[] = 'Start must be before end.';
    if (!in_array($scope, ['GLOBAL','CLASS'], true)) $errors[] = 'Scope is invalid.';
    if ($scope === 'CLASS' && $classId <= 0) $errors[] = 'Class is required for class scope.';

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
      $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
      (new Response())->view('calendar/create.php', [
        'errors' => $errors,
        'years' => $years,
        'classes' => $classes,
        'categories' => $this->categories,
        'event' => $_POST,
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO calendar_events (academic_year_id, title, category, start_datetime, end_datetime, all_day, scope, class_id, description, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
      $academicYearId,
      $title,
      $category,
      $startAt,
      $endAt,
      $allDay,
      $scope,
      $scope === 'CLASS' ? $classId : null,
      $description ?: null,
      $userId,
    ]);

    Flash::set('success', 'Calendar event created.');
    (new Response())->redirect('/calendar');
  }

  public function edit(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'calendar']);
      return;
    }

    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM calendar_events WHERE id = ?');
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if (!$event) {
      (new Response())->status(404)->html('Event not found');
      return;
    }

    $event['start_date_display'] = $this->formatDate($event['start_datetime'] ?? null);
    $event['end_date_display'] = $this->formatDate($event['end_datetime'] ?? null);
    $event['start_time_display'] = $this->formatTime($event['start_datetime'] ?? null);
    $event['end_time_display'] = $this->formatTime($event['end_datetime'] ?? null);

    $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
    $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();

    (new Response())->view('calendar/edit.php', [
      'event' => $event,
      'years' => $years,
      'classes' => $classes,
      'categories' => $this->categories,
    ]);
  }

  public function update(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isStaffAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'calendar']);
      return;
    }

    $id = (int)$request->param('id');
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'OTHER';
    $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
    $scope = $_POST['scope'] ?? 'GLOBAL';
    $classId = (int)($_POST['class_id'] ?? 0);
    $allDay = !empty($_POST['all_day']) ? 1 : 0;
    $startDate = $this->parseDate($_POST['start_date'] ?? '');
    $endDate = $this->parseDate($_POST['end_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($allDay) {
      $startTime = '00:00';
      $endTime = '23:59';
    }

    $startAt = $this->combineDateTime($startDate, $startTime);
    $endAt = $this->combineDateTime($endDate ?: $startDate, $endTime);

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if (!in_array($category, $this->categories, true)) $errors[] = 'Category is invalid.';
    if ($academicYearId <= 0) $errors[] = 'Academic year is required.';
    if (!$startAt || !$endAt) $errors[] = 'Start and end date/time are required.';
    if ($startAt && $endAt && $startAt > $endAt) $errors[] = 'Start must be before end.';
    if (!in_array($scope, ['GLOBAL','CLASS'], true)) $errors[] = 'Scope is invalid.';
    if ($scope === 'CLASS' && $classId <= 0) $errors[] = 'Class is required for class scope.';

    if ($errors) {
      $pdo = Db::pdo();
      $years = $pdo->query('SELECT id, label, is_active FROM academic_years ORDER BY start_date DESC')->fetchAll();
      $classes = $pdo->query('SELECT id, name FROM classes ORDER BY name ASC')->fetchAll();
      (new Response())->view('calendar/edit.php', [
        'errors' => $errors,
        'years' => $years,
        'classes' => $classes,
        'categories' => $this->categories,
        'event' => array_merge(['id' => $id], $_POST),
      ]);
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE calendar_events SET academic_year_id=?, title=?, category=?, start_datetime=?, end_datetime=?, all_day=?, scope=?, class_id=?, description=? WHERE id=?');
    $stmt->execute([
      $academicYearId,
      $title,
      $category,
      $startAt,
      $endAt,
      $allDay,
      $scope,
      $scope === 'CLASS' ? $classId : null,
      $description ?: null,
      $id,
    ]);

    Flash::set('success', 'Calendar event updated.');
    (new Response())->redirect('/calendar');
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
    return null;
  }

  private function combineDateTime(?string $date, string $time): ?string
  {
    if (!$date) return null;
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) return null;
    return $date . ' ' . $time . ':00';
  }

  private function formatDate(?string $value): ?string
  {
    if (!$value) return null;
    return substr($value, 0, 10);
  }

  private function formatTime(?string $value): ?string
  {
    if (!$value) return null;
    return substr($value, 11, 5);
  }

  private function icalEscape(string $value): string
  {
    $value = str_replace(\"\\r\", '', $value);
    $value = str_replace(\"\\n\", '\\\\n', $value);
    $value = str_replace(',', '\\\\,', $value);
    $value = str_replace(';', '\\\\;', $value);
    return $value;
  }

  private function getTeacherClasses(\PDO $pdo, int $userId): array
  {
    if ($userId <= 0) return [];
    $stmt = $pdo->prepare('SELECT c.id, c.name FROM class_teacher_assignments cta JOIN classes c ON c.id = cta.class_id WHERE cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE()) ORDER BY c.name ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }
}
