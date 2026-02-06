<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;

final class DashboardController
{
  public function index(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $isAdmin = $this->isStaffAdmin($userId);

    $pdo = Db::pdo();
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

    (new Response())->view('dashboard/index.php', [
      'upcoming' => $upcoming,
    ]);
  }

  public function health(): void
  {
    $pdo = Db::pdo();
    $v = $pdo->query('SELECT VERSION()')->fetchColumn();

    (new Response())->json([
      'ok' => true,
      'mysql_version' => $v,
      'time' => date('c'),
    ]);
  }

  private function isStaffAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code IN (?, ?) LIMIT 1');
    $stmt->execute([$userId, 'STAFF_ADMIN', 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  private function getTeacherClasses(\PDO $pdo, int $userId): array
  {
    if ($userId <= 0) return [];
    $stmt = $pdo->prepare('SELECT c.id, c.name FROM class_teacher_assignments cta JOIN classes c ON c.id = cta.class_id WHERE cta.user_id = ? AND (cta.end_date IS NULL OR cta.end_date >= CURDATE()) ORDER BY c.name ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }
}
