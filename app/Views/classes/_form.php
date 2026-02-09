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
  $teachers = $teachers ?? [];
  $assignments = $assignments ?? [];

  $postedTeacherIds = $_POST['teacher_id'] ?? null;
  $postedTeacherRoles = $_POST['teacher_role'] ?? null;
  if (is_array($postedTeacherIds) && is_array($postedTeacherRoles)) {
    $assignments = [];
    foreach ($postedTeacherIds as $i => $tid) {
      $assignments[] = [
        'user_id' => $tid,
        'assignment_role' => $postedTeacherRoles[$i] ?? 'ASSISTANT',
      ];
    }
  }
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
      <select name="program" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
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
      <select name="stream" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <?php foreach ($streams as $opt): ?>
          <option value="<?= $opt ?>" <?= $stream === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Session</label>
      <select name="session_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
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
      <select name="academic_year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
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
      <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <?php foreach ($statuses as $opt): ?>
          <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-sm font-semibold text-slate-900">Teacher Assignments</div>
        <div class="text-xs text-slate-500">Add one or more teachers and mark main vs assistant.</div>
      </div>
    <button type="button" class="btn btn-secondary btn-xs" data-add-teacher>Add teacher</button>
    </div>
    <div class="mt-4 grid gap-3" data-teacher-list>
      <?php
        $rows = $assignments ?: [['user_id' => '', 'assignment_role' => 'ASSISTANT']];
      ?>
      <?php foreach ($rows as $row): ?>
        <div class="grid gap-3 md:grid-cols-[1.5fr_0.6fr_auto] items-end" data-teacher-row>
          <div>
            <label class="text-xs text-slate-500">Teacher</label>
            <select name="teacher_id[]" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
              <option value="">Select teacher</option>
              <?php foreach ($teachers as $teacher): ?>
                <option value="<?= (int)$teacher['id'] ?>" <?= (string)($row['user_id'] ?? '') === (string)$teacher['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($teacher['full_name']) ?><?= !empty($teacher['email']) ? ' • ' . htmlspecialchars($teacher['email']) : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-xs text-slate-500">Role</label>
            <select name="teacher_role[]" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
              <?php foreach (['MAIN' => 'Main Teacher', 'ASSISTANT' => 'Assistant'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= strtoupper($row['assignment_role'] ?? 'ASSISTANT') === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex items-end">
            <button type="button" class="btn btn-danger btn-icon btn-sm" data-remove-teacher aria-label="Remove teacher">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"></path>
                <path d="M8 6V4h8v2"></path>
                <path d="M6 6l1 14h10l1-14"></path>
                <path d="M10 11v6"></path>
                <path d="M14 11v6"></path>
              </svg>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <button class="btn btn-primary" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/classes" class="btn btn-ghost btn-sm">Cancel</a>
  </div>
</form>

<template id="teacher-row-template">
  <div class="grid gap-3 md:grid-cols-[1.5fr_0.6fr_auto] items-end" data-teacher-row>
    <div>
      <label class="text-xs text-slate-500">Teacher</label>
      <select name="teacher_id[]" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
        <option value="">Select teacher</option>
        <?php foreach ($teachers as $teacher): ?>
          <option value="<?= (int)$teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name']) ?><?= !empty($teacher['email']) ? ' • ' . htmlspecialchars($teacher['email']) : '' ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-500">Role</label>
      <select name="teacher_role[]" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        <option value="MAIN">Main Teacher</option>
        <option value="ASSISTANT" selected>Assistant</option>
      </select>
    </div>
    <div class="flex items-end">
      <button type="button" class="btn btn-danger btn-icon btn-sm" data-remove-teacher aria-label="Remove teacher">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 6h18"></path>
          <path d="M8 6V4h8v2"></path>
          <path d="M6 6l1 14h10l1-14"></path>
          <path d="M10 11v6"></path>
          <path d="M14 11v6"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
  (function () {
    const list = document.querySelector('[data-teacher-list]');
    const addBtn = document.querySelector('[data-add-teacher]');
    const template = document.getElementById('teacher-row-template');

    if (!list || !addBtn || !template) return;

    const bindRow = (row) => {
      const remove = row.querySelector('[data-remove-teacher]');
      if (remove) {
        remove.addEventListener('click', () => {
          const rows = list.querySelectorAll('[data-teacher-row]');
          if (rows.length > 1) {
            row.remove();
          } else {
            const select = row.querySelector('select[name="teacher_id[]"]');
            if (select) select.value = '';
          }
        });
      }
    };

    list.querySelectorAll('[data-teacher-row]').forEach(bindRow);

    addBtn.addEventListener('click', () => {
      const node = template.content.cloneNode(true);
      const row = node.querySelector('[data-teacher-row]');
      if (row) {
        bindRow(row);
        list.appendChild(node);
      }
    });
  })();
</script>
