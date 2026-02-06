<?php
  $message = $message ?? 'We are currently performing maintenance. Please check back shortly.';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maintenance</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 text-center">
      <div class="text-2xl font-semibold text-slate-900">We'll be right back</div>
      <p class="mt-3 text-sm text-slate-600"><?= htmlspecialchars($message) ?></p>
      <div class="mt-6 text-xs text-slate-400">If you're a SysAdmin, log in to continue.</div>
    </div>
  </div>
</body>
</html>
