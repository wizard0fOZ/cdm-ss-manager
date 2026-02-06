<?php
  $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

  $announcementCount = 0;
  $userId = (int)($_SESSION['user_id'] ?? 0);
  $roleCodes = [];
  $isSysAdmin = false;
  $overrideCode = $_SESSION['_role_override_code'] ?? null;
  $overrideName = $_SESSION['_role_override_name'] ?? null;
  $rbac = new \App\Core\Rbac\Rbac();
  try {
    $pdo = \App\Core\Db\Db::pdo();
    $announcementCount = (int)$pdo->query("SELECT COUNT(*) FROM announcements WHERE status = 'PUBLISHED' AND start_at <= NOW() AND end_at >= NOW()")->fetchColumn();

    if ($userId > 0) {
      $stmt = $pdo->prepare('SELECT r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?');
      $stmt->execute([$userId]);
      $roleCodes = array_map(fn($row) => $row['code'], $stmt->fetchAll());
      $isSysAdmin = in_array('SYSADMIN', $roleCodes, true);
    }
  } catch (\Throwable $e) {
    $announcementCount = 0;
    $roleCodes = [];
    $isSysAdmin = false;
  }

  $can = function (string $perm) use ($rbac, $userId): bool {
    if ($userId <= 0) return false;
    return $rbac->can($userId, $perm);
  };

  $canAny = function (array $perms) use ($can): bool {
    foreach ($perms as $perm) {
      if ($can($perm)) return true;
    }
    return false;
  };

  $canStudents = $canAny(['students.view','students.create','students.edit','students.export']);
  $canClasses = $canAny(['classes.view','classes.manage']);
  $canAttendance = $canAny(['attendance.view','attendance.mark','attendance.bulk_mark']);
  $canLessons = $canAny(['lessons.view','lessons.create','lessons.edit','lessons.publish']);
  $canFaithBook = $canAny(['faithbook.view','faithbook.write','faithbook.edit']);
  $canTraining = $canAny(['training.view','training.manage']);
  $canAnnouncements = $canAny(['bulletins.manage']) || $userId > 0;
  $canCalendar = $userId > 0;
  if ($overrideCode) {
    $canReports = in_array($overrideCode, ['STAFF_ADMIN', 'SYSADMIN'], true);
  } else {
    $canReports = in_array('STAFF_ADMIN', $roleCodes, true) || $isSysAdmin;
  }
  $canImports = $canAny(['imports.view','imports.run']);
  $canAdmin = $canAny(['admin.users','admin.roles','admin.settings','admin.audit']) || $isSysAdmin;

  $nav = [
    ['label' => 'Dashboard', 'icon' => 'M3 3h18v18H3z', 'href' => '/dashboard', 'enabled' => true],
    ['label' => 'Students', 'icon' => 'M8 8a4 4 0 1 0 0.001 7.999A4 4 0 0 0 8 8Zm8 2a3 3 0 1 0 0.001 5.999A3 3 0 0 0 16 10ZM4 18c0-2.2 1.8-4 4-4s4 1.8 4 4v1H4v-1Zm10 1v-1c0-1.04-.24-2.02-.66-2.88.5-.11 1.03-.16 1.66-.16 2.2 0 4 1.8 4 4v1h-5Z', 'href' => '/students', 'enabled' => $canStudents],
    ['label' => 'Academic Years', 'icon' => 'M4 5h16v4H4zm0 6h10v8H4z', 'href' => '/academic-years', 'enabled' => $canClasses],
    ['label' => 'Terms', 'icon' => 'M6 4h12v4H6zm0 6h12v10H6z', 'href' => '/terms', 'enabled' => $canClasses],
    ['label' => 'Sessions', 'icon' => 'M5 4h14v3H5zm0 5h14v3H5zm0 5h14v3H5z', 'href' => '/sessions', 'enabled' => $canClasses],
    ['label' => 'Classes', 'icon' => 'M5 4h14v3H5zm0 5h14v3H5zm0 5h14v3H5z', 'href' => '/classes', 'enabled' => $canClasses],
    ['label' => 'Attendance', 'icon' => 'M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 5h6v2H9V8Zm0 4h6v2H9v-2Z', 'href' => '/attendance', 'enabled' => $canAttendance],
    ['label' => 'Lessons', 'icon' => 'M4 5h7v14H4V5Zm9 0h7v10h-7V5Zm0 12h7v2h-7v-2Z', 'href' => '/lessons', 'enabled' => $canLessons],
    ['label' => 'Faith Book', 'icon' => 'M5 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 4h8v2H6V8Zm0 4h8v2H6v-2Z', 'href' => '/faith-book', 'enabled' => $canFaithBook],
    ['label' => 'Training', 'icon' => 'M4 6h16v2H4V6Zm2 4h12l-6 6-6-6Z', 'href' => '/training', 'enabled' => $canTraining],
    ['label' => 'Announcements', 'icon' => 'M4 12h16M7 6h10M7 18h10', 'href' => '/announcements', 'enabled' => $canAnnouncements, 'badge' => $announcementCount],
    ['label' => 'Calendar', 'icon' => 'M6 4h12v3H6zm-2 5h16v11H4z', 'href' => '/calendar', 'enabled' => $canCalendar],
    ['label' => 'Reports', 'icon' => 'M4 4h6v16H4V4Zm10 6h6v10h-6V10Z', 'href' => '/reports', 'enabled' => $canReports],
    ['label' => 'Imports', 'icon' => 'M12 3v10m0 0 4-4m-4 4-4-4M5 17h14v4H5v-4Z', 'href' => '/imports', 'enabled' => $canImports],
    ['label' => 'Admin', 'icon' => 'M12 4a4 4 0 1 0 0.001 7.999A4 4 0 0 0 12 4Zm-7 14a7 7 0 0 1 14 0v2H5v-2Z', 'href' => '/admin', 'enabled' => $canAdmin],
  ];
