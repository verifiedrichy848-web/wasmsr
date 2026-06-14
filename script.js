// script.js → FINAL VERSION – works on desktop + ALL mobile browsers
document.addEventListener("DOMContentLoaded", function () {

    // DROPDOWN
    document.querySelectorAll("[data-dropdown-button]").forEach(button => {
        button.addEventListener("click", function (e) {
            e.stopPropagation();
            const dropdown = this.closest(".dropdown");
            const isActive = dropdown.classList.toggle("active");
            this.setAttribute("aria-expanded", isActive);
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (!e.target.closest(".dropdown")) {
            document.querySelectorAll(".dropdown").forEach(d => d.classList.remove("active"));
        }
    });

    // HAMBURGER
    const hamburger = document.querySelector(".hamburger");
    const nav = document.querySelector(".main-nav");

    if (hamburger && nav) {
        hamburger.addEventListener("click", function () {
            this.classList.toggle("active");
            nav.classList.toggle("active");
        });

        // Close menu when clicking a link
        document.querySelectorAll(".main-nav a").forEach(link => {
            link.addEventListener("click", () => {
                hamburger.classList.remove("active");
                nav.classList.remove("active");
            });
        });
    }
});