<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $label = $year['label'] ?? '';
  $startDate = $year['start_date'] ?? '';
  $endDate = $year['end_date'] ?? '';
  $isActive = !empty($year['is_active']);
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
    <label class="text-sm text-slate-600">Academic Year Label</label>
    <input name="label" value="<?= htmlspecialchars($label) ?>" placeholder="2026" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
    <label class="text-sm text-slate-600">Start Date</label>
    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
    <div>
    <label class="text-sm text-slate-600">End Date</label>
    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
  </div>

  <label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
    Set as active academic year
  </label>

  <div class="flex items-center gap-3">
    <button class="btn btn-primary" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/academic-years" class="btn btn-ghost btn-sm">Cancel</a>
  </div>
</form>
