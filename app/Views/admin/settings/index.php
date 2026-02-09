<?php
  $pageTitle = 'Admin • Settings';
  $pageSubtitle = 'System configuration values.';

  $settings = $settings ?? [];
  $map = [];
  foreach ($settings as $setting) {
    $map[$setting['setting_key']] = $setting['setting_value'];
  }

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>

  <div class="grid gap-6">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="text-sm font-semibold text-slate-900">Maintenance Mode</div>
      <p class="mt-1 text-xs text-slate-500">Requires SysAdmin password to change.</p>
      <form method="post" action="/admin/settings" class="mt-3 grid gap-3 md:grid-cols-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <input type="hidden" name="maintenance_batch" value="1">
        <div>
          <label class="text-xs text-slate-500">Mode</label>
          <select name="maintenance_mode" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <?php $current = strtoupper($map['maintenance_mode'] ?? 'OFF'); ?>
            <option value="OFF" <?= $current === 'OFF' ? 'selected' : '' ?>>Off</option>
            <option value="PUBLIC" <?= $current === 'PUBLIC' ? 'selected' : '' ?>>Public Only</option>
            <option value="STAFF" <?= $current === 'STAFF' ? 'selected' : '' ?>>Staff Only</option>
            <option value="BOTH" <?= $current === 'BOTH' ? 'selected' : '' ?>>Public + Staff</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="text-xs text-slate-500">Message</label>
          <input name="maintenance_message" value="<?= htmlspecialchars($map['maintenance_message'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="We are currently performing maintenance.">
        </div>
        <div>
          <label class="text-xs text-slate-500">SysAdmin Password</label>
          <input type="password" name="override_password" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Confirm password">
        </div>
        <div class="flex items-end">
          <button class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="text-sm font-semibold text-slate-900">Presets</div>
      <p class="mt-1 text-xs text-slate-500">Quick settings you can store in the system settings table.</p>
      <form method="post" action="/admin/settings" class="mt-3 grid gap-3 md:grid-cols-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <div>
          <label class="text-xs text-slate-500">Setting Key</label>
          <select name="setting_key" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="attendance_lock_time">attendance_lock_time</option>
            <option value="attendance_lock_day">attendance_lock_day</option>
            <option value="active_year_label">active_year_label</option>
            <option value="public_contact_email">public_contact_email</option>
            <option value="public_whatsapp">public_whatsapp</option>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Value</label>
          <input name="setting_value" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="e.g. 23:59">
        </div>
        <div class="flex items-end">
          <button class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Key</th>
            <th class="px-4 py-3">Value</th>
            <th class="px-4 py-3">Updated</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($settings)): ?>
            <tr>
              <td colspan="3" class="px-4 py-6">
                <?php $message = 'No settings found.'; ?>
                <?php require __DIR__ . '/../../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($settings as $setting): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($setting['setting_key']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($setting['setting_value'] ?? '') ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($setting['updated_at'] ?? '') ?></td>
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
