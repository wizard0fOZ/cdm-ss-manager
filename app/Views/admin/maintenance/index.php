<?php
  $pageTitle = 'Admin • Maintenance';
  $pageSubtitle = 'Data integrity checks.';

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>

  <div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500 uppercase">Orphan Enrollments</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$orphanEnrollments ?></div>
      <div class="mt-1 text-xs text-slate-500">Enrollments with missing students.</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500 uppercase">Orphan Attendance</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$orphanAttendance ?></div>
      <div class="mt-1 text-xs text-slate-500">Attendance rows with missing students.</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500 uppercase">Classes Missing Session</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$orphanClasses ?></div>
      <div class="mt-1 text-xs text-slate-500">Classes with missing session record.</div>
    </div>
  </div>

  <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
    Cleanup actions require SysAdmin password confirmation.
  </div>

  <form method="post" action="/admin/maintenance" class="mt-4 space-y-3">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="text-xs text-slate-500">SysAdmin Password</label>
        <input type="password" name="override_password" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Confirm password to run cleanup">
      </div>
      <div>
        <label class="text-xs text-slate-500">Action</label>
        <select name="action" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="cleanup_orphan_enrollments">Remove orphan enrollments</option>
          <option value="cleanup_orphan_attendance">Remove orphan attendance</option>
        </select>
      </div>
    </div>
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Run Cleanup</button>
  </form>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
