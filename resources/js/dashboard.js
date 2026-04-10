document.addEventListener("DOMContentLoaded", () => {
  // Load Lucide icons (if available)
  if (window.lucide) lucide.createIcons();

  const root = document.documentElement; // <html>

  // Dropdown elements
  const toggle = document.getElementById("profileToggle");
  const menu = document.getElementById("profileDropdown");

  // Theme button inside dropdown
  const themeBtn = document.getElementById("themeToggleBtn");

  /* ----------------------------
     THEME: load + toggle
  ----------------------------- */
  const savedTheme = localStorage.getItem("theme"); // "dark" | "light"
  if (savedTheme === "dark") root.classList.add("theme-dark");

  const updateThemeBtn = () => {
    if (!themeBtn) return;

    const isDark = root.classList.contains("theme-dark");
    const label = themeBtn.querySelector("span");
    const icon = themeBtn.querySelector("i");

    if (label) label.textContent = isDark ? "Light mode" : "Dark mode";
    if (icon) icon.setAttribute("data-lucide", isDark ? "sun" : "moon");

    if (window.lucide) lucide.createIcons();
  };

  updateThemeBtn();

  if (themeBtn) {
    themeBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      root.classList.toggle("theme-dark");
      localStorage.setItem("theme", root.classList.contains("theme-dark") ? "dark" : "light");
      updateThemeBtn();
    });
  }

  /* ----------------------------
     DROPDOWN: open/close
  ----------------------------- */
  if (!toggle || !menu) return;

  const setOpen = (open) => {
    menu.classList.toggle("open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    if (window.lucide) lucide.createIcons();
  };

  toggle.addEventListener("click", (e) => {
    e.stopPropagation();
    setOpen(!menu.classList.contains("open"));
  });

  // Don't close when clicking inside the menu
  menu.addEventListener("click", (e) => e.stopPropagation());

  // Close when clicking outside
  document.addEventListener("click", () => setOpen(false));

  // Close on ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setOpen(false);
  });
});