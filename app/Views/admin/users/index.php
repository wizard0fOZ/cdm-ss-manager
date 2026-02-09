<?php
  $pageTitle = 'Admin • Users';
  $pageSubtitle = 'Manage system users and roles.';

  $q = $q ?? '';
  $status = $status ?? '';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <?php require __DIR__ . '/../_nav.php'; ?>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <form method="get" class="filter-bar grid flex-1 gap-3 md:grid-cols-3">
        <div>
          <label class="section-label">Search</label>
          <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name or email" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="section-label">Status</label>
          <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
            <option value="INACTIVE" <?= $status === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="/admin/users" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <a href="/admin/users/create" class="btn btn-primary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add User
      </a>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">User</th>
            <th class="px-4 py-3">Roles</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="4" class="px-4 py-6">
                <?php
                  $message = 'No users found.';
                  $actionLabel = 'Add User';
                  $actionHref = '/admin/users/create';
                ?>
                <?php require __DIR__ . '/../../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $user): ?>
              <?php $roles = $roleMap[$user['id']] ?? []; ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3">
                  <div class="font-semibold text-slate-900"><?= htmlspecialchars($user['full_name']) ?></div>
                  <div class="text-xs text-slate-500"><?= htmlspecialchars($user['email']) ?></div>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <?php foreach ($roles as $role): ?>
                    <span class="badge badge-purple"><?= htmlspecialchars($role) ?></span>
                  <?php endforeach; ?>
                  <?php if (empty($roles)): ?>
                    <span class="text-xs text-slate-400">No roles</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                  <span class="badge <?= $user['status'] === 'ACTIVE' ? 'badge-success' : 'badge-neutral' ?>"><?= htmlspecialchars($user['status']) ?></span>
                </td>
                <td class="px-4 py-3">
                  <a href="/admin/users/<?= (int)$user['id'] ?>" class="btn btn-secondary btn-xs">Edit</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
