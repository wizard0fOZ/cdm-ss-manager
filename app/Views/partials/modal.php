<div
  x-data="confirmModal()"
  x-show="open"
  x-transition.opacity.duration.200ms
  class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
  style="display:none;"
  x-on:cdm-confirm.window="show($event.detail)"
  x-on:keydown.escape.window="close()"
>
  <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.stop
       x-show="open"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95">
    <div class="flex items-start gap-3">
      <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
          <path d="M12 9v4"/><path d="M12 17h.01"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-base font-semibold text-slate-900" x-text="title"></div>
        <div class="mt-1 text-sm text-slate-500" x-text="message"></div>
      </div>
    </div>
    <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
      <button type="button" class="btn btn-secondary btn-sm" @click="close()">Cancel</button>
      <button type="button" class="btn btn-primary btn-sm" @click="confirm()"
              x-text="confirmText"></button>
    </div>
  </div>
</div>
