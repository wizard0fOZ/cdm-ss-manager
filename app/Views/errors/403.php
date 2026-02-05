<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Forbidden</title>
</head>
<body class="bg-slate-50 p-6">
  <div class="max-w-xl mx-auto bg-white rounded-xl border border-slate-200 p-5">
    <h1 class="text-lg font-semibold text-slate-900">403 Forbidden</h1>
    <p class="text-sm text-slate-700 mt-2">Missing permission: <b><?= htmlspecialchars($code ?? '') ?></b></p>
    <a class="inline-block mt-4 text-sm text-slate-900 underline" href="/dashboard">Back</a>
  </div>
</body>
</html>
