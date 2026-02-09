<?php
  $now = new DateTime('now');
  $start = new DateTime($item['start_at']);
  $end = new DateTime($item['end_at']);
  $pinUntil = !empty($item['pin_until']) ? new DateTime($item['pin_until']) : null;
  $isPinnedActive = !empty($item['is_pinned']) && (!$pinUntil || $pinUntil >= $now);
  if ($now < $start) {
    $status = 'Scheduled';
    $badge = 'bg-blue-100 text-blue-700';
  } elseif ($now > $end) {
    $status = 'Expired';
    $badge = 'bg-slate-100 text-slate-600';
  } else {
    $status = 'Active';
    $badge = 'bg-emerald-100 text-emerald-700';
  }
  $scopeLabel = $item['scope'] === 'CLASS' ? ('Class • ' . ($item['class_name'] ?? 'Unknown')) : 'Global';
  $priority = (int)($item['priority'] ?? 0);
  $priorityLabel = $priority === 2 ? 'Urgent' : ($priority === 1 ? 'High' : 'Normal');
  $statusLabel = $item['status'] ?? 'PUBLISHED';
  $csrf = $_SESSION['_csrf'] ?? '';
?>
<tr class="border-t border-slate-200">
  <td class="px-4 py-3">
    <div class="flex flex-wrap items-center gap-2">
      <span class="font-semibold text-slate-900"><?= htmlspecialchars($item['title']) ?></span>
      <?php if ($isPinnedActive): ?>
        <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold text-amber-700">Pinned</span>
      <?php endif; ?>
      <?php if ($priority > 0): ?>
        <span class="rounded-full bg-rose-100 px-2 py-1 text-[10px] font-semibold text-rose-700"><?= htmlspecialchars($priorityLabel) ?></span>
      <?php endif; ?>
      <?php if (!empty($isAdmin)): ?>
        <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600"><?= htmlspecialchars($statusLabel) ?></span>
      <?php endif; ?>
    </div>
    <div class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($item['message']) ?></div>
  </td>
  <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($scopeLabel) ?></td>
  <td class="px-4 py-3 text-slate-600">
    <?= htmlspecialchars($start->format('d M Y, H:i')) ?><br>
    <span class="text-xs text-slate-400">to</span> <?= htmlspecialchars($end->format('d M Y, H:i')) ?>
  </td>
  <td class="px-4 py-3">
    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold <?= $badge ?>"><?= $status ?></span>
  </td>
  <td class="px-4 py-3">
    <?php if (!empty($isAdmin)): ?>
      <div class="flex items-center gap-2">
        <form method="post"
              action="/announcements/<?= (int)$item['id'] ?>/toggle"
              hx-post="/announcements/<?= (int)$item['id'] ?>/toggle"
              hx-include="closest td"
              hx-target="closest tr"
              hx-swap="outerHTML">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="value" value="<?= ($item['status'] ?? '') === 'PUBLISHED' ? 0 : 1 ?>">
          <button class="btn btn-secondary btn-xs">
            <?= ($item['status'] ?? '') === 'PUBLISHED' ? 'Unpublish' : 'Publish' ?>
          </button>
        </form>
        <form method="post"
              action="/announcements/<?= (int)$item['id'] ?>/toggle"
              hx-post="/announcements/<?= (int)$item['id'] ?>/toggle"
              hx-include="closest td"
              hx-target="closest tr"
              hx-swap="outerHTML">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="action" value="pin">
          <input type="hidden" name="value" value="<?= !empty($item['is_pinned']) ? 0 : 1 ?>">
          <button class="btn btn-secondary btn-xs">
            <?= !empty($item['is_pinned']) ? 'Unpin' : 'Pin' ?>
          </button>
        </form>
        <a href="/announcements/<?= (int)$item['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
      </div>
    <?php else: ?>
      <span class="text-xs text-slate-400">View only</span>
    <?php endif; ?>
  </td>
</tr>
