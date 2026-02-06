<?php
  $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
  $tabs = [
    ['label' => 'Users', 'href' => '/admin/users'],
    ['label' => 'Roles & Permissions', 'href' => '/admin/roles'],
    ['label' => 'System Settings', 'href' => '/admin/settings'],
    ['label' => 'Monitoring', 'href' => '/admin/monitoring'],
    ['label' => 'Maintenance', 'href' => '/admin/maintenance'],
  ];
?>
<div class="mb-5 flex flex-wrap gap-2">
  <?php foreach ($tabs as $tab):
    $active = $currentPath === $tab['href'];
    $classes = $active ? 'bg-slate-900 text-white' : 'border border-slate-200 text-slate-600';
  ?>
    <a href="<?= htmlspecialchars($tab['href']) ?>" class="rounded-xl px-4 py-2 text-sm font-semibold <?= $classes ?>">
      <?= htmlspecialchars($tab['label']) ?>
    </a>
  <?php endforeach; ?>
</div>
