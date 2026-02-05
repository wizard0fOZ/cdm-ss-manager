<?php $csrf = $_SESSION['_csrf'] ?? ''; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Dashboard</title>
</head>
<body class="bg-slate-50">
  <div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>

      <form method="post" action="/logout">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <button class="rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm">
          Logout
        </button>
      </form>
    </div>

    <div class="mt-6 bg-white border border-slate-200 rounded-xl p-4">
      <p class="text-slate-700 text-sm">You are logged in. Next we build Students module.</p>
    </div>
  </div>
</body>
</html>
