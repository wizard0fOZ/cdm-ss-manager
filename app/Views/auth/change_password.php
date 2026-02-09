<?php $csrf = $_SESSION['_csrf'] ?? ''; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Change Password</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-slate-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
      <h1 class="text-xl font-semibold text-slate-900">Set New Password</h1>
      <p class="text-sm text-slate-600 mt-1">Please change your temporary password to continue.</p>

      <?php if (!empty($errors)): ?>
        <div class="mt-4 rounded-lg bg-red-50 text-red-700 p-3 text-sm">
          <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="mt-5 space-y-3" method="post" action="/password/change">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div>
          <label class="text-sm text-slate-700">New Password</label>
          <input name="password" type="password" class="mt-1 w-full rounded-lg border border-slate-300 p-2" required>
        </div>

        <div>
          <label class="text-sm text-slate-700">Confirm Password</label>
          <input name="password_confirm" type="password" class="mt-1 w-full rounded-lg border border-slate-300 p-2" required>
        </div>

        <button class="btn btn-primary w-full">
          Update Password
        </button>
      </form>
    </div>
  </div>
</body>
</html>
