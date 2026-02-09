<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Response;
use App\Core\Db\Db;

final class PublicController
{
  public function home(): void
  {
    $settings = $this->publicSettings();
    $contactEmail = $settings['email'];
    $whatsapp = $settings['whatsapp'];
    $pdo = Db::pdo();
    $stmt = $pdo->query("SELECT title, message, start_at, end_at, pin_until, is_pinned, priority FROM announcements WHERE scope = 'GLOBAL' AND status = 'PUBLISHED' AND start_at <= NOW() AND end_at >= NOW() ORDER BY (CASE WHEN is_pinned = 1 AND (pin_until IS NULL OR pin_until >= NOW()) THEN 1 ELSE 0 END) DESC, priority DESC, start_at DESC LIMIT 3");
    $announcements = $stmt->fetchAll();
    $rangeStart = (new \DateTime('today'))->setTime(0, 0, 0);
    $rangeEnd = (new \DateTime('+14 days'))->setTime(23, 59, 59);
    $eventsStmt = $pdo->prepare("SELECT title, start_datetime, end_datetime, all_day, description
      FROM calendar_events
      WHERE scope = 'GLOBAL' AND start_datetime <= ? AND end_datetime >= ?
      ORDER BY start_datetime ASC
      LIMIT 5");
    $eventsStmt->execute([$rangeEnd->format('Y-m-d H:i:s'), $rangeStart->format('Y-m-d H:i:s')]);
    $events = $eventsStmt->fetchAll();
    (new Response())->view('public/home.php', [
      'contactEmail' => $contactEmail,
      'whatsapp' => $whatsapp,
      'announcements' => $announcements,
      'events' => $events,
    ]);
  }

  public function announcements(): void
  {
    $settings = $this->publicSettings();
    $contactEmail = $settings['email'];
    $whatsapp = $settings['whatsapp'];
    $pdo = Db::pdo();
    $stmt = $pdo->query("SELECT title, message, start_at, end_at, pin_until, is_pinned, priority FROM announcements WHERE scope = 'GLOBAL' AND status = 'PUBLISHED' AND start_at <= NOW() AND end_at >= NOW() ORDER BY (CASE WHEN is_pinned = 1 AND (pin_until IS NULL OR pin_until >= NOW()) THEN 1 ELSE 0 END) DESC, priority DESC, start_at DESC");
    $announcements = $stmt->fetchAll();
    $rangeStart = (new \DateTime('today'))->setTime(0, 0, 0);
    $rangeEnd = (new \DateTime('+30 days'))->setTime(23, 59, 59);
    $eventsStmt = $pdo->prepare("SELECT title, start_datetime, end_datetime, all_day, description
      FROM calendar_events
      WHERE scope = 'GLOBAL' AND start_datetime <= ? AND end_datetime >= ?
      ORDER BY start_datetime ASC
      LIMIT 10");
    $eventsStmt->execute([$rangeEnd->format('Y-m-d H:i:s'), $rangeStart->format('Y-m-d H:i:s')]);
    $events = $eventsStmt->fetchAll();

    (new Response())->view('public/announcements.php', [
      'announcements' => $announcements,
      'events' => $events,
      'contactEmail' => $contactEmail,
      'whatsapp' => $whatsapp,
    ]);
  }

  public function calendar(): void
  {
    $settings = $this->publicSettings();
    $contactEmail = $settings['email'];
    $whatsapp = $settings['whatsapp'];
    $pdo = Db::pdo();
    $monthParam = trim((string)($_GET['month'] ?? ''));
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

    $stmt = $pdo->prepare("SELECT title, start_datetime, end_datetime, all_day, description
      FROM calendar_events
      WHERE scope = 'GLOBAL' AND start_datetime <= ? AND end_datetime >= ?
      ORDER BY start_datetime ASC");
    $stmt->execute([$calendarEnd->format('Y-m-d H:i:s'), $calendarStart->format('Y-m-d H:i:s')]);
    $events = $stmt->fetchAll();

    $eventsByDate = [];
    foreach ($events as $event) {
      $start = new \DateTime($event['start_datetime']);
      $end = new \DateTime($event['end_datetime']);
      $day = (clone $start)->setTime(0, 0, 0);
      $endDay = (clone $end)->setTime(0, 0, 0);
      while ($day <= $endDay) {
        $dateKey = $day->format('Y-m-d');
        if (!isset($eventsByDate[$dateKey])) $eventsByDate[$dateKey] = [];
        $eventsByDate[$dateKey][] = $event;
        $day->modify('+1 day');
      }
    }

    (new Response())->view('public/calendar.php', [
      'events' => $events,
      'eventsByDate' => $eventsByDate,
      'monthLabel' => $monthLabel,
      'monthPrev' => $monthPrev,
      'monthNext' => $monthNext,
      'calendarStart' => $calendarStart,
      'calendarEnd' => $calendarEnd,
      'monthStart' => $monthStart,
      'contactEmail' => $contactEmail,
      'whatsapp' => $whatsapp,
    ]);
  }

  private function publicSettings(): array
  {
    $email = $_ENV['PUBLIC_CONTACT_EMAIL'] ?? 'catechetical@divinmercy.my';
    $whatsapp = $_ENV['PUBLIC_WHATSAPP'] ?? '';
    try {
      $pdo = Db::pdo();
      $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN (?, ?)');
      $stmt->execute(['public_contact_email', 'public_whatsapp']);
      foreach ($stmt->fetchAll() as $row) {
        if ($row['setting_key'] === 'public_contact_email' && $row['setting_value']) {
          $email = $row['setting_value'];
        }
        if ($row['setting_key'] === 'public_whatsapp' && $row['setting_value']) {
          $whatsapp = $row['setting_value'];
        }
      }
    } catch (\Throwable $e) {
      // fallback to env
    }
    return [
      'email' => $email,
      'whatsapp' => $whatsapp,
    ];
  }
}
