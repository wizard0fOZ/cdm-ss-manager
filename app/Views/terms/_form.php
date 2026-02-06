<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $academicYearId = $term['academic_year_id'] ?? '';
  $termNumber = $term['term_number'] ?? '';
  $label = $term['label'] ?? '';
  $startDate = $term['start_date'] ?? '';
  $endDate = $term['end_date'] ?? '';
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
    <label class="text-sm text-slate-600">Academic Year</label>
    <select name="academic_year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
      <option value="">Select academic year</option>
      <?php foreach ($years as $year): ?>
        <option value="<?= (int)$year['id'] ?>" <?= (string)$academicYearId === (string)$year['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($year['label']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Term Number</label>
      <select name="term_number" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <option value="">Select term</option>
        <option value="1" <?= (string)$termNumber === '1' ? 'selected' : '' ?>>Term 1</option>
        <option value="2" <?= (string)$termNumber === '2' ? 'selected' : '' ?>>Term 2</option>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Label</label>
      <input name="label" value="<?= htmlspecialchars($label) ?>" placeholder="Term 1" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
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

  <div class="flex items-center gap-3">
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/terms" class="text-sm text-slate-600">Cancel</a>
  </div>
</form>
