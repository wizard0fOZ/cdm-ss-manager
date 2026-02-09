(function () {
  const openBtn = document.querySelector('[data-sidebar-open]');
  const closeBtn = document.querySelector('[data-sidebar-close]');
  const overlay = document.querySelector('[data-sidebar-overlay]');
  const sidebar = document.querySelector('[data-sidebar]');
  const collapseBtn = document.querySelector('[data-sidebar-collapse]');

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

  function setCollapsed(collapsed) {
    document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
    localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0');
  }

  if (collapseBtn) {
    const stored = localStorage.getItem('sidebar_collapsed') === '1';
    setCollapsed(stored);
    collapseBtn.addEventListener('click', () => {
      const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
      setCollapsed(!isCollapsed);
    });
  }
})();

(function () {
  const root = document.documentElement;
  const toggle = document.querySelector('[data-theme-toggle]');
  const stored = localStorage.getItem('theme');
  const systemQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  function setTheme(theme, persist = true) {
    if (theme === 'dark') {
      root.classList.add('theme-dark');
    } else {
      root.classList.remove('theme-dark');
    }
    if (persist) {
      localStorage.setItem('theme', theme);
    }
    const sun = document.querySelector('[data-theme-icon-sun]');
    const moon = document.querySelector('[data-theme-icon-moon]');
    if (sun && moon) {
      if (theme === 'dark') {
        sun.classList.add('hidden');
        moon.classList.remove('hidden');
      } else {
        moon.classList.add('hidden');
        sun.classList.remove('hidden');
      }
    }
  }

  function applySystemTheme() {
    if (!systemQuery) return;
    setTheme(systemQuery.matches ? 'dark' : 'light', false);
  }

  if (stored === 'dark' || stored === 'light') {
    setTheme(stored);
  } else {
    applySystemTheme();
    systemQuery?.addEventListener?.('change', applySystemTheme);
  }

  toggle?.addEventListener('click', () => {
    const isDark = root.classList.contains('theme-dark');
    setTheme(isDark ? 'light' : 'dark');
    systemQuery?.removeEventListener?.('change', applySystemTheme);
  });
})();

(function () {
  if (!window.Alpine) return;
  window.confirmModal = function () {
    return {
      open: false,
      title: 'Confirm Action',
      message: 'Are you sure?',
      confirmText: 'Confirm',
      form: null,
      show(detail) {
        this.title = detail?.title || 'Confirm Action';
        this.message = detail?.message || 'Are you sure?';
        this.confirmText = detail?.confirmText || 'Confirm';
        this.form = detail?.form || null;
        this.open = true;
      },
      close() {
        this.open = false;
        this.form = null;
      },
      confirm() {
        if (this.form) {
          this.form.submit();
        }
        this.close();
      },
    };
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) return;
    const formId = trigger.getAttribute('data-confirm-form');
    const form = formId ? document.getElementById(formId) : trigger.closest('form');
    if (!form) return;
    event.preventDefault();
    window.dispatchEvent(new CustomEvent('cdm-confirm', {
      detail: {
        title: trigger.getAttribute('data-confirm-title') || 'Confirm Action',
        message: trigger.getAttribute('data-confirm-message') || 'Are you sure?',
        confirmText: trigger.getAttribute('data-confirm-text') || 'Confirm',
        form,
      },
    }));
  });
})();

(function () {
  function initBulkSelect() {
    const selects = document.querySelectorAll('[data-select-all]');
    selects.forEach((selectAll) => {
      if (selectAll.dataset.bound === '1') return;
      selectAll.addEventListener('change', () => {
        const scope = selectAll.closest('form') || document;
        scope.querySelectorAll('[data-select-item]').forEach((item) => {
          item.checked = selectAll.checked;
        });
      });
      selectAll.dataset.bound = '1';
    });
  }

  initBulkSelect();
  if (window.htmx) {
    document.addEventListener('htmx:afterSwap', initBulkSelect);
  }
})();

(function () {
  if (!window.Choices) return;
  function initChoices(root = document) {
    const selects = root.querySelectorAll('select[data-enhance="search"]');
    selects.forEach((select) => {
      if (select.dataset.enhanced === '1') return;
      new Choices(select, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: '',
        position: 'auto',
        fuseOptions: { threshold: 0.3 },
      });
      select.dataset.enhanced = '1';
    });
  }

  initChoices();
  if (window.htmx) {
    document.addEventListener('htmx:afterSwap', (event) => {
      initChoices(event.target);
    });
  }

  document.addEventListener('showDropdown', (event) => {
    const select = event.target;
    if (!(select instanceof HTMLElement)) return;
    const bar = select.closest('.filter-bar');
    bar?.classList.add('filter-bar--open');
  });

  document.addEventListener('hideDropdown', (event) => {
    const select = event.target;
    if (!(select instanceof HTMLElement)) return;
    const bar = select.closest('.filter-bar');
    bar?.classList.remove('filter-bar--open');
  });
})();

