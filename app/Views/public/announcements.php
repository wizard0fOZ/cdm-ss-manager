<?php
  $announcements = $announcements ?? [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Announcements • Church of Divine Mercy Sunday School</title>

  <link rel="icon" href="/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
</head>
<body class="bg-slate-50 text-slate-900">
  <header class="border-b border-slate-200 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="/assets/cdm_logo_300dpi.png" alt="CDM" class="h-9 w-9 rounded-full border border-slate-200">
        <div class="leading-tight">
          <div class="font-extrabold">Church of Divine Mercy</div>
          <div class="text-xs text-slate-600">Sunday School</div>
        </div>
      </div>
      <a href="/" class="text-sm text-slate-600 hover:text-slate-900">Back to home</a>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-12">
    <div class="text-center">
      <h1 class="text-3xl font-extrabold">Announcements</h1>
      <p class="mt-2 text-sm text-slate-600">Active updates for families and students.</p>
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
          <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="text-xs uppercase tracking-wide text-slate-500">
              <?= htmlspecialchars($start->format('d M Y')) ?> – <?= htmlspecialchars($end->format('d M Y')) ?>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-lg font-semibold">
              <span><?= htmlspecialchars($announcement['title']) ?></span>
              <?php if ($isPinned): ?>
                <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold text-amber-700">Pinned</span>
              <?php endif; ?>
            </div>
            <p class="mt-2 text-sm text-slate-700">
              <?= nl2br(htmlspecialchars($announcement['message'])) ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
