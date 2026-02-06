<?php
  $pageTitle = 'Dashboard';
  $pageSubtitle = 'Today’s snapshot of Sunday School operations.';

  ob_start();
?>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Students</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">0</div>
      <div class="mt-1 text-xs text-slate-500">Awaiting student module</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Classes</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">0</div>
      <div class="mt-1 text-xs text-slate-500">Set in Phase 4</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Attendance</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">0%</div>
      <div class="mt-1 text-xs text-slate-500">Next: Phase 5</div>
    </div>
  </div>

  <div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <div class="text-sm font-semibold text-slate-900">Phase 2 Checklist</div>
      <ul class="mt-3 space-y-2 text-sm text-slate-600">
        <li>• Layout + sidebar scaffold</li>
        <li>• Shared typography + UI tokens</li>
        <li>• Placeholder modules list</li>
      </ul>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <div class="text-sm font-semibold text-slate-900">Next Up</div>
      <p class="mt-2 text-sm text-slate-600">Build Students module: list, create, view, edit.</p>
      <a href="/students" class="mt-4 inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
        Go to Students
      </a>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
