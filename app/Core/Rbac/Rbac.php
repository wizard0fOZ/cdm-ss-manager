<?php
namespace App\Core\Rbac;

use App\Core\Db\Db;

final class Rbac
{
  public function can(int $userId, string $permissionCode): bool
  {
    $pdo = Db::pdo();

    // Cache per request for speed
    if (!isset($_SESSION['_perm_cache'])) {
      $_SESSION['_perm_cache'] = [];
    }

    $overrideRoleId = (int)($_SESSION['_role_override'] ?? 0);
    $cacheKey = $userId . '|' . $permissionCode . '|' . $overrideRoleId;
    if (array_key_exists($cacheKey, $_SESSION['_perm_cache'])) {
      return (bool)$_SESSION['_perm_cache'][$cacheKey];
    }

    if ($overrideRoleId > 0) {
      $sql = "
        SELECT 1
        FROM role_permissions rp
        JOIN permissions p ON p.id = rp.permission_id
        WHERE rp.role_id = ?
          AND p.code = ?
        LIMIT 1
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$overrideRoleId, $permissionCode]);
    } else {
      $sql = "
        SELECT 1
        FROM user_roles ur
        JOIN roles r ON r.id = ur.role_id
        JOIN role_permissions rp ON rp.role_id = r.id
        JOIN permissions p ON p.id = rp.permission_id
        WHERE ur.user_id = ?
          AND p.code = ?
        LIMIT 1
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$userId, $permissionCode]);
    }

    $ok = (bool)$stmt->fetchColumn();
    $_SESSION['_perm_cache'][$cacheKey] = $ok;
    return $ok;
  }
}
