<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $event = $event ?? [];

  $title = $event['title'] ?? '';
  $category = $event['category'] ?? 'OTHER';
  $academicYearId = $event['academic_year_id'] ?? '';
  $scope = $event['scope'] ?? 'GLOBAL';
  $classId = $event['class_id'] ?? '';
  $allDay = !empty($event['all_day']);
  $startDate = $event['start_date_display'] ?? ($event['start_date'] ?? '');
  $endDate = $event['end_date_display'] ?? ($event['end_date'] ?? '');
  $startTime = $event['start_time_display'] ?? ($event['start_time'] ?? '09:00');
  $endTime = $event['end_time_display'] ?? ($event['end_time'] ?? '12:00');
  $description = $event['description'] ?? '';
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

<form method="post" action="<?= htmlspecialchars($action) ?>" class="space-y-6" data-calendar-form>
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

  <div>
    <label class="text-sm text-slate-600">Title</label>
    <input name="title" value="<?= htmlspecialchars($title) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Academic Year</label>
      <select name="academic_year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <option value="">Select year</option>
        <?php foreach ($years as $year): ?>
          <option value="<?= (int)$year['id'] ?>" <?= (string)$academicYearId === (string)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?><?= (int)$year['is_active'] === 1 ? ' (Active)' : '' ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Category</label>
      <select name="category" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Scope</label>
      <select name="scope" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" data-calendar-scope>
        <option value="GLOBAL" <?= $scope === 'GLOBAL' ? 'selected' : '' ?>>Global</option>
        <option value="CLASS" <?= $scope === 'CLASS' ? 'selected' : '' ?>>Class</option>
      </select>
    </div>
    <div data-calendar-class>
      <label class="text-sm text-slate-600">Class (if class scope)</label>
      <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="">Select class</option>
        <?php foreach ($classes as $class): ?>
          <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" name="all_day" value="1" <?= $allDay ? 'checked' : '' ?> data-calendar-all-day>
      All day event
    </label>
  </div>

  <div class="grid gap-4 md:grid-cols-2" data-calendar-dates>
    <div>
      <label class="text-sm text-slate-600">Start Date</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
    <div>
      <label class="text-sm text-slate-600">End Date</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2" data-calendar-times>
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
    <label class="text-sm text-slate-600">Description</label>
    <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"><?= htmlspecialchars($description) ?></textarea>
  </div>

  <div class="flex items-center gap-3">
    <button class="btn btn-primary" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/calendar" class="btn btn-ghost btn-sm">Cancel</a>
  </div>
</form>
