<?php
  $pageTitle = $student['full_name'] ?? 'Student';
  $pageSubtitle = 'Student profile overview.';
  $sacrament = $sacrament ?? [];

  ob_start();
?>
  <div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Student Details</div>
        <div class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($student['full_name'] ?? '') ?></div>
      </div>
      <div class="flex items-center gap-2">
        <a href="/students/<?= (int)$student['id'] ?>/edit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Edit</a>
        <a href="/students" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Back</a>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div class="rounded-xl border border-slate-200 p-4">
        <div class="text-xs uppercase tracking-wide text-slate-500">Basic Info</div>
        <dl class="mt-3 space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-slate-500">DOB</dt><dd><?= htmlspecialchars($student['dob_display'] ?? $student['dob'] ?? '—') ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Age</dt><dd><?= htmlspecialchars(($student['age_display'] ?? null) !== null ? (string)$student['age_display'] : '—') ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Class</dt><dd><?= htmlspecialchars($student['class_name'] ?? '—') ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">RCIC</dt><dd><?= !empty($student['is_rcic']) ? 'Yes' : 'No' ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd><?= htmlspecialchars($student['status'] ?? '—') ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Identity No.</dt><dd><?= htmlspecialchars($student['identity_number'] ?? '—') ?></dd></div>
        </dl>
      </div>
      <div class="rounded-xl border border-slate-200 p-4">
        <div class="text-xs uppercase tracking-wide text-slate-500">Notes</div>
        <p class="mt-3 text-sm text-slate-600 whitespace-pre-wrap"><?= htmlspecialchars($student['notes'] ?? '—') ?></p>
        <div class="mt-4 text-xs uppercase tracking-wide text-slate-500">Address</div>
        <p class="mt-2 text-sm text-slate-600 whitespace-pre-wrap"><?= htmlspecialchars($student['address'] ?? '—') ?></p>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Guardians</div>
      <?php if (empty($guardians)): ?>
        <p class="mt-3 text-sm text-slate-600">No guardian records.</p>
      <?php else: ?>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
          <?php foreach ($guardians as $guardian): ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($guardian['full_name'] ?? '') ?></div>
              <div class="text-xs text-slate-500"><?= htmlspecialchars($guardian['relationship_label'] ?? 'Guardian') ?></div>
              <div class="mt-2 text-xs text-slate-500">Email: <?= htmlspecialchars($guardian['email'] ?? '—') ?></div>
              <div class="text-xs text-slate-500">Phone: <?= htmlspecialchars($guardian['phone'] ?? '—') ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="rounded-xl border border-slate-200 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Sacraments</div>
      <div class="mt-3 grid gap-3 md:grid-cols-2 text-sm">
        <div>
          <div class="text-slate-500">Church of Baptism</div>
          <div><?= htmlspecialchars($sacrament['church_of_baptism'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Place of Baptism</div>
          <div><?= htmlspecialchars($sacrament['place_of_baptism'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Date of Baptism</div>
          <div><?= htmlspecialchars($sacrament['date_of_baptism_display'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Godfather</div>
          <div><?= htmlspecialchars($sacrament['godfather'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Godmother</div>
          <div><?= htmlspecialchars($sacrament['godmother'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">First Holy Communion</div>
          <div><?= htmlspecialchars($sacrament['date_of_first_holy_communion_display'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Place of FHC</div>
          <div><?= htmlspecialchars($sacrament['place_of_first_holy_communion'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Confirmation</div>
          <div><?= htmlspecialchars($sacrament['date_of_confirmation_display'] ?? '—') ?></div>
        </div>
        <div>
          <div class="text-slate-500">Place of Confirmation</div>
          <div><?= htmlspecialchars($sacrament['place_of_confirmation'] ?? '—') ?></div>
        </div>
      </div>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
