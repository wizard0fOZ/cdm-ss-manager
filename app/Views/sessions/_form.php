<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $name = $session['name'] ?? '';
  $startTime = $session['start_time'] ?? '';
  $endTime = $session['end_time'] ?? '';
  $sortOrder = $session['sort_order'] ?? 1;
?>

<?php if (!empty($errors)): ?>
  <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-disc pl-5">
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="space-y-6">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

  <div>
    <label class="text-sm text-slate-600">Session Name</label>
    <input name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Session 1" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Start Time</label>
      <input type="time" name="start_time" value="<?= htmlspecialchars($startTime) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
    <div>
      <label class="text-sm text-slate-600">End Time</label>
      <input type="time" name="end_time" value="<?= htmlspecialchars($endTime) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
  </div>

  <div>
    <label class="text-sm text-slate-600">Sort Order</label>
    <input type="number" name="sort_order" value="<?= htmlspecialchars((string)$sortOrder) ?>" class="mt-1 w-32 rounded-xl border border-slate-200 px-3 py-2" min="1">
  </div>

  <div class="flex items-center gap-3">
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/sessions" class="text-sm text-slate-600">Cancel</a>
  </div>
</form>
