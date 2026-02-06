<?php
  $events = $events ?? [];
  $eventsByDate = $eventsByDate ?? [];
  $monthLabel = $monthLabel ?? '';
  $monthPrev = $monthPrev ?? '';
  $monthNext = $monthNext ?? '';
  $calendarStart = $calendarStart ?? new DateTime('first day of this month');
  $calendarEnd = $calendarEnd ?? new DateTime('last day of this month');
  $monthStart = $monthStart ?? new DateTime('first day of this month');
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

  <title>Calendar • Church of Divine Mercy Sunday School</title>

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
        <a href="/public/announcements" class="hover:text-slate-900">Announcements</a>
        <a href="/" class="hover:text-slate-900">Back to home</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="font-display text-3xl font-extrabold">Calendar</h1>
        <p class="mt-1 text-sm text-slate-600">Public Sunday School events and key dates.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="/public/calendar?month=<?= htmlspecialchars($monthPrev) ?>" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">Prev</a>
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold"><?= htmlspecialchars($monthLabel) ?></div>
        <a href="/public/calendar?month=<?= htmlspecialchars($monthNext) ?>" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">Next</a>
      </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
      <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs uppercase tracking-wide text-slate-500">
        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?>
          <div class="py-3"><?= $day ?></div>
        <?php endforeach; ?>
      </div>
      <div class="grid grid-cols-7 text-sm">
        <?php
          $cursor = clone $calendarStart;
          $end = clone $calendarEnd;
          while ($cursor <= $end):
            $dateKey = $cursor->format('Y-m-d');
            $isCurrentMonth = $cursor->format('Y-m') === $monthStart->format('Y-m');
            $count = isset($eventsByDate[$dateKey]) ? count($eventsByDate[$dateKey]) : 0;
        ?>
          <div
            class="min-h-[96px] border-b border-slate-200 border-r border-slate-200 p-3 cursor-pointer <?= $isCurrentMonth ? '' : 'bg-slate-50 text-slate-400' ?>"
            data-day-cell
            data-date="<?= htmlspecialchars($dateKey) ?>"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold"><?= $cursor->format('j') ?></span>
              <?php if ($count > 0): ?>
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700"><?= $count ?></span>
              <?php endif; ?>
            </div>
            <?php if ($count > 0): ?>
              <div class="mt-2 space-y-1">
                <?php foreach (array_slice($eventsByDate[$dateKey], 0, 2) as $event): ?>
                  <div class="truncate rounded-md bg-slate-100 px-2 py-1 text-[11px] text-slate-700">
                    <?= htmlspecialchars($event['title']) ?>
                  </div>
                <?php endforeach; ?>
                <?php if ($count > 2): ?>
                  <div class="text-[10px] text-slate-500">+<?= $count - 2 ?> more</div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php
            $cursor->modify('+1 day');
          endwhile;
        ?>
      </div>
    </div>

    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
      <div class="text-sm font-semibold text-slate-900">Events this month</div>
      <div class="mt-1 text-xs text-slate-500">Global events shown for the public calendar.</div>
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

    <div class="mt-8 flex flex-wrap items-center gap-3">
      <a href="<?= htmlspecialchars($mailto) ?>" class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-100">Email</a>
      <?php if ($waLink !== ''): ?>
        <a href="<?= htmlspecialchars($waLink) ?>" class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-100">WhatsApp</a>
      <?php endif; ?>
    </div>
  </main>

  <div id="day-drawer" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/40" data-drawer-close></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-xl">
      <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <div>
          <div class="text-xs uppercase tracking-wide text-slate-500">Events</div>
          <div id="drawer-date" class="text-lg font-semibold">Selected day</div>
        </div>
        <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-drawer-close>Close</button>
      </div>
      <div id="drawer-body" class="p-5 space-y-3 text-sm text-slate-700"></div>
    </div>
  </div>

  <script>
    const eventsByDate = <?= json_encode($eventsByDate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const drawer = document.getElementById('day-drawer');
    const drawerBody = document.getElementById('drawer-body');
    const drawerDate = document.getElementById('drawer-date');

    const closeDrawer = () => {
      if (!drawer) return;
      drawer.classList.add('hidden');
      drawer.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('[data-day-cell]').forEach((cell) => {
      cell.addEventListener('click', () => {
        const date = cell.getAttribute('data-date');
        const items = eventsByDate[date] || [];
        if (drawerDate) {
          drawerDate.textContent = date;
        }
        if (drawerBody) {
          drawerBody.innerHTML = '';
          if (!items.length) {
            drawerBody.innerHTML = '<div class="text-sm text-slate-500">No events scheduled.</div>';
          } else {
            items.forEach((event) => {
              const start = new Date(event.start_datetime.replace(' ', 'T'));
              const end = new Date(event.end_datetime.replace(' ', 'T'));
              const allDay = Number(event.all_day) === 1;
              const timeLabel = allDay
                ? 'All day'
                : `${start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}–${end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
              const wrapper = document.createElement('div');
              wrapper.className = 'rounded-xl border border-slate-200 bg-slate-50 p-4';
              wrapper.innerHTML = `
                <div class="text-sm font-semibold">${event.title}</div>
                <div class="mt-1 text-xs text-slate-600">${timeLabel}</div>
                ${event.description ? `<div class="mt-1 text-xs text-slate-500">${event.description}</div>` : ''}
              `;
              drawerBody.appendChild(wrapper);
            });
          }
        }
        if (drawer) {
          drawer.classList.remove('hidden');
          drawer.setAttribute('aria-hidden', 'false');
        }
      });
    });

    document.querySelectorAll('[data-drawer-close]').forEach((btn) => {
      btn.addEventListener('click', closeDrawer);
    });
  </script>
</body>
</html>
