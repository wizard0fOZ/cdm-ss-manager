<?php
  $csrf = $_SESSION['_csrf'] ?? '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-slate-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
      <h1 class="text-xl font-semibold text-slate-900">Reset password</h1>
      <p class="text-sm text-slate-600 mt-1">Generate a reset link for your account.</p>

      <?php if (!empty($success)): ?>
        <div class="mt-4 rounded-lg bg-emerald-50 text-emerald-700 p-3 text-sm">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($resetLink)): ?>
        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm">
          <div class="text-xs uppercase tracking-wide text-slate-500">Reset Link</div>
          <a class="mt-1 block break-words text-slate-700 underline" href="<?= htmlspecialchars($resetLink) ?>">
            <?= htmlspecialchars($resetLink) ?>
          </a>
        </div>
      <?php endif; ?>

      <form class="mt-5 space-y-3" method="post" action="/password/forgot">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div>
          <label class="text-sm text-slate-700">Email</label>
          <input name="email" type="email" class="mt-1 w-full rounded-lg border border-slate-300 p-2" required>
        </div>

        <button class="btn btn-primary w-full">
          Generate reset link
        </button>
      </form>

      <div class="mt-4 text-sm text-slate-600">
        <a class="underline" href="/login">Back to login</a>
      </div>
    </div>
  </div>
</body>
</html>
