<?php
/** @var string $contactEmail */
$mailto = 'mailto:' . $contactEmail;
?>
<!doctype html>
<html lang="en" x-data="theme()" x-init="initTheme()" :class="{ 'dark': isDark }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Church of Divine Mercy, Sunday School</title>

  <link rel="icon" href="/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>

  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script defer src="https://unpkg.com/htmx.org@1.9.12"></script>

  <style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px);} to {opacity: 1; transform: translateY(0);} }
    .fade-in { animation: fadeIn .35s ease-out both; }
  </style>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100">
  <!-- Header -->
  <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-700 dark:bg-slate-800/80">
    <div class="mx-auto max-w-6xl px-4">
      <div class="flex items-center justify-between py-3">
        <div class="flex items-center gap-3">
          <img src="/assets/media_logo.png" alt="CDM" class="h-9 w-9 rounded-full border border-slate-200 dark:border-slate-700">
          <div class="leading-tight">
            <div class="font-extrabold">Church of Divine Mercy</div>
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
            Contact Us
          </a>
        </div>
      </div>

      <nav class="pb-3 text-sm text-slate-700 dark:text-slate-300">
        <div class="flex flex-wrap gap-x-5 gap-y-2">
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#about">About</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#org">Org Chart</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#pics">PICs</a>
          <a class="hover:text-slate-900 dark:hover:text-slate-100" href="#contacts">Contacts</a>
        </div>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section id="about" class="fade-in">
    <div class="mx-auto max-w-6xl px-4 py-14">
      <div class="rounded-2xl border border-slate-200 bg-white p-10 dark:border-slate-700 dark:bg-slate-800/60">
        <h1 class="text-3xl font-extrabold tracking-tight md:text-5xl">
          Nurturing Faith, One Sunday at a Time
        </h1>
        <p class="mt-4 max-w-2xl text-slate-700 dark:text-slate-300">
          Sunday School supports children and teens through engaging lessons, fellowship, and faith formation.
        </p>

        <div class="mt-7 flex flex-wrap gap-3">
          <a
            href="<?= htmlspecialchars($mailto) ?>"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200"
          >
            <i class="fa-regular fa-envelope"></i>
            Email Us
          </a>

          <a
            href="#org"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700"
          >
            <i class="fa-solid fa-people-group"></i>
            Meet Our Team
          </a>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
          <?php
          $cards = [
            ['icon'=>'fa-book-open', 'title'=>'Classes', 'text'=>'Age-appropriate classes across multiple programs and languages.'],
            ['icon'=>'fa-clock', 'title'=>'Sessions', 'text'=>'3 Sunday sessions for different programs and year groups.'],
            ['icon'=>'fa-heart', 'title'=>'Community', 'text'=>'Catechists and families working together in faith formation.'],
          ];
          foreach ($cards as $c):
          ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900/40">
              <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                <i class="fa-solid <?= $c['icon'] ?>"></i>
              </div>
              <div class="mt-3 font-semibold"><?= htmlspecialchars($c['title']) ?></div>
              <div class="mt-1 text-sm text-slate-700 dark:text-slate-300"><?= htmlspecialchars($c['text']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($announcements)): ?>
    <section id="announcements" class="fade-in">
      <div class="mx-auto max-w-6xl px-4 pb-14">
        <div class="text-center">
          <h2 class="text-2xl font-extrabold md:text-3xl">Announcements</h2>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Latest updates for families and students.
          </p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
          <?php foreach ($announcements as $announcement): ?>
            <?php
              $start = new DateTime($announcement['start_at']);
              $end = new DateTime($announcement['end_at']);
              $pinUntil = !empty($announcement['pin_until']) ? new DateTime($announcement['pin_until']) : null;
              $isPinned = !empty($announcement['is_pinned']) && (!$pinUntil || $pinUntil >= new DateTime('now'));
            ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800/60">
              <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <?= htmlspecialchars($start->format('d M Y')) ?> – <?= htmlspecialchars($end->format('d M Y')) ?>
              </div>
              <div class="mt-2 flex flex-wrap items-center gap-2 text-lg font-semibold">
                <span><?= htmlspecialchars($announcement['title']) ?></span>
                <?php if ($isPinned): ?>
                  <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold text-amber-700">Pinned</span>
                <?php endif; ?>
              </div>
              <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                <?= nl2br(htmlspecialchars($announcement['message'])) ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Org Chart -->
  <section id="org" class="fade-in">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="text-center">
        <h2 class="text-2xl font-extrabold md:text-3xl">Organization Structure</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
          Core team supporting operations, training, and class coordination.
        </p>
      </div>

      <!-- Coordinator -->
      <div class="mt-8 flex justify-center">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-800/60">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-900 text-white font-extrabold dark:bg-slate-100 dark:text-slate-900">
            RM
          </div>
          <div class="mt-3 text-lg font-extrabold">Roach Michael</div>
          <div class="text-sm text-slate-600 dark:text-slate-300">Coordinator</div>
        </div>
      </div>

      <!-- Team grid -->
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

  <!-- PICs -->
  <section id="pics" class="fade-in">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="text-center">
        <h2 class="text-2xl font-extrabold md:text-3xl">Program PICs</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
          Key persons in charge for program language groups.
        </p>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-3">
        <?php
        $pics = [
          ['name'=>'Anna Long Anyi', 'role'=>'KUBM PIC'],
          ['name'=>'Alex Thomas', 'role'=>'Tamil PIC'],
          ['name'=>'Francis Wong Kam Heng', 'role'=>'Mandarin PIC'],
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

  <!-- Contacts -->
  <section id="contacts" class="fade-in">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="text-center">
        <h2 class="text-2xl font-extrabold md:text-3xl">Key Contacts</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
          Reach out for enquiries, registration, or support.
        </p>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-3">
        <?php
        // These are placeholders, replace with real names/emails later
        $contacts = [
          ['name'=>'Roach Michael', 'role'=>'Coordinator', 'desc'=>'Overall coordination, teacher assignments, training', 'email'=>$contactEmail],
          ['name'=>'Sunday School Admin', 'role'=>'Office Admin', 'desc'=>'Registration, records, parent enquiries', 'email'=>$contactEmail],
          ['name'=>'Program PICs', 'role'=>'Language Programs', 'desc'=>'KUBM, Tamil, Mandarin enquiries', 'email'=>$contactEmail],
        ];
        foreach ($contacts as $c):
        ?>
          <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="font-extrabold"><?= htmlspecialchars($c['name']) ?></div>
            <div class="text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($c['role']) ?></div>
            <div class="mt-3 text-sm text-slate-700 dark:text-slate-300"><?= htmlspecialchars($c['desc']) ?></div>

            <a
              class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700"
              href="<?= htmlspecialchars('mailto:' . $c['email']) ?>"
            >
              <i class="fa-regular fa-envelope"></i>
              <?= htmlspecialchars($c['email']) ?>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="fade-in">
    <div class="mx-auto max-w-6xl px-4 pb-14">
      <div class="rounded-2xl bg-slate-900 p-10 text-center text-white dark:bg-slate-800">
        <h3 class="text-2xl font-extrabold">Have Questions?</h3>
        <p class="mx-auto mt-2 max-w-2xl text-slate-200">
          Whether you want to enrol your child or join the team of catechists, we’d love to hear from you.
        </p>
        <a
          href="<?= htmlspecialchars($mailto) ?>"
          class="mt-6 inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100"
        >
          <i class="fa-regular fa-envelope"></i>
          Email Us Today
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="border-t border-slate-200 py-8 dark:border-slate-700">
    <div class="mx-auto max-w-6xl px-4 flex items-center justify-between">
      <div class="text-xs text-slate-600 dark:text-slate-300">
        © <?= date('Y') ?> Church of Divine Mercy, Sunday School
      </div>
      <a class="text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100" href="/login">
        Staff Login
      </a>
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
