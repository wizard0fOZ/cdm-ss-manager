<?php
  $pageTitle = $student['full_name'] ?? 'Student';
  $pageSubtitle = 'Student profile overview.';
  $sacrament = $sacrament ?? [];

  ob_start();
?>
  <div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="section-label">Student Details</div>
        <div class="mt-1 text-lg font-semibold text-slate-900"><?= htmlspecialchars($student['full_name'] ?? '') ?></div>
        <?php
          $showStatus = $student['status'] ?? '';
          $showBadge = match($showStatus) {
            'ACTIVE' => 'badge-success',
            'INACTIVE' => 'badge-neutral',
            'GRADUATED' => 'badge-info',
            'TRANSFERRED' => 'badge-warning',
            default => 'badge-neutral',
          };
        ?>
        <span class="badge <?= $showBadge ?> mt-1"><?= htmlspecialchars($showStatus) ?></span>
      </div>
      <div class="flex items-center gap-2">
        <a href="/students/<?= (int)$student['id'] ?>/edit" class="btn btn-secondary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <a href="/students" class="btn btn-secondary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Back
        </a>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div class="card-hover rounded-2xl border border-slate-200 p-4">
        <div class="section-label">Basic Info</div>
        <dl class="mt-3 space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-slate-500">DOB</dt><dd><?= htmlspecialchars($student['dob_display'] ?? $student['dob'] ?? '—') ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Age</dt><dd><?= htmlspecialchars(($student['age_display'] ?? null) !== null ? (string)$student['age_display'] : '—') ?></dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Class</dt><dd><?= htmlspecialchars($student['class_name'] ?? '—') ?></dd></div>
        <?php if (!empty($teachers)): ?>
          <div class="flex justify-between"><dt class="text-slate-500">Teachers</dt><dd>
            <?php
              $labels = [];
              foreach ($teachers as $row) {
                $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
              }
              echo implode(', ', $labels);
            ?>
          </dd></div>
        <?php endif; ?>
          <div class="flex justify-between"><dt class="text-slate-500">Admission</dt><dd><?= htmlspecialchars($student['admission_type'] ?? '—') ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">RCIC</dt><dd><?= !empty($student['is_rcic']) ? 'Yes' : 'No' ?></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd><span class="badge <?= $showBadge ?>"><?= htmlspecialchars($student['status'] ?? '—') ?></span></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Identity No.</dt><dd><?= htmlspecialchars($student['identity_number'] ?? '—') ?></dd></div>
        </dl>
      </div>
      <div class="card-hover rounded-2xl border border-slate-200 p-4">
        <div class="section-label">Notes</div>
        <p class="mt-3 text-sm text-slate-600 whitespace-pre-wrap"><?= htmlspecialchars($student['notes'] ?? '—') ?></p>
        <div class="mt-4 section-label">Address</div>
        <p class="mt-2 text-sm text-slate-600 whitespace-pre-wrap"><?= htmlspecialchars($student['address'] ?? '—') ?></p>
      </div>
    </div>

    <div class="card-hover rounded-2xl border border-slate-200 p-4">
      <div class="section-label">Guardians</div>
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

    <div class="card-hover rounded-2xl border border-slate-200 p-4">
      <div class="section-label">Sacraments</div>
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

    <div class="card-hover rounded-2xl border border-slate-200 p-4">
      <div class="section-label">Documents</div>
      <div class="mt-3 grid gap-2 text-sm md:grid-cols-2">
        <div class="flex items-center gap-2">
          <span class="text-slate-500">Birth certificate</span>
          <?php if (!empty($student['doc_birth_cert_url'])): ?>
            <a class="text-xs text-slate-700 underline" href="<?= htmlspecialchars($student['doc_birth_cert_url']) ?>" target="_blank" rel="noreferrer">View link</a>
            <span class="badge badge-success">Received</span>
          <?php else: ?>
            <span class="badge badge-danger">Missing</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-slate-500">Baptism certificate</span>
          <?php if (!empty($student['doc_baptism_cert_url'])): ?>
            <a class="text-xs text-slate-700 underline" href="<?= htmlspecialchars($student['doc_baptism_cert_url']) ?>" target="_blank" rel="noreferrer">View link</a>
            <span class="badge badge-success">Received</span>
          <?php else: ?>
            <span class="badge badge-danger">Missing</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-slate-500">Transfer letter</span>
          <?php if (!empty($student['doc_transfer_letter_url'])): ?>
            <a class="text-xs text-slate-700 underline" href="<?= htmlspecialchars($student['doc_transfer_letter_url']) ?>" target="_blank" rel="noreferrer">View link</a>
            <span class="badge badge-success">Received</span>
          <?php else: ?>
            <span class="badge badge-danger">Missing</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-slate-500">FHC certificate</span>
          <?php if (!empty($student['doc_fhc_cert_url'])): ?>
            <a class="text-xs text-slate-700 underline" href="<?= htmlspecialchars($student['doc_fhc_cert_url']) ?>" target="_blank" rel="noreferrer">View link</a>
            <span class="badge badge-success">Received</span>
          <?php else: ?>
            <span class="badge badge-neutral">Optional</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
