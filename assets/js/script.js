// Sidebar toggle (collapsible on desktop, off-canvas on mobile)
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const isMobile = () => window.innerWidth <= 992;

  if (toggle && sidebar) {
    // Restore saved collapse preference on desktop
    if (!isMobile() && localStorage.getItem('vms_sidebar_collapsed') === '1') {
      sidebar.classList.add('collapsed');
    }

    toggle.addEventListener('click', function () {
      if (isMobile()) {
        sidebar.classList.toggle('open');
      } else {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('vms_sidebar_collapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
      }
    });

    // Close the off-canvas sidebar when clicking outside it (mobile)
    document.addEventListener('click', function (e) {
      if (isMobile() && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
          sidebar.classList.remove('open');
        }
      }
    });

    // Keep classes sane when crossing the mobile/desktop breakpoint
    window.addEventListener('resize', function () {
      if (isMobile()) {
        sidebar.classList.remove('collapsed');
      } else {
        sidebar.classList.remove('open');
        if (localStorage.getItem('vms_sidebar_collapsed') === '1') {
          sidebar.classList.add('collapsed');
        }
      }
    });
  }

  // Auto-dismiss flash alerts
  document.querySelectorAll('.flash-alert').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 4500);
  });

  // Admin dropdown
  const chip = document.getElementById('adminChip');
  const dropdown = document.getElementById('adminDropdown');
  if (chip && dropdown) {
    chip.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.classList.toggle('show');
    });
    document.addEventListener('click', function () { dropdown.classList.remove('show'); });
  }
});

// Generic modal helpers
function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('show');
}
function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('show');
}
document.addEventListener('click', function (e) {
  if (e.target.classList && e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
  }
});
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
  }
});

// Populate an "edit" modal's form fields from data attributes on the triggering button
function fillEditForm(formId, data) {
  Object.keys(data).forEach(function (key) {
    const field = document.querySelector('#' + formId + ' [name="' + key + '"]');
    if (field) field.value = data[key];
  });
}

// Confirm before destructive actions
function confirmDelete(message) {
  return confirm(message || 'Are you sure you want to delete this record? This action cannot be undone.');
}

// Simple client-side table search filter
function filterTable(inputEl, tableId) {
  const query = inputEl.value.toLowerCase();
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(function (row) {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(query) ? '' : 'none';
  });
}

// ---------- Basic form validation (applies to every form on the site) ----------
document.addEventListener('DOMContentLoaded', function () {

  // 1) Password + Confirm Password must match, on any form that has both.
  //    Works for register forms (password/confirm_password) and
  //    profile "change password" forms (new_password/confirm_password).
  document.querySelectorAll('form').forEach(function (form) {
    const pass = form.querySelector('[name="password"], [name="new_password"]');
    const confirm = form.querySelector('[name="confirm_password"]');
    if (!pass || !confirm) return;

    function checkMatch() {
      if (confirm.value !== '' && confirm.value !== pass.value) {
        confirm.setCustomValidity('Passwords do not match.');
      } else {
        confirm.setCustomValidity('');
      }
    }
    pass.addEventListener('input', checkMatch);
    confirm.addEventListener('input', checkMatch);
  });

  // 2) Trim empty-looking (whitespace-only) required text fields on submit,
  //    so "   " can't slip through as a valid name/username/etc.
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      const requiredTextFields = form.querySelectorAll(
        'input[required][type="text"], input[required][type="email"], input[required][type="tel"], textarea[required]'
      );
      for (const field of requiredTextFields) {
        if (field.value.trim() === '') {
          field.value = '';
          field.reportValidity();
          e.preventDefault();
          return;
        }
      }
    });
  });

  // 3) Address textareas can't use the HTML5 "pattern" attribute (it only
  //    works on <input>), so check them here instead: must contain at
  //    least one real letter or digit, not just symbols/dots/whitespace.
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      const addressField = form.querySelector('textarea[name="address"]');
      if (!addressField) return;
      const value = addressField.value.trim();
      if (value !== '' && !/[A-Za-z0-9]/.test(value)) {
        addressField.setCustomValidity('Please enter a real address.');
        addressField.reportValidity();
        e.preventDefault();
      } else {
        addressField.setCustomValidity('');
      }
    });
  });
});
