</main>
<?php if (!empty($GLOBALS['layout_has_sidebar'])): ?></div></div><?php endif; ?>

<script>
(function() {
  function initSidebarToggle() {
    var wrapper = document.getElementById('wrapper');
    var toggleBtn = document.getElementById('sidebar-toggle');
    var overlay = document.getElementById('sidebar-overlay');
    var openBtn = document.getElementById('sidebar-open-btn');
    if (!wrapper) return;
    // Na página do PDV, em mobile, menu começa fechado para não cobrir a tela
    if (wrapper.classList.contains('pos-page') && window.matchMedia('(max-width: 768px)').matches) {
      wrapper.classList.add('toggled');
    }
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function() { wrapper.classList.toggle('toggled'); });
    }
    if (overlay) {
      overlay.addEventListener('click', function() { wrapper.classList.add('toggled'); });
    }
    if (openBtn) {
      openBtn.addEventListener('click', function() { wrapper.classList.remove('toggled'); });
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarToggle);
  } else {
    initSidebarToggle();
  }
})();
</script>
<script>
(function() {
  function closeAllDropdowns() {
    document.querySelectorAll('.js-dropdown-panel').forEach(function(p) {
      p.classList.remove('is-open');
      p.setAttribute('aria-hidden', 'true');
    });
  }
  function initDropdowns() {
    document.querySelectorAll('.js-dropdown-trigger').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var wrap = btn.closest('.dropdown-wrap');
        var panel = wrap ? wrap.querySelector('.js-dropdown-panel') : null;
        if (!panel) return;
        var wasOpen = panel.classList.contains('is-open');
        closeAllDropdowns();
        if (!wasOpen) {
          panel.classList.add('is-open');
          panel.setAttribute('aria-hidden', 'false');
        }
      });
    });
    document.addEventListener('click', function(e) {
      if (e.target.closest('.js-dropdown-trigger') || e.target.closest('.js-dropdown-panel')) return;
      closeAllDropdowns();
    });
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeAllDropdowns();
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDropdowns);
  } else {
    initDropdowns();
  }
})();
</script>

</body>

</html>