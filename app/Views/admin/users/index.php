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
      <form method="get" class="grid gap-3 md:grid-cols-3">
        <div>
          <label class="text-xs text-slate-500">Search</label>
          <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name or email" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-slate-500">Status</label>
          <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
            <option value="INACTIVE" <?= $status === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
          <a href="/admin/users" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        </div>
      </form>

      <a href="/admin/users/create" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add User</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
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
                <?php $message = 'No users found.'; ?>
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
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars(implode(', ', $roles)) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($user['status']) ?></td>
                <td class="px-4 py-3">
                  <a href="/admin/users/<?= (int)$user['id'] ?>" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
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
