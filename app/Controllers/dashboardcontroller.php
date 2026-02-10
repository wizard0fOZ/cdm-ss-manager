<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;

final class DashboardController extends BaseController
{
  public function index(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();
    $activeYear = $pdo->query('SELECT id, label FROM academic_years WHERE is_active = 1 LIMIT 1')->fetch();
    $studentsCount = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    if ($activeYear) {
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM classes WHERE academic_year_id = ?');
      $stmt->execute([(int)$activeYear['id']]);
      $classesCount = (int)$stmt->fetchColumn();
    } else {
      $classesCount = (int)$pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn();
    }
    $teachersCount = (int)$pdo->query("SELECT COUNT(DISTINCT u.id)
      FROM users u
      JOIN user_roles ur ON ur.user_id = u.id
      JOIN roles r ON r.id = ur.role_id
      WHERE r.code = 'TEACHER' AND u.status = 'ACTIVE'")->fetchColumn();

    $attendanceRate = null;
    try {
      $datesStmt = $pdo->query("SELECT DISTINCT session_date FROM class_sessions WHERE DAYOFWEEK(session_date) = 1 ORDER BY session_date DESC LIMIT 4");
      $dates = array_map(fn($row) => $row['session_date'], $datesStmt->fetchAll());
      if ($dates) {
        $placeholders = implode(',', array_fill(0, count($dates), '?'));
        $totStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_records ar JOIN class_sessions cs ON cs.id = ar.class_session_id WHERE cs.session_date IN ($placeholders) AND ar.status IS NOT NULL");
        $totStmt->execute($dates);
        $totalMarked = (int)$totStmt->fetchColumn();
        $presentStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_records ar JOIN class_sessions cs ON cs.id = ar.class_session_id WHERE cs.session_date IN ($placeholders) AND ar.status = 'PRESENT'");
        $presentStmt->execute($dates);
        $presentCount = (int)$presentStmt->fetchColumn();
        if ($totalMarked > 0) {
          $attendanceRate = round(($presentCount / $totalMarked) * 100, 1);
        }
      }
    } catch (\Throwable $e) {
      $attendanceRate = null;
    }

    $pendingLessons = 0;
    try {
      $from = date('Y-m-d');
      $to = (new \DateTime('+14 days'))->format('Y-m-d');
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM lesson_plans WHERE status = 'DRAFT' AND session_date BETWEEN ? AND ?");
      $stmt->execute([$from, $to]);
      $pendingLessons = (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {
      $pendingLessons = 0;
    }

    $start = new \DateTime('today');
    $end = (clone $start)->modify('+14 days')->setTime(23, 59, 59);

    $filters = ['(e.start_datetime <= ? AND e.end_datetime >= ?)'];
    $params = [$end->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s')];

    if (!$isAdmin) {
      $classes = $this->getTeacherClasses($pdo, $userId);
      $classIds = array_map('intval', array_column($classes, 'id'));
      if ($classIds) {
        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        $filters[] = "(e.scope = 'GLOBAL' OR (e.scope = 'CLASS' AND e.class_id IN ($placeholders)))";
        $params = array_merge($params, $classIds);
      } else {
        $filters[] = "e.scope = 'GLOBAL'";
      }
    }

    $sql = 'SELECT e.*, c.name AS class_name FROM calendar_events e LEFT JOIN classes c ON c.id = e.class_id';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY e.start_datetime ASC LIMIT 8';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $upcoming = $stmt->fetchAll();

    $aFilters = ["a.status = 'PUBLISHED'", 'a.start_at <= NOW()', 'a.end_at >= NOW()'];
    $aParams = [];
    if (!$isAdmin) {
      $classes = $this->getTeacherClasses($pdo, $userId);
      $classIds = array_map('intval', array_column($classes, 'id'));
      if ($classIds) {
        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        $aFilters[] = "(a.scope = 'GLOBAL' OR (a.scope = 'CLASS' AND a.class_id IN ($placeholders)))";
        $aParams = array_merge($aParams, $classIds);
      } else {
        $aFilters[] = "a.scope = 'GLOBAL'";
      }
    }
    $aSql = 'SELECT a.*, c.name AS class_name FROM announcements a LEFT JOIN classes c ON c.id = a.class_id';
    if ($aFilters) {
      $aSql .= ' WHERE ' . implode(' AND ', $aFilters);
    }
    $aSql .= ' ORDER BY (CASE WHEN a.is_pinned = 1 AND (a.pin_until IS NULL OR a.pin_until >= NOW()) THEN 1 ELSE 0 END) DESC, a.priority DESC, a.start_at DESC LIMIT 3';
    $aStmt = $pdo->prepare($aSql);
    $aStmt->execute($aParams);
    $announcements = $aStmt->fetchAll();

    (new Response())->view('dashboard/index.php', [
      'upcoming' => $upcoming,
      'announcements' => $announcements,
      'studentsCount' => $studentsCount,
      'classesCount' => $classesCount,
      'teachersCount' => $teachersCount,
      'activeYear' => $activeYear,
      'attendanceRate' => $attendanceRate,
      'pendingLessons' => $pendingLessons,
    ]);
  }

  public function health(): void
  {
    $dbOk = false;
    try {
      Db::pdo()->query('SELECT 1');
      $dbOk = true;
    } catch (\Throwable) {
      // DB unreachable
    }

    (new Response())->json([
      'ok' => $dbOk,
      'time' => date('c'),
    ]);
  }

  private function getTeacherClasses(\PDO $pdo, int $userId): array
  {
    if ($userId <= 0) return [];
    $stmt = $pdo->prepare('SELECT c.id, c.name FROM class_teacher_assignments cta JOIN classes c ON c.id = cta.class_id WHERE cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE()) ORDER BY c.name ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }
}
