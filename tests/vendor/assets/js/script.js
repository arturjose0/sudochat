if (menu_btn = document.getElementById("menu-acao")) {
    menu_btn.addEventListener('click', () => {

        if (principal = document.getElementById("principal")) {
            if (principal.classList.contains("principal")) {
                principal.classList.remove("principal");
                principal.classList.add("principal-hide");
            } else {
                principal.classList.remove("principal-hide");
                principal.classList.add("principal");
            }
        }
    });
}
