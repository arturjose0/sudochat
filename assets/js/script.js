document.getElementById("btn-back").addEventListener("click", function () {
    let elemento = document.querySelector(".recursos_conter") || document.querySelector(".recursos");

    if (elemento) {
        if (elemento.classList.contains("recursos_conter")) {
            elemento.classList.replace("recursos_conter", "recursos");
        } else {
            elemento.classList.replace("recursos", "recursos_conter");
        }
    }
});
