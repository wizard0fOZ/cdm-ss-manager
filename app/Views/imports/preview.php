<?php
  $pageTitle = 'Import Preview';
  $pageSubtitle = 'Map your CSV fields before importing.';

  $headers = $headers ?? [];
  $mapping = $mapping ?? [];
  $rows = $rows ?? [];
  $rowIssues = $rowIssues ?? [];
  $summary = $summary ?? ['total' => 0, 'existing' => 0, 'new' => 0, 'unknown' => 0];
  $isSysAdmin = !empty($isSysAdmin);
  $defaults = $defaults ?? [];
  $years = $years ?? [];
  $classes = $classes ?? [];
  $sessions = $sessions ?? [];

  $activeYearId = 0;
  foreach ($years as $year) {
    if ((int)$year['is_active'] === 1) {
      $activeYearId = (int)$year['id'];
      break;
    }
  }

  ob_start();
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

  <form method="post" action="/imports" class="space-y-6" data-import-preview>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
    <input type="hidden" name="job_type" value="<?= htmlspecialchars($type) ?>">
    <input type="hidden" name="stored_path" value="<?= htmlspecialchars($storedPath) ?>">
    <input type="hidden" name="mapping_json" value="" data-import-mapping>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-sm font-semibold text-slate-900">Mapping</div>
      <div class="mt-3 grid gap-3 md:grid-cols-2">
        <?php foreach ($mapping as $field => $selected): ?>
          <div>
            <label class="text-xs text-slate-500"><?= htmlspecialchars($field) ?></label>
            <select class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-map-field="<?= htmlspecialchars($field) ?>">
              <option value="__ignore__">— Ignore —</option>
              <?php foreach ($headers as $header): ?>
                <option value="<?= htmlspecialchars($header) ?>" <?= $selected === $header ? 'selected' : '' ?>><?= htmlspecialchars($header) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-sm font-semibold text-slate-900">Options</div>
      <div class="mt-3 grid gap-4 md:grid-cols-2">
        <div>
          <label class="text-xs text-slate-500">Duplicate Handling</label>
          <select name="duplicate_mode" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="update" <?= ($defaults['duplicate_mode'] ?? '') === 'update' ? 'selected' : '' ?>>Update existing</option>
            <option value="skip" <?= ($defaults['duplicate_mode'] ?? '') === 'skip' ? 'selected' : '' ?>>Skip duplicates</option>
          </select>
        </div>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="dry_run" value="1" <?= !empty($defaults['dry_run']) ? 'checked' : '' ?>>
            Dry run (validate only)
          </label>
        </div>
      </div>

      <div class="mt-4 grid gap-4 md:grid-cols-2" data-import-year>
        <div>
          <label class="text-xs text-slate-500">Academic Year (for student enrollment)</label>
          <select name="academic_year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">Select year</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= (int)$year['id'] ?>" <?= ((int)($defaults['academic_year_id'] ?? 0) ?: $activeYearId) === (int)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div data-import-class>
          <label class="text-xs text-slate-500">Default Class (if CSV class_name missing)</label>
          <select name="default_class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">No default</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (int)($defaults['default_class_id'] ?? 0) === (int)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-4 grid gap-4 md:grid-cols-2" data-import-session>
        <div>
          <label class="text-xs text-slate-500">Default Session (for class import)</label>
          <select name="default_session_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">Select session</option>
            <?php foreach ($sessions as $session): ?>
              <option value="<?= (int)$session['id'] ?>" <?= (int)($defaults['default_session_id'] ?? 0) === (int)$session['id'] ? 'selected' : '' ?>><?= htmlspecialchars($session['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

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
      <div class="mt-3 overflow-x-auto">
        <table class="w-full text-left text-xs">
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

    <?php
      $missingRequired = false;
      foreach ($rowIssues as $issue) {
        if (!empty($issue['missing_fields'])) { $missingRequired = true; break; }
      }
    ?>
    <?php if ($missingRequired): ?>
      <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Required fields are missing. Import is blocked unless a SysAdmin password is provided.
      </div>
      <?php if ($isSysAdmin): ?>
        <div>
          <label class="text-sm text-slate-600">SysAdmin Password Override</label>
          <input type="password" name="override_password" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="Enter SysAdmin password to override" data-override-password>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="flex items-center gap-3">
      <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit" data-run-import <?= $missingRequired && !$isSysAdmin ? 'disabled aria-disabled=\"true\"' : '' ?>>Run Import</button>
      <a href="/imports/create" class="text-sm text-slate-600">Back</a>
    </div>
  </form>

  <script>
    (function () {
      const form = document.querySelector('[data-import-preview]');
      if (!form) return;
      const mappingInput = form.querySelector('[data-import-mapping]');
      const selects = form.querySelectorAll('[data-map-field]');
      const overrideInput = form.querySelector('[data-override-password]');
      const runButton = form.querySelector('[data-run-import]');
      const missingRequired = <?= $missingRequired ? 'true' : 'false' ?>;

      const updateMapping = () => {
        const mapping = {};
        selects.forEach((select) => {
          mapping[select.dataset.mapField] = select.value;
        });
        mappingInput.value = JSON.stringify(mapping);
      };

      const updateRunState = () => {
        if (!missingRequired) return;
        if (!overrideInput || !runButton) return;
        runButton.disabled = overrideInput.value.trim() === '';
      };

      selects.forEach((select) => select.addEventListener('change', updateMapping));
      overrideInput?.addEventListener('input', updateRunState);
      updateRunState();
      updateMapping();
    })();
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
