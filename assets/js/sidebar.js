const menuToggles = document.querySelectorAll(".menu-toggle");

menuToggles.forEach((toggle) => {
    toggle.addEventListener("click", () => {
        const group = toggle.parentElement;
        group.classList.toggle("open");
    });
});