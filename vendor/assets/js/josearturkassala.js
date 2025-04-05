var RECEPTOR = 0;
var LOGADO = 1;

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
   const container = document.querySelector(".baixo");
   const mensagensContainer = document.getElementById("mensagens");

   function carregarUsuarios() {
      fetch("vendor/backend/requisicoes.php")
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
                  carregarMensagens(user.USER_ATTB_ID);
               });

               container.appendChild(userElement);
            });
         })
         .catch(error => console.error("Erro ao buscar usuários:", error));
   }

   function carregarMensagens(id) {
      fetch("vendor/backend/requisicoes.php", {
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
            ultimoId = 0;
            tocar = false;

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

});


let ultimoId = 0;
let tocar = false;

function carregarMensagens(de, para) {
   if (document.getElementById("conteudo")) {
      fetch("vendor/backend/requisicoes.php", {
         method: "POST",
         headers: {
            "Content-Type": "application/x-www-form-urlencoded",
         },
         body: `de=${encodeURIComponent(de)}&para=${encodeURIComponent(para)}&ultimo_id=${encodeURIComponent(ultimoId)}`
      })
         .then(response => response.json())
         .then(mensagens => {
            const conteudo = document.getElementById("conteudo");

            mensagens.forEach(msg => {
               const div = document.createElement("div");
               div.classList.add("caixa");
               if (msg.MENSAGENS_ATTB_DE === LOGADO) {
                  div.innerHTML = `

                        <div class="caixa">
       <div class="emissor">
        <div class="texto">
          <div class="datahora">enviado aos ${msg.MENSAGENS_ATTB_CRIADO_AOS}</div>
          <div class="msg">
          ${msg.MENSAGENS_ATTB_MSG}
          </div>
        </div>
        </div>
      </div>
                    `;
               } else {
                  div.innerHTML = `
                        <div class="caixa">
       <div class="receptor">
        <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?t=st=1743555705~exp=1743559305~hmac=fb04ae28a8512fe8af76eb993e6dea68c3de7e38691ed699887af3a51666ca34&w=740" alt="">
        <div class="texto">
          <div class="datahora">01:02:11 dia 31/07/2025</div>
          <div class="msg">
            ${msg.MENSAGENS_ATTB_MSG}
          </div>
        </div>
        </div>
      </div>
                        `;

               }
               tocar = true;
               conteudo.appendChild(div);

               ultimoId = msg.MENSAGENS_ATTB_ID; // Atualiza último ID

               document.getElementById("conteudo").scrollTop = conteudo.scrollHeight;
            });
         })
         .catch(error => console.error("Erro ao carregar mensagens:", error));
   }
}


// setInterval(() => carregarMensagens(1, 2), 5000);
setInterval(() => {
   carregarMensagens(1, RECEPTOR);
   if (tocar) {
      tocarSom();
      tocar = false; // Define tocar como falso após tocar o som
   }
}, 1000);

function tocarSom() {
   const som = new Audio('vendor/assets/musics/notification.mp3'); // Caminho para o ficheiro de áudio
   som.play().catch(function (error) {
      console.error("Erro ao tentar reproduzir o som:", error);
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
`;

document.head.appendChild(document.createElement('style')).innerText = myCSS;

// Criação do elemento div
// const alertDiv = document.createElement("div");

// alertDiv.role = "alert";

function capturarIDdoFormulario(event) {
   // Evita o comportamento padrão de envio do formulário

   form = document.getElementById(event.target.id);
   const btn_submit = form.querySelector('button[type="submit"]');
   if (btn_submit != null) {
      event.preventDefault();
      btn_submit.disabled = true;
      old = btn_submit.innerHTML;
      btn_submit.innerHTML = '<div class="load"><div class="spinner"></div> Processando...</div>';


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
               alert(data['msg']);
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
            alert("Houve um erro ao processar a sua solicitação. Tente novamente mais tarde.");
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

   document.getElementById(elemento).innerHTML = '<div class="load"><div class="spinner"></div> Processando...</div>';

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

   document.getElementById(elemento).innerHTML = '<div class="load"><div class="spinner"></div> Processando...</div>';

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
