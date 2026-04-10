document.addEventListener("DOMContentLoaded", () => {

    /* ---------------- LUCIDE ICONS ---------------- */
    if (window.lucide) {
        lucide.createIcons();
    }

    /* ---------------- PASSWORD TOGGLES ---------------- */

    function setupToggle(toggleId, inputId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);

        if (!toggle || !input || !window.lucide) return;

        toggle.addEventListener("click", () => {
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";

            toggle.innerHTML = isHidden
                ? '<i data-lucide="eye-off"></i>'
                : '<i data-lucide="eye"></i>';

            lucide.createIcons();
        });
    }

    setupToggle("togglePass", "password"); // Login
    setupToggle("toggleRegPass", "reg_password"); // Signup
    setupToggle("toggleRegConfirm", "reg_password_confirm"); // Confirm


    /* ---------------- AJAX SIGNUP ---------------- */

    const form = document.getElementById("signupForm");
    const msg = document.getElementById("signupMsg");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Clear old errors
        document.querySelectorAll(".error").forEach(el => el.remove());

        // Reset message
        if (msg) {
            msg.style.display = "none";
            msg.className = "alert";
            msg.textContent = "";
        }

        const formData = new FormData(form);
        const token = form.querySelector('input[name="_token"]')?.value;

        // ✅ SAFE dynamic route from Blade
        const url =
            document.getElementById("signupAjaxUrl")?.value ||
            "/signup-ajax";

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json",
                },
                body: formData,
            });

            const data = await res.json();

            /* ---------- VALIDATION ERRORS ---------- */
            if (!res.ok) {

                if (data.errors) {
                    Object.keys(data.errors).forEach((field) => {
                        const input = form.querySelector(`[name="${field}"]`);

                        if (input) {
                            const p = document.createElement("p");
                            p.className = "error";
                            p.textContent = data.errors[field][0];

                            input.closest(".input-group")?.after(p);
                        }
                    });
                    return;
                }

                /* ---------- OTHER ERRORS ---------- */
                if (msg) {
                    msg.style.display = "block";
                    msg.classList.add("danger");
                    msg.textContent = data.message || "Signup failed.";
                }
                return;
            }

            /* ---------- SUCCESS ---------- */
            if (msg) {
                msg.style.display = "block";
                msg.classList.add("success");
                msg.textContent = data.message || "Account created!";
            }

            /* ---------- REDIRECT ---------- */
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 800);
            }

        } catch (err) {
            if (msg) {
                msg.style.display = "block";
                msg.classList.add("danger");
                msg.textContent = "Network error. Please try again.";
            }
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        if (window.lucide) lucide.createIcons();

        const toggle = document.getElementById("profileToggle");
        const menu = document.getElementById("profileDropdown");

        if (toggle && menu) {
            const closeMenu = () => {
                menu.classList.remove("open");
                toggle.setAttribute("aria-expanded", "false");
            };

            toggle.addEventListener("click", (e) => {
                e.stopPropagation();
                const isOpen = menu.classList.toggle("open");
                toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            });

            document.addEventListener("click", closeMenu);

            // Close on ESC
            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape") closeMenu();
            });

            // Prevent closing when clicking inside dropdown
            menu.addEventListener("click", (e) => e.stopPropagation());
        }
    });






















});
