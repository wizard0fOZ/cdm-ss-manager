<?php
  $title = $title ?? 'Something went wrong';
  $message = $message ?? 'An unexpected error occurred.';
  $details = $details ?? '';
  $isSysAdmin = false;
  if (!empty($_SESSION['user_id'])) {
    try {
      $pdo = \App\Core\Db\Db::pdo();
      $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code = ? LIMIT 1');
      $stmt->execute([(int)$_SESSION['user_id'], 'SYSADMIN']);
      $isSysAdmin = (bool)$stmt->fetchColumn();
    } catch (\Throwable $e) {
      $isSysAdmin = false;
    }
  }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-slate-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6">
      <div class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($title) ?></div>
      <p class="mt-3 text-sm text-slate-600"><?= htmlspecialchars($message) ?></p>
      <div class="mt-5 flex gap-2">
        <a href="/" class="btn btn-secondary">Go Home</a>
        <a href="/login" class="btn btn-primary">Login</a>
      </div>

      <?php if ($details && $isSysAdmin): ?>
        <button class="btn btn-ghost btn-xs mt-4" type="button" data-toggle-details>Show technical details</button>
        <pre class="mt-2 hidden whitespace-pre-wrap rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600" data-details><?= htmlspecialchars($details) ?></pre>
      <?php endif; ?>
    </div>
  </div>

  <script>
    (function () {
      const btn = document.querySelector('[data-toggle-details]');
      const details = document.querySelector('[data-details]');
      if (!btn || !details) return;
      btn.addEventListener('click', () => {
        details.classList.toggle('hidden');
        btn.textContent = details.classList.contains('hidden') ? 'Show technical details' : 'Hide technical details';
      });
    })();
  </script>
</body>
</html>
