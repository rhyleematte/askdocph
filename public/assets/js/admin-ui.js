/* ================================================================
   ADMIN UI LOGIC
   Handles theme toggles and header dropdowns for the admin panel
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) lucide.createIcons();

    /* ── Theme toggle ───────────────────────────────────────── */
    var root = document.documentElement;
    var savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') root.classList.add('theme-dark');

    var themeBtn = document.getElementById('themeToggleBtn');

    function updateThemeBtn() {
      if (!themeBtn) return;
      var isDark = root.classList.contains('theme-dark');
      var label = themeBtn.querySelector('span');
      var icon = themeBtn.querySelector('i');
      if (label) label.textContent = isDark ? 'Light mode' : 'Dark mode';
      if (icon) icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
      if (window.lucide) lucide.createIcons();
    }
    updateThemeBtn();

    if (themeBtn) {
      themeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        root.classList.toggle('theme-dark');
        localStorage.setItem('theme', root.classList.contains('theme-dark') ? 'dark' : 'light');
        updateThemeBtn();
      });
    }

    /* ── Profile dropdown ───────────────────────────────────── */
    var toggle = document.getElementById('profileToggle');
    var menu = document.getElementById('profileDropdown');

    if (toggle && menu) {
      toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = menu.classList.contains('open');
        menu.classList.toggle('open', !isOpen);
        toggle.setAttribute('aria-expanded', String(!isOpen));
        if (!isOpen && window.lucide) lucide.createIcons();
      });
      menu.addEventListener('click', function (e) { e.stopPropagation(); });
      document.addEventListener('click', function () {
        menu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { menu.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }
      });
    }
});
