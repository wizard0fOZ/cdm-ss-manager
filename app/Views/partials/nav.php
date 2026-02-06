<?php
  $pageTitle = $pageTitle ?? 'Dashboard';
  $pageSubtitle = $pageSubtitle ?? 'Manage Sunday School operations with clarity.';
  $userLabel = $userLabel ?? 'Staff';
  $userId = (int)($_SESSION['user_id'] ?? 0);
  $overrideRole = $_SESSION['_role_override_name'] ?? null;
  $isSysAdmin = false;
  $roleOptions = [];
  try {
    if ($userId > 0) {
      $pdo = \App\Core\Db\Db::pdo();
      $rolesStmt = $pdo->prepare('SELECT r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?');
      $rolesStmt->execute([$userId]);
      $codes = array_map(fn($row) => $row['code'], $rolesStmt->fetchAll());
      $isSysAdmin = in_array('SYSADMIN', $codes, true);
      if ($isSysAdmin) {
        $roleOptions = $pdo->query('SELECT id, name FROM roles ORDER BY name ASC')->fetchAll();
      }
    }
  } catch (\Throwable $e) {
    $isSysAdmin = false;
    $roleOptions = [];
  }
?>
<header class="flex flex-col gap-4 border-b border-slate-200 bg-white/70 px-6 py-5 glass lg:flex-row lg:items-center lg:justify-between">
  <div class="flex items-center gap-4">
    <button type="button" class="lg:hidden rounded-xl border border-slate-200 px-3 py-2 text-slate-600" data-sidebar-open aria-label="Open menu">
      Menu
    </button>
    <div>
      <h1 class="font-display text-2xl text-slate-900"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="text-sm text-slate-500"><?= htmlspecialchars($pageSubtitle) ?></p>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <div class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 lg:flex">
      <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
      System healthy
    </div>
    <?php if ($isSysAdmin): ?>
      <form method="post" action="/admin/role-switch" class="hidden items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 lg:flex">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <label class="text-slate-500">View as</label>
        <select name="role_id" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
          <option value="0">Default (SysAdmin)</option>
          <?php foreach ($roleOptions as $role): ?>
            <option value="<?= (int)$role['id'] ?>" <?= (int)($_SESSION['_role_override'] ?? 0) === (int)$role['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($role['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="rounded-lg border border-slate-200 px-2 py-1 text-xs">Apply</button>
      </form>
    <?php endif; ?>
    <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600">
      <span class="font-semibold text-slate-900"><?= htmlspecialchars($userLabel) ?></span>
      <span class="text-xs text-slate-400">Online</span>
    </div>
    <?php if ($overrideRole): ?>
      <div class="hidden rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 lg:flex">
        Viewing as <?= htmlspecialchars($overrideRole) ?>
      </div>
    <?php endif; ?>
    <button
      type="button"
      class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
      onclick="document.getElementById('logout-form')?.submit()"
    >
      Logout
    </button>
  </div>
</header>
