<?php
  $mapping = $mapping ?? [];
  $rows = $rows ?? [];
  $rowIssues = $rowIssues ?? [];
  $summary = $summary ?? ['total' => 0, 'existing' => 0, 'new' => 0, 'unknown' => 0];
  $missingRequired = !empty($missingRequired);
  $isSysAdmin = !empty($isSysAdmin);
?>
<div data-import-preview-dynamic data-missing-required="<?= $missingRequired ? '1' : '0' ?>">
  <div class="rounded-xl border border-slate-200 bg-white p-4">
    <div class="text-sm font-semibold text-slate-900">Preview (first 5 rows)</div>
    <div class="mt-2 grid gap-3 md:grid-cols-4 text-xs text-slate-600">
      <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="text-[11px] uppercase tracking-wide text-slate-500">Total Rows</div>
        <div class="mt-1 text-sm font-semibold text-slate-900"><?= (int)($summary['total'] ?? 0) ?></div>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="text-[11px] uppercase tracking-wide text-slate-500">Existing</div>
        <div class="mt-1 text-sm font-semibold text-slate-900"><?= (int)($summary['existing'] ?? 0) ?></div>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="text-[11px] uppercase tracking-wide text-slate-500">New</div>
        <div class="mt-1 text-sm font-semibold text-slate-900"><?= (int)($summary['new'] ?? 0) ?></div>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="text-[11px] uppercase tracking-wide text-slate-500">Unknown</div>
        <div class="mt-1 text-sm font-semibold text-slate-900"><?= (int)($summary['unknown'] ?? 0) ?></div>
      </div>
    </div>
    <?php
      $hasWarnings = false;
      foreach ($rowIssues as $issue) {
        if (!empty($issue['warnings'])) { $hasWarnings = true; break; }
      }
    ?>
    <?php if ($hasWarnings): ?>
      <div class="mt-2 text-xs text-amber-700">Some rows have missing required fields. Fix before importing or use Dry Run.</div>
    <?php endif; ?>
    <div class="mt-3 table-wrap overflow-x-auto">
      <table class="cdm-table w-full text-left text-xs">
        <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
          <tr>
            <?php foreach (array_keys($mapping) as $field): ?>
              <th class="px-3 py-2"><?= htmlspecialchars($field) ?></th>
            <?php endforeach; ?>
            <th class="px-3 py-2">Warnings</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="<?= count($mapping) + 1 ?>" class="px-3 py-4 text-slate-500">No rows found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $idx => $row): ?>
              <?php $issue = $rowIssues[$idx] ?? ['missing_fields' => [], 'warnings' => []]; ?>
              <tr class="border-t border-slate-200">
                <?php foreach (array_keys($mapping) as $field): ?>
                  <?php $missing = in_array($field, $issue['missing_fields'] ?? [], true); ?>
                  <td class="px-3 py-2 <?= $missing ? 'bg-rose-50 text-rose-700' : 'text-slate-600' ?>"><?= htmlspecialchars((string)($row[$field] ?? '')) ?></td>
                <?php endforeach; ?>
                <td class="px-3 py-2 text-amber-700">
                  <?= htmlspecialchars(implode(' ', $issue['warnings'] ?? [])) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($missingRequired): ?>
    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
      Required fields are missing. Import is blocked unless a SysAdmin password is provided.
    </div>
    <?php if ($isSysAdmin): ?>
      <div class="mt-3">
        <label class="text-sm text-slate-600">SysAdmin Password Override</label>
        <input id="import-override-password" type="password" name="override_password" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="Enter SysAdmin password to override" data-override-password hx-preserve>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="mt-4 flex items-center gap-3">
    <button class="btn btn-primary" type="submit" data-run-import>
      Run Import
    </button>
    <a href="/imports/create" class="btn btn-ghost btn-sm">Back</a>
  </div>
</div>
