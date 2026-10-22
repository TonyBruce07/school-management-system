// =====================================================================
// script.js — shared behaviour for public site + portal
// =====================================================================

document.addEventListener('DOMContentLoaded', function () {
  // ---------------------------------------------------------------
  // Portal responsive sidebar (off-canvas below the lg breakpoint)
  // ---------------------------------------------------------------
  var toggleBtn = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('portalSidebar') || document.querySelector('.portal-sidebar');
  var overlay = document.getElementById('sidebarOverlay');

  function openSidebar() {
    sidebar.classList.add('open');
    document.body.classList.add('sidebar-open');
    if (overlay) overlay.classList.add('show');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    document.body.classList.remove('sidebar-open');
    if (overlay) overlay.classList.remove('show');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
  }

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function (e) {
      // Stop this click from also being seen by the "click outside"
      // listener below on the same event bubble.
      e.stopPropagation();
      if (sidebar.classList.contains('open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });

    if (overlay) {
      overlay.addEventListener('click', closeSidebar);
    }

    var closeBtn = document.getElementById('sidebarClose');
    if (closeBtn) {
      closeBtn.addEventListener('click', closeSidebar);
    }

    // Click anywhere outside the open sidebar (and not on the toggle
    // button or any of its children, e.g. the icon) closes it.
    document.addEventListener('click', function (e) {
      if (sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          !toggleBtn.contains(e.target)) {
        closeSidebar();
      }
    });

    // Escape key closes the sidebar for keyboard users.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('open')) {
        closeSidebar();
      }
    });

    // If the window is resized back up to desktop width, make sure
    // the off-canvas "open" state doesn't linger.
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 992) {
        closeSidebar();
      }
    });
  }

  // Auto-dismiss flash alerts after 5 seconds
  document.querySelectorAll('.alert').forEach(function (alertEl) {
    setTimeout(function () {
      if (window.bootstrap) {
        var instance = bootstrap.Alert.getOrCreateInstance(alertEl);
        instance.close();
      }
    }, 5000);
  });

  // Bootstrap client-side validation styling (server-side is authoritative)
  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });

  // Confirm before destructive delete actions
  document.querySelectorAll('.btn-delete-confirm').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var label = btn.getAttribute('data-label') || 'this record';
      if (!confirm('Are you sure you want to delete ' + label + '? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });
});
