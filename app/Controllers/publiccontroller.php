<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Response;
use App\Core\Db\Db;

final class PublicController
{
  public function home(): void
  {
    $contactEmail = $_ENV['PUBLIC_CONTACT_EMAIL'] ?? 'coordinator@divinemercy.my';
    $pdo = Db::pdo();
    $stmt = $pdo->query("SELECT title, message, start_at, end_at, pin_until, is_pinned, priority FROM announcements WHERE scope = 'GLOBAL' AND status = 'PUBLISHED' AND start_at <= NOW() AND end_at >= NOW() ORDER BY (CASE WHEN is_pinned = 1 AND (pin_until IS NULL OR pin_until >= NOW()) THEN 1 ELSE 0 END) DESC, priority DESC, start_at DESC LIMIT 3");
    $announcements = $stmt->fetchAll();
    (new Response())->view('public/home.php', [
      'contactEmail' => $contactEmail,
      'announcements' => $announcements,
    ]);
  }

  public function announcements(): void
  {
    $pdo = Db::pdo();
    $stmt = $pdo->query("SELECT title, message, start_at, end_at, pin_until, is_pinned, priority FROM announcements WHERE scope = 'GLOBAL' AND status = 'PUBLISHED' AND start_at <= NOW() AND end_at >= NOW() ORDER BY (CASE WHEN is_pinned = 1 AND (pin_until IS NULL OR pin_until >= NOW()) THEN 1 ELSE 0 END) DESC, priority DESC, start_at DESC");
    $announcements = $stmt->fetchAll();

    (new Response())->view('public/announcements.php', [
      'announcements' => $announcements,
    ]);
  }
}
