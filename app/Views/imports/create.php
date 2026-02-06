<?php
  $pageTitle = 'New Import';
  $pageSubtitle = 'Upload a CSV file to bulk import.';
  $types = $types ?? ['STUDENTS','TEACHERS','CLASSES'];
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

  <form method="post" action="/imports" enctype="multipart/form-data" class="space-y-6" data-import-form>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">

    <div>
      <label class="text-sm text-slate-600">Import Type</label>
      <select name="job_type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" data-import-type>
        <?php foreach ($types as $type): ?>
          <option value="<?= $type ?>"><?= $type ?></option>
        <?php endforeach; ?>
      </select>
      <div class="mt-2 text-xs text-slate-500">
        <a href="/imports/template/STUDENTS" class="underline">Student template</a> ·
        <a href="/imports/template/TEACHERS" class="underline">Teacher template</a> ·
        <a href="/imports/template/CLASSES" class="underline">Class template</a>
      </div>
    </div>

    <div>
      <label class="text-sm text-slate-600">CSV File</label>
      <input type="file" name="csv_file" accept=".csv" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="text-sm text-slate-600">Duplicate Handling</label>
        <select name="duplicate_mode" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
          <option value="update">Update existing</option>
          <option value="skip">Skip duplicates</option>
        </select>
      </div>
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
          <input type="checkbox" name="dry_run" value="1">
          Dry run (validate only, no changes)
        </label>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2" data-import-year>
      <div>
        <label class="text-sm text-slate-600">Academic Year (for student enrollment)</label>
        <select name="academic_year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
          <option value="">Select year</option>
          <?php foreach ($years as $year): ?>
            <option value="<?= (int)$year['id'] ?>" <?= $activeYearId === (int)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div data-import-class>
        <label class="text-sm text-slate-600">Default Class (if CSV class_name missing)</label>
        <select name="default_class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
          <option value="">No default</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2" data-import-session>
      <div>
        <label class="text-sm text-slate-600">Default Session (for class import)</label>
        <select name="default_session_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
          <option value="">Select session</option>
          <?php foreach ($sessions as $session): ?>
            <option value="<?= (int)$session['id'] ?>"><?= htmlspecialchars($session['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold" type="submit" formaction="/imports/preview">Preview & Map</button>
      <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Run Import</button>
      <a href="/imports" class="text-sm text-slate-600">Cancel</a>
    </div>
  </form>

  <script>
    (function () {
      const form = document.querySelector('[data-import-form]');
      if (!form) return;
      const typeSelect = form.querySelector('[data-import-type]');
      const yearBlock = form.querySelector('[data-import-year]');
      const classBlock = form.querySelector('[data-import-class]');
      const sessionBlock = form.querySelector('[data-import-session]');

      const toggle = () => {
        const type = typeSelect?.value;
        if (type === 'STUDENTS') {
          yearBlock.style.display = 'grid';
          classBlock.style.display = 'block';
          sessionBlock.style.display = 'none';
        } else if (type === 'CLASSES') {
          yearBlock.style.display = 'none';
          sessionBlock.style.display = 'grid';
        } else {
          yearBlock.style.display = 'none';
          sessionBlock.style.display = 'none';
        }
      };

      typeSelect?.addEventListener('change', toggle);
      toggle();
    })();
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
