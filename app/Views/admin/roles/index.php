<?php
  $pageTitle = 'Admin • Roles';
  $pageSubtitle = 'Review role permissions.';

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>

  <?php
    $grouped = [];
    foreach ($permissions as $perm) {
      $module = $perm['module'] ?? 'general';
      $grouped[$module][] = $perm;
    }
  ?>
  <div class="grid gap-4">
    <?php foreach ($roles as $role): ?>
      <?php $perms = $rolePerms[$role['id']] ?? []; ?>
      <form method="post" action="/admin/roles" class="rounded-xl border border-slate-200 bg-white p-4" data-role-form>
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
        <div class="flex items-center justify-between gap-3">
          <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($role['name']) ?> <span class="text-xs text-slate-500">(<?= htmlspecialchars($role['code']) ?>)</span></div>
          <button class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Save</button>
        </div>
        <div class="mt-3 grid gap-4">
          <?php foreach ($grouped as $module => $modulePerms): ?>
            <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-3">
              <div class="flex items-center justify-between">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= htmlspecialchars($module) ?></div>
                <div class="flex items-center gap-3 text-xs text-slate-600">
                  <label class="inline-flex items-center gap-2">
                    <input type="checkbox" data-select-all="<?= htmlspecialchars($module) ?>">
                    Select all
                  </label>
                  <button type="button" class="rounded-lg border border-slate-200 px-2 py-0.5 text-xs text-slate-600" data-select-none="<?= htmlspecialchars($module) ?>">
                    Select none
                  </button>
                </div>
              </div>
              <div class="mt-2 grid gap-2 md:grid-cols-3">
                <?php foreach ($modulePerms as $perm): ?>
                  <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($perm['code']) ?>" data-perm-module="<?= htmlspecialchars($module) ?>" <?= in_array($perm['code'], $perms, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($perm['name']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <script>
    (function () {
      document.querySelectorAll('[data-role-form]').forEach((form) => {
        form.querySelectorAll('[data-select-all]').forEach((toggle) => {
          toggle.addEventListener('change', () => {
            const module = toggle.getAttribute('data-select-all');
            form.querySelectorAll(`[data-perm-module=\"${module}\"]`).forEach((cb) => {
              cb.checked = toggle.checked;
            });
          });
        });
        form.querySelectorAll('[data-select-none]').forEach((btn) => {
          btn.addEventListener('click', () => {
            const module = btn.getAttribute('data-select-none');
            form.querySelectorAll(`[data-perm-module=\"${module}\"]`).forEach((cb) => {
              cb.checked = false;
            });
            const selectAll = form.querySelector(`[data-select-all=\"${module}\"]`);
            if (selectAll) selectAll.checked = false;
          });
        });
      });
    })();
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
