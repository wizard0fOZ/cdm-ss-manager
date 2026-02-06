<?php
  $pageTitle = 'Audit Detail';
  $pageSubtitle = 'Technical audit information.';

  $before = $audit['before_json'] ?? null;
  $after = $audit['after_json'] ?? null;
  $beforePretty = $before ? json_encode(json_decode($before, true), JSON_PRETTY_PRINT) : '';
  $afterPretty = $after ? json_encode(json_decode($after, true), JSON_PRETTY_PRINT) : '';

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>

  <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-4">
    <div>
      <div class="text-xs text-slate-500">Action</div>
      <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($audit['action'] ?? '') ?></div>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
      <div>
        <div class="text-xs text-slate-500">Entity</div>
        <div class="text-sm text-slate-900"><?= htmlspecialchars(($audit['entity_type'] ?? '') . ' #' . ($audit['entity_id'] ?? '')) ?></div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Actor</div>
        <div class="text-sm text-slate-900"><?= htmlspecialchars((string)($audit['actor_user_id'] ?? '')) ?></div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Time</div>
        <div class="text-sm text-slate-900"><?= htmlspecialchars($audit['created_at'] ?? '') ?></div>
      </div>
    </div>
    <div>
      <div class="text-xs text-slate-500">IP / User Agent</div>
      <div class="text-sm text-slate-900"><?= htmlspecialchars((string)($audit['ip_address'] ?? '')) ?></div>
      <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars((string)($audit['user_agent'] ?? '')) ?></div>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <div class="text-xs text-slate-500">Before</div>
        <pre class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($beforePretty ?: '—') ?></pre>
      </div>
      <div>
        <div class="text-xs text-slate-500">After</div>
        <pre class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($afterPretty ?: '—') ?></pre>
      </div>
    </div>
    <div>
      <a href="/admin/monitoring" class="text-sm text-slate-600">Back to Monitoring</a>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
