<?php
  $user = $user ?? [];
  $assigned = $assigned ?? [];
  $fullName = $user['full_name'] ?? '';
  $email = $user['email'] ?? '';
  $status = $user['status'] ?? 'ACTIVE';
  $mustChange = !empty($user['must_change_password']);
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
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">

  <div>
    <label class="text-sm text-slate-600">Full Name</label>
    <input name="full_name" value="<?= htmlspecialchars($fullName) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div>
    <label class="text-sm text-slate-600">Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Status</label>
      <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
        <option value="INACTIVE" <?= $status === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <div class="flex items-end">
      <label class="inline-flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="must_change_password" value="1" <?= $mustChange ? 'checked' : '' ?>>
        Must change password
      </label>
    </div>
  </div>

  <div>
    <label class="text-sm text-slate-600">Roles</label>
    <div class="mt-2 grid gap-2 md:grid-cols-2">
      <?php foreach ($roles as $role): ?>
        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
          <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($role['code']) ?>" <?= in_array($role['code'], $assigned, true) ? 'checked' : '' ?>>
          <?= htmlspecialchars($role['name']) ?>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!empty($showReset)): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="reset_password" value="1">
        Reset password to default
      </label>
    </div>
  <?php endif; ?>

  <div class="flex items-center gap-3">
    <button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
    <a href="/admin/users" class="btn btn-ghost btn-sm">Cancel</a>
  </div>
</form>
