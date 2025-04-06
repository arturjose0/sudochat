const form = document.querySelector("form");
const eField = form.querySelector(".email");
const eInput = eField.querySelector("input");
const pField = form.querySelector(".password");
const pInput = pField.querySelector("input");

form.onsubmit = (e) => {
  // Removido o e.preventDefault() para permitir o envio padrão do formulário

  // Verifica os campos e adiciona classes de erro ou sucesso antes do envio
  if (eInput.value === "") {
    eField.classList.add("shake", "error");
  } else {
    checkEmail();
  }

  if (pInput.value === "") {
    pField.classList.add("shake", "error");
  } else {
    checkPass();
  }

  // Remove a classe "shake" após 500ms
  setTimeout(() => {
    eField.classList.remove("shake");
    pField.classList.remove("shake");
  }, 500);

  // Funções de validação em tempo real com keyup
  eInput.onkeyup = () => {
    checkEmail();
  };
  pInput.onkeyup = () => {
    checkPass();
  };

  function checkEmail() {
    let pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/; // Padrão para validar email
    if (!eInput.value.match(pattern)) {
      eField.classList.add("error");
      eField.classList.remove("valid");
      let errorTxt = eField.querySelector(".error-txt");
      errorTxt.innerText =
        eInput.value !== ""
          ? "O formato de email está incorreto"
          : "Digite o email";
    } else {
      eField.classList.remove("error");
      eField.classList.add("valid");
    }
  }

  function checkPass() {
    if (pInput.value === "") {
      pField.classList.add("error");
      pField.classList.remove("valid");
    } else {
      pField.classList.remove("error");
      pField.classList.add("valid");
    }
  }

  // O envio acontece naturalmente, então removemos a redireção manual
  // window.location.href foi removido, pois o formulário será enviado ao action via POST
};