<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $statusColors = [
    'ACTIVE' => 'badge-success',
    'INACTIVE' => 'badge-neutral',
    'GRADUATED' => 'badge-info',
    'TRANSFERRED' => 'badge-warning',
  ];
  $currentStatus = $student['status'] ?? 'ACTIVE';
  $badgeClass = $statusColors[$currentStatus] ?? 'badge-neutral';
?>
<td class="px-4 py-3">
  <form method="post"
        action="/students/<?= (int)$student['id'] ?>/status"
        hx-post="/students/<?= (int)$student['id'] ?>/status"
        hx-trigger="change from:select"
        hx-target="closest td"
        hx-swap="outerHTML">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <select name="status"
            class="badge <?= $badgeClass ?> cursor-pointer border-0 appearance-none pr-5 bg-[length:12px] bg-[right_4px_center] bg-no-repeat"
            style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M3 5l3 3 3-3' fill='none' stroke='%2364748b' stroke-width='1.5'/%3E%3C/svg%3E&quot;);">
      <?php foreach (['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'] as $opt): ?>
        <option value="<?= $opt ?>" <?= $currentStatus === $opt ? 'selected' : '' ?>><?= $opt ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</td>
