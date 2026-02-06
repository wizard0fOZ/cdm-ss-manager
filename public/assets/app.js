(function () {
  const openBtn = document.querySelector('[data-sidebar-open]');
  const closeBtn = document.querySelector('[data-sidebar-close]');
  const overlay = document.querySelector('[data-sidebar-overlay]');
  const sidebar = document.querySelector('[data-sidebar]');

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('-translate-x-full');
    overlay?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('-translate-x-full');
    overlay?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  openBtn?.addEventListener('click', openSidebar);
  closeBtn?.addEventListener('click', closeSidebar);
  overlay?.addEventListener('click', closeSidebar);
})();

(function () {
  if (!window.Choices) return;
  const selects = document.querySelectorAll('select[data-enhance="search"]');
  selects.forEach((select) => {
    if (select.dataset.enhanced === '1') return;
    new Choices(select, {
      searchEnabled: true,
      shouldSort: false,
      itemSelectText: '',
    });
    select.dataset.enhanced = '1';
  });
})();

(function () {
  if (!window.CDM_ANNOUNCEMENT_PREVIEW) return;
  const titleInput = document.querySelector('[data-announcement-title]');
  const messageInput = document.querySelector('[data-announcement-message]');
  const titlePreview = document.querySelector('[data-announcement-preview-title]');
  const messagePreview = document.querySelector('[data-announcement-preview-message]');

  const sync = () => {
    if (titlePreview) titlePreview.textContent = titleInput?.value || 'Announcement title';
    if (messagePreview) {
      const value = messageInput?.value || 'Announcement message will appear here.';
      messagePreview.innerHTML = value.replace(/\n/g, '<br>');
    }
  };

  if (titleInput) titleInput.addEventListener('input', sync);
  if (messageInput) messageInput.addEventListener('input', sync);
  sync();
})();

(function () {
  if (!window.CDM_CALENDAR_FORM) return;

  const form = document.querySelector('[data-calendar-form]');
  if (!form) return;

  const allDay = form.querySelector('[data-calendar-all-day]');
  const timeRow = form.querySelector('[data-calendar-times]');
  const scope = form.querySelector('[data-calendar-scope]');
  const classWrap = form.querySelector('[data-calendar-class]');

  const syncAllDay = () => {
    const isAllDay = allDay && allDay.checked;
    if (timeRow) timeRow.style.display = isAllDay ? 'none' : 'grid';
  };

  const syncScope = () => {
    const isClass = scope && scope.value === 'CLASS';
    if (classWrap) classWrap.style.display = isClass ? 'block' : 'none';
  };

  if (allDay) {
    allDay.addEventListener('change', syncAllDay);
    syncAllDay();
  }

  if (scope) {
    scope.addEventListener('change', syncScope);
    syncScope();
  }
})();

(function () {
  if (!window.CDM_CALENDAR_DRAWER) return;
  const data = window.CDM_CALENDAR_DAYS || {};
  const overlay = document.querySelector('[data-calendar-overlay]');
  const drawer = document.querySelector('[data-calendar-drawer]');
  const title = document.querySelector('[data-calendar-drawer-title]');
  const body = document.querySelector('[data-calendar-drawer-body]');
  const closeBtn = document.querySelector('[data-calendar-drawer-close]');

  const close = () => {
    overlay?.classList.add('hidden');
    drawer?.classList.add('translate-x-full');
  };

  const open = (dateKey) => {
    const items = data[dateKey] || [];
    if (title) title.textContent = dateKey;
    if (body) {
      if (!items.length) {
        body.innerHTML = '<div class="text-sm text-slate-500">No events for this date.</div>';
      } else {
        body.innerHTML = items.map((event) => {
          const time = event.all_day ? 'All day' : `${event.start_time} - ${event.end_time}`;
          const scope = event.scope === 'CLASS' ? `Class • ${event.class_name || 'Unknown'}` : 'Global';
          return `
            <div class="rounded-lg border border-slate-200 px-3 py-2">
              <div class="text-sm font-semibold text-slate-900">${event.title}</div>
              <div class="text-xs text-slate-500">${scope} • ${event.category}</div>
              <div class="mt-1 text-xs text-slate-600">${time}</div>
            </div>
          `;
        }).join('');
      }
    }

    overlay?.classList.remove('hidden');
    drawer?.classList.remove('translate-x-full');
  };

  document.querySelectorAll('[data-calendar-day]').forEach((cell) => {
    cell.addEventListener('click', () => {
      const key = cell.getAttribute('data-calendar-day');
      if (key) open(key);
    });
  });

  overlay?.addEventListener('click', close);
  closeBtn?.addEventListener('click', close);
})();
