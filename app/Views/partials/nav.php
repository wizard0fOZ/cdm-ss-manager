<?php
  $pageTitle = $pageTitle ?? 'Dashboard';
  $pageSubtitle = $pageSubtitle ?? 'Manage Sunday School operations with clarity.';
  $userLabel = $userLabel ?? 'Staff';
?>
<header class="flex flex-col gap-4 border-b border-slate-200 bg-white/70 px-6 py-5 glass lg:flex-row lg:items-center lg:justify-between">
  <div class="flex items-center gap-4">
    <button type="button" class="lg:hidden rounded-xl border border-slate-200 px-3 py-2 text-slate-600" data-sidebar-open aria-label="Open menu">
      Menu
    </button>
    <div>
      <h1 class="font-display text-2xl text-slate-900"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="text-sm text-slate-500"><?= htmlspecialchars($pageSubtitle) ?></p>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <div class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 lg:flex">
      <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
      System healthy
    </div>
    <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600">
      <span class="font-semibold text-slate-900"><?= htmlspecialchars($userLabel) ?></span>
      <span class="text-xs text-slate-400">Online</span>
    </div>
    <button
      type="button"
      class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
      onclick="document.getElementById('logout-form')?.submit()"
    >
      Logout
    </button>
  </div>
</header>
