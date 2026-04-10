document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("togglePass");
    const pass = document.getElementById("password");
    const eye = document.getElementById("eyeIcon");

    if (toggle) {
        toggle.addEventListener("click", () => {
            const isHidden = pass.type === "password";
            pass.type = isHidden ? "text" : "password";

            eye.src = isHidden
                ? "/assets/img/eye-off.png"
                : "/assets/img/eye.png";
        });
    }
});
