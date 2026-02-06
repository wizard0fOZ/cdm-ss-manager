<?php
  $announcements = $announcements ?? [];
  $events = $events ?? [];
  $contactEmail = $contactEmail ?? 'coordinator@divinemercy.my';
  $mailto = 'mailto:' . $contactEmail;
  $whatsapp = $whatsapp ?? '';
  $waNumber = preg_replace('/\D+/', '', $whatsapp);
  $waLink = $waNumber !== '' ? ('https://wa.me/' . $waNumber) : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Announcements • Church of Divine Mercy Sunday School</title>

  <link rel="icon" href="/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['Fraunces', 'serif'],
            body: ['Source Sans 3', 'sans-serif']
          }
        }
      }
    }
  </script>
</head>
<body class="bg-slate-50 font-body text-slate-900">
  <header class="border-b border-slate-200 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="/assets/cdm_logo_300dpi.png" alt="CDM" class="h-9 w-9 rounded-full border border-slate-200">
        <div class="leading-tight">
          <div class="font-display text-lg font-extrabold">Church of Divine Mercy</div>
          <div class="text-xs text-slate-600">Sunday School</div>
        </div>
      </div>
      <div class="flex items-center gap-3 text-sm text-slate-600">
        <a href="/public/calendar" class="hover:text-slate-900">Calendar</a>
        <a href="/" class="hover:text-slate-900">Back to home</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-12">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="font-display text-3xl font-extrabold">Announcements</h1>
        <p class="mt-2 text-sm text-slate-600">Active updates for families and students.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?= htmlspecialchars($mailto) ?>" class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-100">Email</a>
        <?php if ($waLink !== ''): ?>
          <a href="<?= htmlspecialchars($waLink) ?>" class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-100">WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
      <?php if (empty($announcements)): ?>
        <div class="col-span-2 rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-600">
          No announcements right now.
        </div>
      <?php else: ?>
        <?php foreach ($announcements as $announcement): ?>
          <?php
            $start = new DateTime($announcement['start_at']);
            $end = new DateTime($announcement['end_at']);
            $pinUntil = !empty($announcement['pin_until']) ? new DateTime($announcement['pin_until']) : null;
            $isPinned = !empty($announcement['is_pinned']) && (!$pinUntil || $pinUntil >= new DateTime('now'));
          ?>
          <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <div class="text-xs uppercase tracking-wide text-amber-700">
              <?= htmlspecialchars($start->format('d M Y')) ?> – <?= htmlspecialchars($end->format('d M Y')) ?>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-lg font-semibold">
              <span><?= htmlspecialchars($announcement['title']) ?></span>
              <?php if ($isPinned): ?>
                <span class="rounded-full bg-white px-2 py-1 text-[10px] font-semibold text-amber-700">Pinned</span>
              <?php endif; ?>
            </div>
            <p class="mt-2 text-sm text-slate-700">
              <?= nl2br(htmlspecialchars($announcement['message'])) ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6">
      <div class="text-sm font-semibold text-slate-900">Upcoming Calendar (next 30 days)</div>
      <div class="mt-1 text-xs text-slate-500">Key dates from the Sunday School calendar.</div>
      <div class="mt-4 grid gap-3 md:grid-cols-2">
        <?php if (empty($events)): ?>
          <div class="text-sm text-slate-500">No events scheduled.</div>
        <?php else: ?>
          <?php foreach ($events as $event): ?>
            <?php
              $start = new DateTime($event['start_datetime']);
              $end = new DateTime($event['end_datetime']);
              $allDay = (int)$event['all_day'] === 1;
            ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <div class="text-sm font-semibold"><?= htmlspecialchars($event['title']) ?></div>
              <div class="mt-1 text-xs text-slate-600">
                <?= htmlspecialchars($start->format('d M Y')) ?>
                <?php if ($allDay): ?>
                  • All day
                <?php else: ?>
                  • <?= htmlspecialchars($start->format('H:i')) ?>–<?= htmlspecialchars($end->format('H:i')) ?>
                <?php endif; ?>
              </div>
              <?php if (!empty($event['description'])): ?>
                <div class="mt-1 text-xs text-slate-500"><?= htmlspecialchars($event['description']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
