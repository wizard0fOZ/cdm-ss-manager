<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Rbac\Rbac;
use App\Core\Support\DateHelper;

abstract class BaseController
{
  protected function guard(string $permission): bool
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

  protected function isStaffAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $override = $_SESSION['_role_override_code'] ?? null;
    if ($override) {
      return in_array($override, ['STAFF_ADMIN','SYSADMIN'], true);
    }
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code IN (?, ?) LIMIT 1');
    $stmt->execute([$userId, 'STAFF_ADMIN', 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  protected function isSysAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code = ? LIMIT 1');
    $stmt->execute([$userId, 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  protected function parseDate(string $value): ?string
  {
    return DateHelper::parseDate($value);
  }

  protected function normalizeDate(string $value): ?string
  {
    return DateHelper::normalizeDate($value);
  }

  protected function normalizeDateOrToday(string $value): string
  {
    return DateHelper::normalizeDateOrToday($value);
  }

  protected function formatDate(?string $value): ?string
  {
    return DateHelper::formatDate($value);
  }

  protected function parseDateTime(string $value): ?string
  {
    return DateHelper::parseDateTime($value);
  }

  protected function formatDateTime(?string $value): ?string
  {
    return DateHelper::formatDateTime($value);
  }
}
