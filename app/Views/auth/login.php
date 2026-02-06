<?php $csrf = $_SESSION['_csrf'] ?? ''; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Login</title>
</head>
<body class="bg-slate-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
      <h1 class="text-xl font-semibold text-slate-900">CDM SS Manager</h1>
      <p class="text-sm text-slate-600 mt-1">Staff login</p>

      <?php if (!empty($error)): ?>
        <div class="mt-4 rounded-lg bg-red-50 text-red-700 p-3 text-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form class="mt-5 space-y-3" method="post" action="/login">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div>
          <label class="text-sm text-slate-700">Email</label>
          <input name="email" type="email" class="mt-1 w-full rounded-lg border border-slate-300 p-2" required>
        </div>

        <div>
          <label class="text-sm text-slate-700">Password</label>
          <div class="relative mt-1">
            <input id="login-password" name="password" type="password" class="w-full rounded-lg border border-slate-300 p-2 pr-12" required>
            <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-xs text-slate-600 hover:bg-slate-100" data-toggle-password>
              Show
            </button>
          </div>
        </div>

        <button class="w-full rounded-lg bg-slate-900 text-white py-2">
          Sign in
        </button>
      </form>

      <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
        <a class="underline" href="/password/forgot">Forgot password?</a>
        <a class="underline" href="/">Back to homepage</a>
      </div>
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
