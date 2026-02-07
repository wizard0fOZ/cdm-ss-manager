<?php
$contactEmail = $contactEmail ?? 'catechetical@divinemercy.my';
$mailto = 'mailto:' . $contactEmail;
$whatsapp = $whatsapp ?? '';
$waNumber = preg_replace('/\D+/', '', $whatsapp);
$waLink = $waNumber !== '' ? ('https://wa.me/' . $waNumber) : '';
?>
<!doctype html>
<html lang="en" x-data="theme()" x-init="initTheme()" :class="{ 'dark': isDark }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Church of Divine Mercy, Sunday School</title>

  <link rel="icon" href="/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
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

  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    :root {
      --ink: #0f172a;
      --sand: #f8fafc;
      --sun: #f8c77f;
      --leaf: #2f6f5f;
      --sky: #dbeafe;
    }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px);} to { opacity: 1; transform: translateY(0);} }
    .fade-up { animation: fadeUp .4s ease-out both; }
    .bg-hero {
      background: radial-gradient(circle at top left, rgba(248,199,127,.35), transparent 48%),
                  radial-gradient(circle at 20% 20%, rgba(47,111,95,.18), transparent 45%),
                  linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .glass-card { background: rgba(255,255,255,.88); backdrop-filter: blur(10px); }
  </style>
</head>

<body class="bg-slate-50 font-body text-slate-900 dark:bg-slate-900 dark:text-slate-100">
  <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-700 dark:bg-slate-800/80">
    <div class="mx-auto max-w-6xl px-4">
      <div class="flex items-center justify-between py-4">
        <div class="flex items-center gap-3">
          <img src="/assets/cdm_logo_300dpi.png" alt="CDM" class="h-10 w-10 rounded-full border border-slate-200 dark:border-slate-700">
          <div class="leading-tight">
            <div class="font-display text-lg font-extrabold">Church of Divine Mercy</div>
            <div class="text-xs text-slate-600 dark:text-slate-300">Sunday School</div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            class="rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-700"
            @click="toggleTheme()"
            type="button"
            aria-label="Toggle dark mode"
          >
            <i class="fa-solid" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
          </button>
          <a
            href="<?= htmlspecialchars($mailto) ?>"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200"
          >
            <i class="fa-regular fa-envelope"></i>
            Email
          </a>
          <?php if ($waLink !== ''): ?>
            <a
              href="<?= htmlspecialchars($waLink) ?>"
              class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
            >
              <i class="fa-brands fa-whatsapp"></i>
              WhatsApp
            </a>
          <?php endif; ?>
        </div>
      </div>

      <nav class="pb-3 text-sm text-slate-700 dark:text-slate-300">
        <div class="flex flex-wrap gap-x-5 gap-y-2">
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#about">About</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#announcements">Announcements</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#calendar">Calendar</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#org">Org Chart</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#pics">Program PICs</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#contacts">Contacts</a>
        </div>
      </nav>
    </div>
  </header>

  <section id="about" class="bg-hero">
    <div class="mx-auto max-w-6xl px-4 py-16">
      <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="fade-up">
          <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-700">Sunday School • Faith Formation</div>
          <h1 class="mt-5 font-display text-4xl font-extrabold tracking-tight md:text-6xl">Forming Disciples, Rooted in the Gospel</h1>
          <p class="mt-4 text-base text-slate-700 dark:text-slate-300">We walk with children and teens in catechesis, prayer, and community life across English, KUBM, Mandarin, Tamil, and RCIC programs.</p>
          <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars($mailto) ?>" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200">
              <i class="fa-regular fa-envelope"></i>
              Email Us
            </a>
            <?php if ($waLink !== ''): ?>
              <a href="<?= htmlspecialchars($waLink) ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                <i class="fa-brands fa-whatsapp"></i>
                WhatsApp Us
              </a>
            <?php endif; ?>
          </div>
        </div>
        <div class="fade-up">
          <div class="glass-card rounded-2xl border border-slate-200 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/60">
            <div class="grid gap-4">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Programs</div>
                <div class="mt-2 text-sm text-slate-700 dark:text-slate-200">English, KUBM, Mandarin, Tamil, RCIC</div>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Sessions</div>
                <div class="mt-2 text-sm text-slate-700 dark:text-slate-200">3 Sunday sessions across programs</div>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Community</div>
                <div class="mt-2 text-sm text-slate-700 dark:text-slate-200">Catechists, families, and parish community</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="announcements" class="fade-up">
    <div class="mx-auto max-w-6xl px-4 py-12">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="font-display text-2xl font-bold">Announcements</h2>
          <p class="text-sm text-slate-600 dark:text-slate-300">Latest updates for families and catechists.</p>
        </div>
        <a href="/public/announcements" class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-700">View all</a>
      </div>
      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <?php if (empty($announcements)): ?>
          <div class="col-span-3 rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60">No announcements right now.</div>
        <?php else: ?>
          <?php foreach (array_slice($announcements, 0, 3) as $item): ?>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
              <div class="text-xs uppercase tracking-wide text-amber-700">Announcement</div>
              <div class="mt-2 font-semibold text-slate-900"><?= htmlspecialchars($item['title']) ?></div>
              <div class="mt-2 text-sm text-slate-700"><?= nl2br(htmlspecialchars($item['message'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="calendar" class="fade-up">
    <div class="mx-auto max-w-6xl px-4 pb-12">
      <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/60">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">Next 2 Weeks</div>
            <h3 class="font-display text-xl font-bold">Calendar Highlights</h3>
          </div>
          <a href="/public/calendar" class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-700">Open full calendar</a>
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-2">
          <?php if (empty($events)): ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">No events scheduled.</div>
          <?php else: ?>
            <?php foreach ($events as $event): ?>
              <?php
                $start = new DateTime($event['start_datetime']);
                $end = new DateTime($event['end_datetime']);
                $allDay = (int)$event['all_day'] === 1;
              ?>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                <div class="text-sm font-semibold"><?= htmlspecialchars($event['title']) ?></div>
                <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                  <?= htmlspecialchars($start->format('d M Y')) ?>
                  <?php if ($allDay): ?>
                    • All day
                  <?php else: ?>
                    • <?= htmlspecialchars($start->format('H:i')) ?>–<?= htmlspecialchars($end->format('H:i')) ?>
                  <?php endif; ?>
                </div>
                <?php if (!empty($event['description'])): ?>
                  <div class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($event['description']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section id="org" class="fade-up">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="text-center">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Organization Structure</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Core team supporting operations, training, and class coordination.</p>
      </div>

      <div class="mt-8 flex justify-center">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-800/60">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-900 text-white font-extrabold dark:bg-slate-100 dark:text-slate-900">RM</div>
          <div class="mt-3 text-lg font-extrabold">Roach Michael</div>
          <div class="text-sm text-slate-600 dark:text-slate-300">Coordinator</div>
        </div>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-3">
        <?php
        $team = [
          ['name'=>'Yamuna Anthony', 'role'=>'Office Admin'],
          ['name'=>'Stella Lawrance', 'role'=>'Office Admin'],
          ['name'=>'Laney Pio', 'role'=>'Office Admin'],
          ['name'=>'Callista', 'role'=>'Office Admin'],
          ['name'=>'Mandy Ragavan', 'role'=>'Office Admin'],
          ['name'=>'Virginia Woodford', 'role'=>'Core Team'],
          ['name'=>'Jean Khor', 'role'=>'Core Team'],
          ['name'=>'Carol Vanessa', 'role'=>'Core Team'],
          ['name'=>'Charles Nathan', 'role'=>'Core Team'],
        ];
        foreach ($team as $t):
          $initials = strtoupper(substr($t['name'], 0, 1));
        ?>
          <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-900 font-bold dark:bg-slate-700 dark:text-slate-100">
                <?= htmlspecialchars($initials) ?>
              </div>
              <div>
                <div class="font-semibold"><?= htmlspecialchars($t['name']) ?></div>
                <div class="text-xs text-slate-600 dark:text-slate-300"><?= htmlspecialchars($t['role']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="pics" class="fade-up">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="text-center">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Program PICs</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Key persons in charge for language and RCIC programs.</p>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-4">
        <?php
        $pics = [
          ['name'=>'Anna Long Anyi', 'role'=>'KUBM PIC'],
          ['name'=>'Alex Thomas', 'role'=>'Tamil PIC'],
          ['name'=>'Francis Wong Kam Heng', 'role'=>'Mandarin PIC'],
          ['name'=>'RCIC Team Lead', 'role'=>'RCIC PIC'],
        ];
        foreach ($pics as $p):
          $initials = strtoupper(substr($p['name'], 0, 1));
        ?>
          <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-800/60">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white font-extrabold dark:bg-slate-100 dark:text-slate-900">
              <?= htmlspecialchars($initials) ?>
            </div>
            <div class="mt-3 text-base font-extrabold"><?= htmlspecialchars($p['name']) ?></div>
            <div class="text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($p['role']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="contacts" class="fade-up">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="text-center">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Key Contacts</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Reach out for enquiries, registration, or support.</p>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-3">
        <?php
        $contacts = [
          ['name'=>'Roach Michael', 'role'=>'Coordinator', 'desc'=>'Overall coordination, teacher assignments, training', 'email'=>$contactEmail],
          ['name'=>'Sunday School Admin', 'role'=>'Office Admin', 'desc'=>'Registration, records, parent enquiries', 'email'=>$contactEmail],
          ['name'=>'Program PICs', 'role'=>'Language & RCIC Programs', 'desc'=>'KUBM, Tamil, Mandarin, RCIC enquiries', 'email'=>$contactEmail],
        ];
        foreach ($contacts as $c):
        ?>
          <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="font-extrabold"><?= htmlspecialchars($c['name']) ?></div>
            <div class="text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($c['role']) ?></div>
            <div class="mt-3 text-sm text-slate-700 dark:text-slate-300"><?= htmlspecialchars($c['desc']) ?></div>

            <div class="mt-5 flex flex-wrap gap-2">
              <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700" href="<?= htmlspecialchars('mailto:' . $c['email']) ?>">
                <i class="fa-regular fa-envelope"></i>
                Email
              </a>
              <?php if ($waLink !== ''): ?>
                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700" href="<?= htmlspecialchars($waLink) ?>">
                  <i class="fa-brands fa-whatsapp"></i>
                  WhatsApp
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="fade-up">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="rounded-2xl bg-slate-900 p-10 text-center text-white dark:bg-slate-800">
        <h3 class="font-display text-2xl font-extrabold">Have Questions?</h3>
        <p class="mx-auto mt-2 max-w-2xl text-slate-200">Whether you want to enrol your child or join the team of catechists, we’d love to hear from you.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
          <a href="<?= htmlspecialchars($mailto) ?>" class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100">
            <i class="fa-regular fa-envelope"></i>
            Email Us Today
          </a>
          <?php if ($waLink !== ''): ?>
            <a href="<?= htmlspecialchars($waLink) ?>" class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">
              <i class="fa-brands fa-whatsapp"></i>
              WhatsApp
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <footer class="border-t border-slate-200 py-8 dark:border-slate-700">
    <div class="mx-auto max-w-6xl px-4 flex items-center justify-between">
      <div class="text-xs text-slate-600 dark:text-slate-300">© <?= date('Y') ?> Church of Divine Mercy, Sunday School</div>
      <a class="text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100" href="/login">Staff Login</a>
    </div>
  </footer>

  <script>
    function theme() {
      return {
        isDark: false,
        initTheme() {
          const saved = localStorage.getItem('cdm_theme');
          this.isDark = saved ? saved === 'dark' : false;
        },
        toggleTheme() {
          this.isDark = !this.isDark;
          localStorage.setItem('cdm_theme', this.isDark ? 'dark' : 'light');
        }
      }
    }
  </script>
</body>
</html>
