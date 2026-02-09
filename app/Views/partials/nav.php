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
<header class="sticky top-0 z-20 flex flex-col gap-3 border-b border-slate-200 bg-white/80 px-6 py-4 glass lg:flex-row lg:items-center lg:justify-between">
  <div class="flex items-center gap-4">
    <button type="button" class="lg:hidden btn btn-secondary btn-sm" data-sidebar-open aria-label="Open menu">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 12h18M3 6h18M3 18h18"/>
      </svg>
    </button>
    <div>
      <h1 class="font-display text-xl font-semibold text-slate-900"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="text-xs text-slate-400"><?= htmlspecialchars($pageSubtitle) ?></p>
    </div>
  </div>

  <div class="flex items-center gap-2">
    <?php if ($isSysAdmin): ?>
      <form method="post" action="/admin/role-switch" class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 lg:flex">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <label class="text-slate-400">View as</label>
        <select name="role_id" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
          <option value="0">Default (SysAdmin)</option>
          <?php foreach ($roleOptions as $role): ?>
            <option value="<?= (int)$role['id'] ?>" <?= (int)($_SESSION['_role_override'] ?? 0) === (int)$role['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($role['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary btn-xs">Apply</button>
      </form>
    <?php endif; ?>
    <?php if ($overrideRole): ?>
      <div class="hidden items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 lg:flex">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <?= htmlspecialchars($overrideRole) ?>
      </div>
    <?php endif; ?>
    <div class="hidden items-center gap-2 rounded-xl bg-slate-50 px-3 py-1.5 text-xs text-slate-500 lg:flex">
      <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-slate-600">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>
        </svg>
      </div>
      <span class="font-medium text-slate-700"><?= htmlspecialchars($userLabel) ?></span>
    </div>
    <button
      type="button"
      class="btn btn-ghost btn-icon btn-sm"
      data-theme-toggle
      aria-label="Toggle theme"
    >
      <svg data-theme-icon-sun class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v2"></path>
        <path d="M12 20v2"></path>
        <path d="M4.93 4.93l1.41 1.41"></path>
        <path d="M17.66 17.66l1.41 1.41"></path>
        <path d="M2 12h2"></path>
        <path d="M20 12h2"></path>
        <path d="M6.34 17.66l-1.41 1.41"></path>
        <path d="M19.07 4.93l-1.41 1.41"></path>
      </svg>
      <svg data-theme-icon-moon class="h-4 w-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"></path>
      </svg>
    </button>
    <button
      type="button"
      class="btn btn-ghost btn-sm text-slate-500 hover:text-red-600"
      onclick="document.getElementById('logout-form')?.submit()"
    >
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      <span class="hidden lg:inline">Logout</span>
    </button>
  </div>
</header>
