<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $token = $token ?? '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Reset Password</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-slate-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
      <h1 class="text-xl font-semibold text-slate-900">Set a new password</h1>
      <p class="text-sm text-slate-600 mt-1">Choose a new password for your account.</p>

      <?php if (!empty($error)): ?>
        <div class="mt-4 rounded-lg bg-red-50 text-red-700 p-3 text-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="mt-4 rounded-lg bg-red-50 text-red-700 p-3 text-sm">
          <ul class="list-disc list-inside">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="mt-4 rounded-lg bg-emerald-50 text-emerald-700 p-3 text-sm">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form class="mt-5 space-y-3" method="post" action="/password/reset">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div>
          <label class="text-sm text-slate-700">New password</label>
          <input name="password" type="password" class="mt-1 w-full rounded-lg border border-slate-300 p-2" required>
        </div>

        <div>
          <label class="text-sm text-slate-700">Confirm password</label>
          <input name="password_confirm" type="password" class="mt-1 w-full rounded-lg border border-slate-300 p-2" required>
        </div>

        <button class="btn btn-primary w-full">
          Update password
        </button>
      </form>

      <div class="mt-4 text-sm text-slate-600">
        <a class="underline" href="/login">Back to login</a>
      </div>
    </div>
  </div>
</body>
</html>