(function () {
  function validateField(input) {
    const field = input.getAttribute('data-field');
    if (!field) return;
    const errorEl = document.querySelector(`[data-error-for=\"${field}\"]`);
    if (!errorEl) return;

    const type = input.getAttribute('data-validate') || '';
    const value = (input.value || '').trim();
    let message = '';

    if (type.includes('required') && value === '') {
      message = 'This field is required.';
    } else if (type.includes('email') && value !== '') {
      const emailOk = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(value);
      if (!emailOk) message = 'Enter a valid email.';
    }

    errorEl.textContent = message;
  }

  function initInlineValidation(root = document) {
    root.querySelectorAll('[data-validate]').forEach((input) => {
      if (input.dataset.bound === '1') return;
      input.addEventListener('blur', () => validateField(input));
      input.addEventListener('input', () => {
        if (input.value === '') return;
        validateField(input);
      });
      input.dataset.bound = '1';
    });
  }

  initInlineValidation();
  if (window.htmx) {
    document.addEventListener('htmx:afterSwap', (event) => {
      initInlineValidation(event.target);
    });
  }
})();

(function () {
  function initUnsaved(root = document) {
    root.querySelectorAll('form[data-unsaved]').forEach((form) => {
      if (form.dataset.boundUnsaved === '1') return;
      const banner = document.querySelector('[data-unsaved-banner]');
      const markDirty = () => {
        form.dataset.dirty = '1';
        if (banner) banner.classList.remove('hidden');
      };
      form.querySelectorAll('input, select, textarea').forEach((input) => {
        input.addEventListener('change', markDirty);
        input.addEventListener('input', markDirty);
      });
      form.addEventListener('submit', () => {
        form.dataset.dirty = '0';
        if (banner) banner.classList.add('hidden');
      });
      window.addEventListener('beforeunload', (event) => {
        if (form.dataset.dirty === '1') {
          event.preventDefault();
          event.returnValue = '';
        }
      });
      form.dataset.boundUnsaved = '1';
    });
  }

  initUnsaved();
  if (window.htmx) {
    document.addEventListener('htmx:afterSwap', (event) => {
      initUnsaved(event.target);
    });
  }
})();

(function () {
  function initImportPreview(root = document) {
    const form = root.querySelector('[data-import-preview]');
    if (!form) return;
    const mappingInput = form.querySelector('[data-import-mapping]');
    const selects = form.querySelectorAll('[data-map-field]');
    const overrideInput = form.querySelector('[data-override-password]');
    const runButton = form.querySelector('[data-run-import]');
    const dynamic = form.querySelector('[data-import-preview-dynamic]');

    const updateMapping = () => {
      if (!mappingInput) return;
      const mapping = {};
      selects.forEach((select) => {
        mapping[select.dataset.mapField] = select.value;
      });
      mappingInput.value = JSON.stringify(mapping);
    };

    const updateRunState = () => {
      if (!runButton) return;
      const missingRequired = dynamic?.dataset.missingRequired === '1';
      if (!missingRequired) {
        runButton.disabled = false;
        return;
      }
      if (!overrideInput) {
        runButton.disabled = true;
        return;
      }
      runButton.disabled = overrideInput.value.trim() === '';
    };

    selects.forEach((select) => select.addEventListener('change', updateMapping));
    overrideInput?.addEventListener('input', updateRunState);
    form.addEventListener('htmx:beforeRequest', updateMapping);
    updateMapping();
    updateRunState();
  }

  initImportPreview();
  if (window.htmx) {
    document.addEventListener('htmx:afterSwap', (event) => {
      if (event.target?.querySelector?.('[data-import-preview]')) {
        initImportPreview(event.target);
      }
    });
  }
})();

(function () {
  if (!window.htmx) return;
  document.addEventListener('htmx:beforeRequest', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.tagName === 'FORM') {
      target.querySelectorAll('button, input[type="submit"]').forEach((btn) => {
        btn.disabled = true;
        btn.dataset.loading = '1';
      });
    }
  });

  document.addEventListener('htmx:afterRequest', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.tagName === 'FORM') {
      target.querySelectorAll('button, input[type="submit"]').forEach((btn) => {
        btn.disabled = false;
        delete btn.dataset.loading;
      });
    }
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
  const overlay = document.querySelector('[data-calendar-overlay]');
  const drawer = document.querySelector('[data-calendar-drawer]');
  const title = document.querySelector('[data-calendar-drawer-title]');
  const closeBtn = document.querySelector('[data-calendar-drawer-close]');

  const close = () => {
    overlay?.classList.add('hidden');
    drawer?.classList.add('translate-x-full');
  };

  const open = (dateKey) => {
    if (title) title.textContent = dateKey;
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
