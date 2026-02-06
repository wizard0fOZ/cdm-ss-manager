<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $record = $record ?? [];

  $userId = $record['user_id'] ?? '';
  $type = $record['type'] ?? 'OTHER';
  $title = $record['title'] ?? '';
  $provider = $record['provider'] ?? '';
  $attendedDate = $record['attended_date_display'] ?? ($record['attended_date'] ?? '');
  $hours = $record['hours_fulfilled'] ?? '';
  $issueDate = $record['issue_date_display'] ?? ($record['issue_date'] ?? '');
  $expiryDate = $record['expiry_date_display'] ?? ($record['expiry_date'] ?? '');
  $evidenceUrl = $record['evidence_url'] ?? '';
  $remarks = $record['remarks'] ?? '';
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
    <label class="text-sm text-slate-600">Teacher</label>
    <select name="user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
      <option value="">Select teacher</option>
      <?php foreach ($users as $user): ?>
        <option value="<?= (int)$user['id'] ?>" <?= (string)$userId === (string)$user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['full_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Type</label>
      <select name="type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <?php foreach ($types as $t): ?>
          <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Title</label>
      <input name="title" value="<?= htmlspecialchars($title) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Provider</label>
      <input name="provider" value="<?= htmlspecialchars($provider) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-slate-600">Attended Date</label>
      <input type="date" name="attended_date" value="<?= htmlspecialchars($attendedDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Hours Fulfilled</label>
      <input name="hours_fulfilled" value="<?= htmlspecialchars((string)$hours) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-slate-600">Issue Date</label>
      <input type="date" name="issue_date" value="<?= htmlspecialchars($issueDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Expiry Date</label>
      <input type="date" name="expiry_date" value="<?= htmlspecialchars($expiryDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-slate-600">Evidence Link (Google Drive / OneDrive)</label>
      <input name="evidence_url" value="<?= htmlspecialchars($evidenceUrl) ?>" placeholder="https://" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
  </div>

  <div>
    <label class="text-sm text-slate-600">Remarks</label>
    <textarea name="remarks" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"><?= htmlspecialchars($remarks) ?></textarea>
  </div>

  <div class="flex items-center gap-3">
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/training" class="text-sm text-slate-600">Cancel</a>
  </div>
</form>