?>
<aside class="sidebar-shadow fixed inset-y-0 left-0 z-40 w-72 -translate-x-full bg-white transition-transform duration-200 lg:static lg:translate-x-0" data-sidebar>
  <div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5">
      <div class="flex items-center gap-3">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white font-display text-lg">CDM</div>
        <div>
          <div class="text-sm font-semibold text-slate-900">CDM SS Manager</div>
          <div class="text-xs text-slate-500">Internal Console</div>
        </div>
      </div>
      <button type="button" class="lg:hidden text-slate-500" data-sidebar-close aria-label="Close menu">✕</button>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
      <div class="mb-3 text-xs uppercase tracking-widest text-slate-400 px-3">Modules</div>
      <?php if (!empty($overrideName)): ?>
        <div class="mb-3 mx-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-800">
          Viewing as <?= htmlspecialchars($overrideName) ?>
        </div>
      <?php endif; ?>
      <nav class="space-y-1">
        <?php foreach ($nav as $item):
          if (!$item['enabled']) continue;
          $active = $item['href'] === $currentPath;
          $href = $item['href'];
          $classes = $active
            ? 'bg-slate-900 text-white'
            : 'text-slate-700 hover:bg-slate-100';
        ?>
          <a
            href="<?= htmlspecialchars($href) ?>"
            class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition <?= $classes ?>"
          >
            <svg class="h-5 w-5 <?= $active ? 'text-white' : 'text-slate-500' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="<?= $item['icon'] ?>"></path>
            </svg>
            <span><?= htmlspecialchars($item['label']) ?></span>
            <?php if (!empty($item['badge'])): ?>
              <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"><?= (int)$item['badge'] ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>

    <div class="border-t border-slate-200 px-5 py-4 text-xs text-slate-500">
      Version 0.2 • Phase 9
    </div>
  </div>
</aside>
