var RECEPTOR = 0;
var LOGADO = 1;

let ultimoId = -1;
let LASTID = 0;
let tocar = false;


function abrirmenumsg() {
   let elemento = document.querySelector(".recursos_conter") || document.querySelector(".recursos");

   if (elemento) {
      if (elemento.classList.contains("recursos_conter")) {
         elemento.classList.replace("recursos_conter", "recursos");
      } else {
         elemento.classList.replace("recursos", "recursos_conter");
      }
   }
}

document.addEventListener("DOMContentLoaded", function () {
   if (container = document.querySelector(".baixo")) {
      const mensagensContainer = document.getElementById("mensagens");

      function carregarUsuarios() {
         fetch("/vendor/backend/sudomake.php?usuarios")
            .then(response => response.json())
            .then(data => {
               container.innerHTML = ""; // Limpa o conteúdo anterior

               data.forEach(user => {
                  const nomeFormatado = formatarNome(user.USER_ATTB_NOME, 13);

                  const userElement = document.createElement("a");
                  userElement.href = user.USER_ATTB_ID;
                  userElement.classList.add("mensagem");
                  userElement.innerHTML = `
                        <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
                        <div class="dois">
                            <div class="nome">${nomeFormatado}</div>
                            <p>Escrevendo...</p>
                        </div>
                        <span>${new Date().toLocaleTimeString()}</span>
                    `;

                  userElement.addEventListener("click", function (event) {
                     event.preventDefault();
                     JanelaDeMensagens(user.USER_ATTB_ID);
                  });

                  container.appendChild(userElement);
               });
            })
            .catch(error => console.error("Erro ao buscar usuários:", error));
      }

      function JanelaDeMensagens(id) {
         fetch("/vendor/backend/sudomake.php", {
            method: "POST",
            headers: {
               "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id=${encodeURIComponent(id)}`
         })
            .then(response => response.text())
            .then(html => {
               mensagensContainer.innerHTML = html;
               RECEPTOR = id;
               tocar = false;
               ultimoId = -1;
               LASTID = 0;
               carregarMensagens(id);
               LASTID = ultimoID_do(Number(id));

               // alert("ID do formulário enviado:" + LASTID);
               ultimoId = Number(LASTID);
               tocar = true;

               // alert(ultimoId);
               // Adiciona um ouvinte de evento (event listener) para cada formulário na página
               const forms = document.getElementsByTagName("form");
               for (let i = 0; i < forms.length; i++) {
                  // alert("ID do formulário enviado:"+forms[i].id);

                  forms[i].addEventListener("submit", capturarIDdoFormulario);
               }
            })
            .catch(error => console.error("Erro ao carregar mensagens:", error));
      }


      function formatarNome(nome, tamanho) {
         return nome.length > tamanho ? nome.substring(0, tamanho) + "..." : nome;
      }

      carregarUsuarios();
   }
});


function carregarMensagens(para) {
   if (document.getElementById("conteudo")) {
      fetch("/vendor/backend/sudomake.php", {
         method: "POST",
         headers: {
            "Content-Type": "application/x-www-form-urlencoded",
         },
         body: `para=${encodeURIComponent(para)}&ultimo_id=${encodeURIComponent(ultimoId)}`
      })
         .then(response => response.json())
         .then(mensagens => {
            const conteudo = document.getElementById("conteudo");

            mensagens.forEach(msg => {
               const div = document.createElement("div");
               div.classList.add("caixa");
               if (msg.MENSAGENS_ATTB_DE == Number(LOGADO)) {

                  div.innerHTML = `

                        <div class="caixa">
       <div class="emissor">
        <div class="texto">
          <div class="datahora" title='data e hora de envio'>${msg.MENSAGENS_ATTB_CRIADO_AOS}</div>
          <div class="msg">
          ${msg.MENSAGENS_ATTB_MSG}
          </div>
        </div>
        </div>
      </div>
                    `;
               } else {
                  // alert(msg.MENSAGENS_ATTB_DE);
                  // alert(LOGADO);
                  div.innerHTML = `
                        <div class="caixa">
       <div class="receptor">
        <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?t=st=1743555705~exp=1743559305~hmac=fb04ae28a8512fe8af76eb993e6dea68c3de7e38691ed699887af3a51666ca34&w=740" alt="">
        <div class="texto">
          <div class="datahora" title='data e hora de envio'>${msg.MENSAGENS_ATTB_CRIADO_AOS}</div>
          <div class="msg">
            ${msg.MENSAGENS_ATTB_MSG}
          </div>
        </div>
        </div>
      </div>
                        `;

                  if (ultimoId >= LASTID && tocar) {
                     // alert(ultimoId + " > " + LASTID);
                     tocarSom();
                     // tocar = false; // Define tocar como falso após tocar o som
                  }

               }

               conteudo.appendChild(div);

               ultimoId = Number(msg.MENSAGENS_ATTB_ID); // Atualiza último ID

               document.getElementById("conteudo").scrollTop = conteudo.scrollHeight;
            });
         })
         .catch(error => console.error("Erro ao carregar mensagens:", error));
   }
}

// Função síncrona para obter o último ID de mensagem de um usuário
function ultimoID_do(para) {
   try {
      // Verifica se o elemento com id "conteudo" existe
      if (document.getElementById("conteudo")) {
         const xhr = new XMLHttpRequest();
         xhr.open("POST", "/vendor/backend/sudomake.php", false); // false = síncrono
         xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
         xhr.send(`ultimoID_do=${encodeURIComponent(para)}`);

         if (xhr.status === 200) {
            const resp = JSON.parse(xhr.responseText);
            if (resp.status === "success") {
               return Number(resp.ultimoID); // Retorna o último ID como número
            } else {
               console.log("Nenhum ID encontrado ou erro na resposta:", resp.msg || "Sem mensagem");
               return 0;
            }
         } else {
            throw new Error("Erro na requisição: " + xhr.status);
         }
      } else {
         console.log("Elemento 'conteudo' não encontrado");
         return 0;
      }
   } catch (error) {
      console.log("Erro ao fazer a requisição: " + error);
      return 0;
   }
}

// setInterval(() => carregarMensagens(1, 2), 5000);
setInterval(() => {
   carregarMensagens(RECEPTOR);

}, 1000);

function tocarSom() {
   const som = new Audio('/vendor/assets/musics/notification.mp3'); // Caminho para o ficheiro de áudio
   som.play().catch(function (error) {
      console.error("Erro ao tentar reproduzir o som:", error);
   });
}


// Função para consultar o status da sessão
function verificarSessao() {
   fetch("/vendor/backend/sudomake.php?isLoggedIn=true", {
      method: "GET",
      headers: {
         "Content-Type": "application/json"
      }
   })
      .then(response => {
         if (!response.ok) {
            throw new Error("Erro na requisição: " + response.status);
         }
         return response.json();
      })
      .then(data => {
         // Exibe o resultado no console ou na página
         // console.log("Resposta do servidor:", data);

         // Exemplo: Atualiza a UI com os dados recebidos
         // const resultadoDiv = document.getElementById("resultado");
         const content = document.getElementById("ferramentas");
         if (data.status === "success" && !content) {
            location.href = "../";
         } else if (data.status != "success" && content) {
            location.href = "/login";
         } else if (data.status === "success" && content) {
            LOGADO = data.SUDOCHAT_SESSAO_ID;
         }
      })
      .catch(error => {
         showAlert("Erro ao verificar sessão: " + error, "error", 10000);
      });
}

// Executa a verificação imediatamente e depois a cada 5 segundos
verificarSessao(); // Primeira execução
setInterval(verificarSessao, 5000); // Repete a cada 5 segundos (5000ms)


// Função para terminar a sessão
function terminarSessao() {
   fetch("/vendor/backend/sudomake.php", {
      method: "POST",
      headers: {
         "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "terminarSessao=true"
   })
      .then(response => response.json())
      .then(data => {
         if (data.status === "success") {
            console.log("Sessão terminada com sucesso");
            location.href = "/login"; // Redireciona para a página de login após logout
         } else {
            console.error("Erro ao terminar sessão:", data.msg || "Sem mensagem");
         }
      })
      .catch(error => {
         console.error("Erro na requisição:", error);
      });
}

if (logout = document.getElementById("terminarSessaoBtn")) {
   // Adiciona o evento de clique ao botão
   logout.addEventListener("click", function (event) {
      event.preventDefault(); // Impede o comportamento padrão do link
      terminarSessao();
   });
}
// ---- ANIMACOES, EFEITOS E MECANICAS DE FORMULARIOS
// Adiciona o CSS para o spinner
const myCSS = `
.spinner {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 3.8px solid #474bff;
    animation: spinner-bulqg1 0.8s infinite linear alternate,
    spinner-oaa3wk 1.6s infinite linear;
 }
 
.load{
    display: flex;
    align-items: center;
    gap:10px;
    justify-content:center;
}

 @keyframes spinner-bulqg1 {
    0% {
       clip-path: polygon(50% 50%, 0 0, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%);
    }
 
    12.5% {
       clip-path: polygon(50% 50%, 0 0, 50% 0%, 100% 0%, 100% 0%, 100% 0%, 100% 0%);
    }
 
    25% {
       clip-path: polygon(50% 50%, 0 0, 50% 0%, 100% 0%, 100% 100%, 100% 100%, 100% 100%);
    }
 
    50% {
       clip-path: polygon(50% 50%, 0 0, 50% 0%, 100% 0%, 100% 100%, 50% 100%, 0% 100%);
    }
 
    62.5% {
       clip-path: polygon(50% 50%, 100% 0, 100% 0%, 100% 0%, 100% 100%, 50% 100%, 0% 100%);
    }
 
    75% {
       clip-path: polygon(50% 50%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 50% 100%, 0% 100%);
    }
 
    100% {
       clip-path: polygon(50% 50%, 50% 100%, 50% 100%, 50% 100%, 50% 100%, 50% 100%, 0% 100%);
    }
 }
 
 @keyframes spinner-oaa3wk {
    0% {
       transform: scaleY(1) rotate(0deg);
    }
 
    49.99% {
       transform: scaleY(1) rotate(135deg);
    }
 
    50% {
       transform: scaleY(-1) rotate(0deg);
    }
 
    100% {
       transform: scaleY(-1) rotate(-135deg);
    }
 }

 /* Container do alerta */
      .alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      /* Alerta */

      .error{
         background-color: #f44336;
      }

      .success{
         background-color: #4caf50;
      }
      .warning{
         background-color: #ff9800;
      }

      .alert {
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        min-width: 250px;
        max-width: 300px;
        position: relative;
        opacity: 0;
        transform: translateX(100%);
        animation: slideIn 0.4s forwards;
      }

      /* Barra de progresso */
      .progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        background-color: #fff;
        width: 100%;
        animation: shrink linear forwards;
      }

      /* Animação do alerta entrando */
      @keyframes slideIn {
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }

      /* Animação da barra de progresso encolhendo */
      @keyframes shrink {
        from {
          width: 100%;
        }
        to {
          width: 0%;
        }
      }
`;

document.head.appendChild(document.createElement('style')).innerText = myCSS;

// Criação do elemento div
const alertDiv = document.createElement("div");

alertDiv.classList.add("alert-container");

alertDiv.id = "alertContainer";
// Adiciona o elemento div ao body
document.body.appendChild(alertDiv);

function showAlert(message, tipo = "error", duration = 3000) {
   const container = document.getElementById("alertContainer");

   const alert = document.createElement("div");
   alert.classList.add("alert");
   alert.classList.add(tipo);

   alert.innerText = message;

   // Criar a barra de progresso
   const progress = document.createElement("div");
   progress.classList.add("progress-bar");
   progress.style.animationDuration = duration + "ms";

   alert.appendChild(progress);
   container.appendChild(alert);

   // Remover após o tempo definido
   setTimeout(() => {
      alert.style.opacity = "0";
      alert.style.transform = "translateX(100%)";
      setTimeout(() => container.removeChild(alert), 400); // Espera a animação sair
   }, duration);
}

function capturarIDdoFormulario(event) {
   // Evita o comportamento padrão de envio do formulário

   const form = document.getElementById(event.target.id);
   const btn_submit = form.querySelector('button[type="submit"]');
   if (btn_submit != null) {
      event.preventDefault();

      // alert("ID do formulário enviado:" + form.id);
      btn_submit.disabled = true;
      old = btn_submit.innerHTML;
      btn_submit.innerHTML = '<div class="load"><div class="spinner"></div></div>';


      //pega todos os dados do formulario e coloca na variavel do tipo FormData
      var dados = new FormData(form);
      //faz a requisicao com o fetch
      fetch(form.action, {
         method: form.method,
         body: dados
      })
         .then(res => {
            //em caso de erro
            if (!res.ok) throw new Error(res.status);
            return res.json();
         })
         .then(data => {
            //pegar os parametros necessarios caso for sucesso
            // alertDiv.innerHTML = data['msg'];

            if (data['status'] == "error")
               // alert(data['msg']);
               showAlert(data['msg']);
            else {
               form.reset();
               // alertDiv.className = "alert alert-success alert-dismissible";
               // alertDiv.innerHTML = data['msg'];
               if (form.querySelector('img'))
                  form.querySelector('img').src = "assets/img/avatars/JoseArturKassala.jpg";
               if (data["accao"] == "refresh") {
                  setTimeout(() => {
                     // Código a ser executado após 30 segundos
                     window.location.reload();
                  }, 900);
               }
               if (data["url"]) {
                  window.location.href = data["url"];
               }

            }

            // Adiciona a nova linha à tabela
         })
         .catch((error) => {
            showAlert(error);
            // alert("Houve um erro ao processar a sua solicitação. Tente novamente mais tarde.");
            // alertDiv.innerHTML = error;
            // alertDiv.className = "alert alert-warning alert-dismissible";

         }).finally(() => {
            // alertDiv.innerHTML += "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
            // registarBtn.insertAdjacentElement("afterend", alertDiv);
            // btn_submit.insertAdjacentElement("afterend", alertDiv);
            btn_submit.innerHTML = old;
            btn_submit.disabled = false;
         });
   }
}

// Adiciona um ouvinte de evento (event listener) para cada formulário na página
const forms = document.getElementsByTagName("form");
for (let i = 0; i < forms.length; i++) {
   // alert("ID do formulário enviado:"+forms[i].id);

   forms[i].addEventListener("submit", capturarIDdoFormulario);
}


var oldimg = "";

function sudo_showImage(origem, destino) {
   var imagem = document.querySelector('input[name=' + origem + ']').files[0];
   var preview = document.querySelector('#' + destino);

   var reader = new FileReader();

   reader.onloadend = function () {
      preview.src = reader.result;
   }

   if (imagem) {

      reader.readAsDataURL(imagem);
   } else {
      document.querySelector('#' + destino).src = "assets/img/avatars/JoseArturKassala.jpg";
   }
}

function substituirElemento(elemento, url) {

   document.getElementById(elemento).innerHTML = '<div class="load"><div class="spinner"></div></div>';

   fetch(url, {
      method: 'GET'
   })
      .then(res => {
         //em caso de erro
         if (!res.ok) throw new Error(res.status);
         return res.json();
      })
      .then(data => {

         document.getElementById(elemento).innerHTML = data;

         // Adiciona a nova linha à tabela
      })
      .catch((error) => {
         document.getElementById(elemento).innerHTML = error;
         console.log("erro: ", error);
      }).finally(() => {
         console.log(url);
      });

}

function validar(url, elemento) {

   old = document.getElementById(elemento).innerHTML;

   document.getElementById(elemento).innerHTML = '<div class="load"><div class="spinner"></div></div>';

   fetch(url, {
      method: 'GET'
   })
      .then(res => {
         //em caso de erro
         if (!res.ok) throw new Error(res.status);
         return res.json();
      })
      .then(data => {

         // alert(data["msg"]);
         // swal(data["status"], data["msg"], data["status"]);
         Swal.fire({
            title: "Notificação",
            text: data["msg"],
            icon: data["status"]
         });
         if (data["accao"] == "refresh") {
            setTimeout(() => {
               // Código a ser executado após 30 segundos
               window.location.reload();
            }, 900);
         }
         if (data["url"]) {
            window.location.href = data["url"];
         }
         // Adiciona a nova linha à tabela
      })
      .catch((error) => {
         // alert(url);
         swal('Erro!', error, 'error');
         Swal.fire({
            title: "Erro!",
            text: error,
            icon: 'error'
         });
         console.log("erro: ", error);
      }).finally(() => {
         document.getElementById(elemento).innerHTML = old;
         console.log(url);
      });

}
