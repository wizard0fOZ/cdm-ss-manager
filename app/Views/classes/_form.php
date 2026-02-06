<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $class = $class ?? [];

  $name = $class['name'] ?? '';
  $program = $class['program'] ?? '';
  $gradeLevel = $class['grade_level'] ?? '';
  $stream = $class['stream'] ?? 'SINGLE';
  $room = $class['room'] ?? '';
  $sessionId = $class['session_id'] ?? '';
  $status = $class['status'] ?? 'DRAFT';
  $maxStudents = $class['max_students'] ?? '';
  $academicYearId = $class['academic_year_id'] ?? '';
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
    <label class="text-sm text-slate-600">Class Name</label>
    <input name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Grade 3 - English" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div class="grid gap-4 md:grid-cols-3">
    <div>
      <label class="text-sm text-slate-600">Program</label>
      <select name="program" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
        <option value="">Select program</option>
        <?php foreach ($programs as $opt): ?>
          <option value="<?= $opt ?>" <?= $program === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Grade Level</label>
      <input type="number" name="grade_level" value="<?= htmlspecialchars((string)$gradeLevel) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" min="1" max="13">
    </div>
    <div>
      <label class="text-sm text-slate-600">Stream</label>
      <select name="stream" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
        <?php foreach ($streams as $opt): ?>
          <option value="<?= $opt ?>" <?= $stream === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Session</label>
      <select name="session_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
        <option value="">Select session</option>
        <?php foreach ($sessions as $session): ?>
          <option value="<?= (int)$session['id'] ?>" <?= (string)$sessionId === (string)$session['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($session['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Academic Year</label>
      <select name="academic_year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
        <option value="">Select year</option>
        <?php foreach ($years as $year): ?>
          <option value="<?= (int)$year['id'] ?>" <?= (string)$academicYearId === (string)$year['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($year['label']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-3">
    <div>
      <label class="text-sm text-slate-600">Room</label>
      <input name="room" value="<?= htmlspecialchars($room) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-slate-600">Max Students</label>
      <input type="number" name="max_students" value="<?= htmlspecialchars((string)$maxStudents) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" min="1">
    </div>
    <div>
      <label class="text-sm text-slate-600">Status</label>
      <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
        <?php foreach ($statuses as $opt): ?>
          <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/classes" class="text-sm text-slate-600">Cancel</a>
  </div>
</form>
