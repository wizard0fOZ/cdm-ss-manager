<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $flash = null;
  if (class_exists('App\\Core\\Support\\Flash')) {
    $flash = \App\Core\Support\Flash::get();
  }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Login — CDM SS Manager</title>
  <link rel="icon" href="/assets/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-slate-50">
  <div class="min-h-screen bg-soft-grid flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
      <div class="mb-8 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white font-display text-xl shadow-lg">
          CDM
        </div>
        <h1 class="mt-4 font-display text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="mt-1 text-sm text-slate-400">Sign in to your Sunday School account</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <?php if (!empty($error)): ?>
          <div class="mb-4 flex items-center gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2.5 text-sm text-red-700">
            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($flash)): ?>
          <div class="mb-4 flex items-center gap-2 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <?= htmlspecialchars($flash['message'] ?? '') ?>
          </div>
        <?php endif; ?>

        <form class="space-y-4" method="post" action="/login">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

          <div>
            <label class="text-sm font-medium text-slate-700">Email</label>
            <input name="email" type="email" placeholder="you@example.com" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm placeholder:text-slate-300" required autofocus>
          </div>

          <div>
            <label class="text-sm font-medium text-slate-700">Password</label>
            <div class="relative mt-1.5">
              <input id="login-password" name="password" type="password" placeholder="Enter your password" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 pr-16 text-sm placeholder:text-slate-300" required>
              <button type="button" class="btn btn-ghost btn-xs absolute right-1.5 top-1/2 -translate-y-1/2 text-slate-400" data-toggle-password>
                Show
              </button>
            </div>
          </div>

          <button class="btn btn-primary w-full py-2.5">
            Sign in
          </button>
        </form>

        <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
          <a class="hover:text-slate-600 transition-colors" href="/password/forgot">Forgot password?</a>
          <a class="hover:text-slate-600 transition-colors" href="/">Back to homepage</a>
        </div>
      </div>

      <p class="mt-6 text-center text-[11px] text-slate-300">Church of Divine Mercy &mdash; Sunday School</p>
    </div>
  </div>
  <script>
    (function () {
      const btn = document.querySelector('[data-toggle-password]');
      const input = document.getElementById('login-password');
      if (!btn || !input) return;
      btn.addEventListener('click', () => {
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? 'Hide' : 'Show';
      });
    })();
  </script>
</body>
</html>
