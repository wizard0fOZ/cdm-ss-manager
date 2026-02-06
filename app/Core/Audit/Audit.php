<?php
namespace App\Core\Audit;

use App\Core\Db\Db;

final class Audit
{
  public static function log(string $action, string $entityType, string $entityId, ?array $before = null, ?array $after = null): void
  {
    $actorId = (int)($_SESSION['user_id'] ?? 0);
    if ($actorId <= 0) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, before_json, after_json, ip_address, user_agent) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([
      $actorId,
      $action,
      $entityType,
      $entityId,
      $before ? json_encode($before) : null,
      $after ? json_encode($after) : null,
      $ip,
      $agent,
    ]);
  }
}
