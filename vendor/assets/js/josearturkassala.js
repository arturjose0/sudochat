var RECEPTOR = 0;
var LOGADO = 1;

let ultimoId = -1;
let LASTID = 0;
let tocar = false;
let page = 0;
let userActivo = null;

let tipoA = 1;

// Array global para armazenar os dados dos usuários
let listaUsuarios = [];

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

function normalizarMeno() {
   if (document.getElementById("conversasFoco")) {
      document.getElementById("conversasFoco").classList.add("esconder");
      document.getElementById("conversasDesfoco").classList.remove("esconder");

      document.getElementById("gruposFoco").classList.add("esconder");
      document.getElementById("gruposDesfoco").classList.remove("esconder");

      document.getElementById("UtilizadoresFoco").classList.add("esconder");
      document.getElementById("UtilizadoresDesfoco").classList.remove("esconder");
   }
}

// Função para remover a classe 'activo' de todos os elementos com classe 'mensagem'
function removerClasseActivo() {
   const mensagens = document.querySelectorAll('.mensagem');
   mensagens.forEach(mensagem => {
      mensagem.classList.remove('activo');
   });
}

function carregarListaUsuarios(tipo = 1) {
   const container = document.querySelector(".baixo");
   if (!container) return; // Sai se o container não existir

   fetch("vendor/backend/sudomake.php?usuarios=" + tipo)
      .then(response => response.json())
      .then(data => {
         // Normaliza o menu (mantido como estava)
         normalizarMeno();

         // Atualiza a visibilidade dos elementos com base no tipo
         switch (tipo) {
            case 3:
               document.getElementById("UtilizadoresDesfoco").classList.add("esconder");
               document.getElementById("UtilizadoresFoco").classList.remove("esconder");
               break;
            case 2:
               document.getElementById("gruposDesfoco").classList.add("esconder");
               document.getElementById("gruposFoco").classList.remove("esconder");
               break;
            default:
               document.getElementById("conversasDesfoco").classList.add("esconder");
               document.getElementById("conversasFoco").classList.remove("esconder");
         }

         // Compara os dados novos com os anteriores
         if (JSON.stringify(listaUsuarios) !== JSON.stringify(data)) {
            // Atualiza o array global apenas se houver mudanças
            listaUsuarios = data;

            // Limpa o container e renderiza os novos dados
            container.innerHTML = "";
            renderizarUsuarios(container, tipo);
            if (tipoA == tipo) {
               if (userActivo > 0) {
                  if (elemento = document.getElementById("u_" + userActivo)) {
                     if (!elemento.classList.contains("activo")) {
                        elemento.classList.add("activo"); // Usa 'this' para referenciar o <a> clicado
                     }
                  }
               }
            } else {
               tipoA = tipo;
            }
         } else {
            // console.log("Nenhuma mudança detectada nos dados.");
         }
      })
      .catch(error => mostrarAlert(error));
}

// Função para renderizar os usuários no DOM
function renderizarUsuarios(container, tipo) {
   listaUsuarios.forEach(user => {
      const nomeFormatado = formatarNome(user.USER_ATTB_NOME, 18);

      const userElement = document.createElement("a");
      userElement.href = user.USER_ATTB_ID;
      userElement.id = "u_" + user.USER_ATTB_ID;
      userElement.classList.add("mensagem");

      let estado = "<b style='color: " + (user.sessao ? "#34C759" : "#FF9800") + "; font-size: 22px'>•</b>";
      let dataAtual = "<b style='color: green; font-size: 20px'>•</b>";
      if (tipo === 1 || tipo === 2) {
         const [data, hora] = user.MENSAGENS_ATTB_CRIADO_AOS.split(" ");
         const agora = new Date();
         const dataAtualHoje = agora.toISOString().split("T")[0];
         dataAtual = dataAtualHoje !== data ? data : hora;

      }
      if (tipo === 1) {
         userElement.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <div class="dois" title=${user.USER_ATTB_NOME}>
                <div class="nome">${formatarNome(user.USER_ATTB_NOME, 18)}</div>
                <p style='display: flex; align-items: center'>${estado} ${formatarNome(user.MENSAGENS_ATTB_MSG, 22)}</p>
            </div>
            <span>${dataAtual}</span>
        `;
      } else if (tipo == 2) {
         userElement.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <div class="dois" title=${user.USER_ATTB_NOME}>
                <div class="nome">${formatarNome(user.USER_ATTB_NOME, 18)}</div>
                <p style='display: flex; align-items: center'><b>${formatarNome(user.sessao, 7)}</b>&nbsp; ${formatarNome(user.MENSAGENS_ATTB_MSG, 13)}</p>
            </div>
            <span>${dataAtual}</span>
        `;
      }
      else {
         userElement.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <div class="dois" title=${user.USER_ATTB_NOME}>
                <div class="nome">${nomeFormatado}</div>
            </div>
            <span style='display: flex'>${estado}</span>
        `;
      }

      userElement.addEventListener("click", function (event) {
         event.preventDefault();
         JanelaDeMensagens(user.USER_ATTB_ID);
         removerClasseActivo();
         userActivo = user.USER_ATTB_ID;
         this.classList.add("activo");
      });

      container.appendChild(userElement);
   });
}

function pesquisarUsuario() {
   const pesquisar = document.getElementById("pesquisar");
   const container = document.querySelector(".baixo");

   if (pesquisar && container) {
      pesquisar.addEventListener("input", () => {
         let resultados;
         const termoPesquisa = pesquisar.value.trim().toLowerCase();

         if (termoPesquisa === "") {
            resultados = listaUsuarios; // Mostra todos os usuários se o campo estiver vazio
         } else {
            resultados = listaUsuarios.filter(user => {
               // Normaliza o nome do usuário, removendo acentos
               const nomeNormalizado = user.USER_ATTB_NOME
                  .normalize("NFD") // Decompõe em caracteres base + diacríticos
                  .replace(/[\u0300-\u036f]/g, "") // Remove os diacríticos
                  .toLowerCase();
               // Normaliza o termo de pesquisa, removendo acentos
               const termoNormalizado = termoPesquisa
                  .normalize("NFD")
                  .replace(/[\u0300-\u036f]/g, "");
               return nomeNormalizado.includes(termoNormalizado);
            });
         }

         console.log("Usuários encontrados:", resultados);

         // Limpa e renderiza os resultados
         container.innerHTML = "";
         renderizarUsuariosFiltrados(container, resultados);
         if (userActivo > 0) {
            if (elemento = document.getElementById("u_" + userActivo)) {
               if (!elemento.classList.contains("activo")) {
                  elemento.classList.add("activo"); // Usa 'this' para referenciar o <a> clicado
               }
            }
         }
      });
   } else {
      console.error("Elemento #pesquisar ou .baixo não encontrado.");
   }
}

// Função para renderizar os usuários filtrados
function renderizarUsuariosFiltrados(container, usuarios, tipo = tipoA) {
   usuarios.forEach(user => {
      const nomeFormatado = formatarNome(user.USER_ATTB_NOME, 18);

      const userElement = document.createElement("a");
      userElement.href = user.USER_ATTB_ID;
      userElement.id = "u_" + user.USER_ATTB_ID;
      userElement.classList.add("mensagem");

      let estado = "<b style='color: " + (user.sessao ? "#34C759" : "#FF9800") + "; font-size: 22px'>•</b>";

      let dataAtual = "•";
      if (Number(tipo) === 1 && user.MENSAGENS_ATTB_CRIADO_AOS) {
         const [data, hora] = user.MENSAGENS_ATTB_CRIADO_AOS.split(" ");
         const agora = new Date();
         const dataAtualHoje = agora.toISOString().split("T")[0];
         dataAtual = dataAtualHoje !== data ? data : hora;

         userElement.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <div class="dois" title=${user.USER_ATTB_NOME}>
                <div class="nome">${nomeFormatado}</div>
                <p>${estado} ${formatarNome(user.MENSAGENS_ATTB_MSG, 22)}</p>
            </div>
            <span>${dataAtual}</span>
        `;
      } else if (Number(tipo) === 2) {
         userElement.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <div class="dois" title=${user.USER_ATTB_NOME}>
                <div class="nome">${formatarNome(user.USER_ATTB_NOME, 18)}</div>
                <p style='display: flex; align-items: center'>${formatarNome(user.sessao, 10)} ${formatarNome(user.MENSAGENS_ATTB_MSG, 22)}</p>
            </div>
            <span>${user.MENSAGENS_ATTB_CRIADO_AOS}</span>
        `;
      }
      else {

         userElement.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <div class="dois" title=${user.USER_ATTB_NOME}>
                <div class="nome">${nomeFormatado}</div>
            </div>
            <span>${estado}</span>
        `;
      }

      userElement.addEventListener("click", function (event) {
         event.preventDefault();
         JanelaDeMensagens(user.USER_ATTB_ID);
         removerClasseActivo();
         userActivo = user.USER_ATTB_ID;
         this.classList.add("activo");
      });

      container.appendChild(userElement);
   });
}

// Chama a função para inicializar o evento
pesquisarUsuario();

carregarListaUsuarios();

function JanelaDeMensagens(id) {
   fetch("vendor/backend/sudomake.php", {
      method: "POST",
      headers: {
         "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}&tipo=${tipoA}`
   })
      .then(response => response.text())
      .then(html => {
         document.getElementById("mensagens").innerHTML = html;
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
   return nome ? (nome.length > tamanho ? nome.substring(0, tamanho - 3) + "..." : nome) : "";
}

function carregarMensagens(para) {
   if (document.getElementById("conteudo")) {
      fetch("vendor/backend/sudomake.php", {
         method: "POST",
         headers: {
            "Content-Type": "application/x-www-form-urlencoded",
         },
         body: `para=${encodeURIComponent(para)}&ultimo_id=${encodeURIComponent(ultimoId)}&tipo=${tipoA}`
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
          <div class="datahora" title='data e hora de envio'><b>${tipoA == 2 ? msg.USER_ATTB_NOME : ''}</b></div>
          <div class="msg">
          ${msg.MENSAGENS_ATTB_MSG}
          </div>
          <div class="datahora" title='data e hora de envio'>${msg.MENSAGENS_ATTB_CRIADO_AOS}</div>
        </div>
        </div>
      </div>
                        `;

               }

               conteudo.appendChild(div);

               ultimoId = Number(msg.MENSAGENS_ATTB_ID); // Atualiza último ID

               document.getElementById("conteudo").scrollTop = conteudo.scrollHeight;
            });
         })
         .catch(error => {
            mostrarAlert("Erro ao carregar mensagens: " + error)
            console.error("Erro ao carregar mensagens:", error)
         });
   }
}

// Função síncrona para obter o último ID de mensagem de um usuário
function ultimoID_do(para) {
   try {
      // Verifica se o elemento com id "conteudo" existe
      if (document.getElementById("conteudo")) {
         const xhr = new XMLHttpRequest();
         xhr.open("POST", "vendor/backend/sudomake.php", false); // false = síncrono
         xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
         xhr.send(`ultimoID_do=${encodeURIComponent(para)}&&tipo=${tipoA}`);

         if (xhr.status === 200) {
            const resp = JSON.parse(xhr.responseText);
            if (resp.status === "success") {
               return Number(resp.ultimoID); // Retorna o último ID como número
            } else {
               // mostrarAlert("Nenhum ID encontrado ou erro na resposta: " + (resp.msg || "Sem mensagem"))
               console.log("Nenhum ID encontrado ou erro na resposta:", resp.msg || "Sem mensagem");
               return 0;
            }
         } else {
            mostrarAlert("Erro na requisição: " + xhr.status)
            // throw new Error("Erro na requisição: " + xhr.status);
         }
      } else {
         mostrarAlert("Elemento 'conteudo' não encontrado")
         // console.log("Elemento 'conteudo' não encontrado");
         return 0;
      }
   } catch (error) {
      mostrarAlert("Erro ao fazer a requisição: " + error)
      // console.log("Erro ao fazer a requisição: " + error);
      return 0;
   }
}

// setInterval(() => carregarMensagens(1, 2), 5000);
setInterval(() => {
   carregarMensagens(RECEPTOR);
   // carregarListaUsuarios();
   carregarListaUsuarios(tipoA); // Verifica tipo 1 (conversas) como padrão
}, 1000);

function tocarSom() {
   const som = new Audio('vendor/assets/musics/notification.mp3'); // Caminho para o ficheiro de áudio
   som.play().catch(function (error) {
      console.error("Erro ao tentar reproduzir o som:", error);
   });
}


// Função para consultar o status da sessão
function verificarSessao() {
   if (document.getElementById("mensagens")) {
      uuu = "vendor/backend/sudomake.php?isLoggedIn=true";
   } else {
      uuu = "../vendor/backend/sudomake.php?isLoggedIn=true"
   }
   fetch(uuu, {
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
            location.href = "login";
         } else if (data.status === "success" && content) {
            LOGADO = data.SUDOCHAT_SESSAO_ID;
            if (nnome = document.getElementById("logado")) {
               nnome.innerText = pegarIniciais(data.SUDOCHAT_SESSAO_NOME);
               nnome.title = data.SUDOCHAT_SESSAO_NOME;
            }
         }
      })
      .catch(error => {
         mostrarAlert("Erro ao verificar sessão: " + error, "error", 10000);
      });
}

// Executa a verificação imediatamente e depois a cada 5 segundos
verificarSessao(); // Primeira execução
setInterval(verificarSessao, 10000); // Repete a cada 5 segundos (5000ms)


// Função para terminar a sessão
function terminarSessao() {
   fetch("vendor/backend/sudomake.php", {
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

function pegarIniciais(nome) {
   // Divide o nome em palavras, filtra palavras vazias e pega a primeira letra de cada uma
   const iniciais = nome
      .split(" ") // Divide por espaços
      .filter(palavra => palavra.length > 0) // Remove espaços extras
      .map(palavra => palavra.charAt(0).toUpperCase()) // Pega a primeira letra e capitaliza
      .join(""); // Junta as letras
   return iniciais;
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

function mostrarAlert(message, tipo = "error", duration = 3000) {
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
               mostrarAlert(data['msg']);
            else {
               form.reset();
               if (document.getElementById("anexos"))
                  desvincularAnexos();
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
            mostrarAlert(error);
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

//-----modal

// Arrays para armazenar membros e administradores
let membrosAdicionados = [];
let adminsAdicionados = [];

function abrirModalCriarGrupo() {
   const modal = document.getElementById("modalCriarGrupo");
   modal.style.display = "flex";
   buscarMembros();
}

function fecharModalCriarGrupo() {
   const modal = document.getElementById("modalCriarGrupo");
   modal.style.display = "none";
   document.getElementById("nomeGrupo").value = "";
   document.getElementById("buscaMembros").value = "";
   membrosAdicionados = [];
   adminsAdicionados = [];
   buscarMembros();
}

function buscarMembros() {
   const termo = document.getElementById("buscaMembros").value.toLowerCase();
   const listaMembros = document.getElementById("listaMembros");

   const usuariosFiltrados = listaUsuarios.filter(user => {
      const nomeNormalizado = user.USER_ATTB_NOME
         .normalize("NFD")
         .replace(/[\u0300-\u036f]/g, "")
         .toLowerCase();
      return nomeNormalizado.includes(termo);
   });

   listaMembros.innerHTML = "";

   usuariosFiltrados.forEach(user => {
      const item = document.createElement("div");
      item.classList.add("usuario-item");

      const estaAdicionado = membrosAdicionados.includes(user.USER_ATTB_ID);
      const ehAdmin = adminsAdicionados.includes(user.USER_ATTB_ID);

      item.innerHTML = `
            <img src="https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671142.jpg?w=740" alt="Avatar">
            <span>${user.USER_ATTB_NOME}</span>
            <div class="admin-checkbox" style="${estaAdicionado ? '' : 'display: none;'}">
                <input type="checkbox" id="admin_${user.USER_ATTB_ID}" ${ehAdmin ? 'checked' : ''} onchange="toggleAdmin(${user.USER_ATTB_ID}, this)">
                <label for="admin_${user.USER_ATTB_ID}">Admin</label>
            </div>
            <button class="${estaAdicionado ? 'btn-added' : 'btn-add'}" onclick="toggleMembro(${user.USER_ATTB_ID}, this)">
                ${estaAdicionado ? '✓ Adicionado' : '+ Add'}
            </button>
        `;
      listaMembros.appendChild(item);
   });
}

function toggleMembro(userId, botao) {
   const index = membrosAdicionados.indexOf(userId);
   const adminCheckbox = botao.parentElement.querySelector(".admin-checkbox");

   if (index === -1) {
      // Adiciona o membro
      membrosAdicionados.push(userId);
      botao.classList.remove("btn-add");
      botao.classList.add("btn-added");
      botao.innerHTML = "✓ Adicionado";
      adminCheckbox.style.display = "block";
   } else {
      // Remove o membro e o admin (se aplicável)
      membrosAdicionados.splice(index, 1);
      const adminIndex = adminsAdicionados.indexOf(userId);
      if (adminIndex !== -1) {
         adminsAdicionados.splice(adminIndex, 1);
      }
      botao.classList.remove("btn-added");
      botao.classList.add("btn-add");
      botao.innerHTML = "+ Add";
      adminCheckbox.style.display = "none";
   }
}

function toggleAdmin(userId, checkbox) {
   const index = adminsAdicionados.indexOf(userId);
   if (checkbox.checked) {
      if (index === -1) {
         adminsAdicionados.push(userId);
      }
   } else {
      if (index !== -1) {
         adminsAdicionados.splice(index, 1);
      }
   }
}

function criarGrupo() {
   const nomeGrupo = document.getElementById("nomeGrupo").value.trim();
   if (!nomeGrupo) {
      // alert("Por favor, insira o nome do grupo.");
      mostrarAlert("Por favor, insira o nome do grupo.", "warning");

      return;
   }
   if (membrosAdicionados.length === 0) {
      mostrarAlert("Por favor, adicione pelo menos um membro ao grupo.", "warning");
      return;
   }

   fetch("vendor/backend/sudomake.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
         acao: "criarGrupo",
         nome: nomeGrupo,
         membros: membrosAdicionados,
         administradores: adminsAdicionados
      })
   })
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            mostrarAlert("Grupo criado com sucesso!", "success");
            fecharModalCriarGrupo();
            carregarListaUsuarios(2);
         } else {
            mostrarAlert("Erro ao criar grupo: " + data.message);
         }
      })
      .catch(error => console.error("Erro ao criar grupo:", error));
}


function mudancaAnexos() {
   const inputAnexos = document.getElementById("anexos");
   const arquivosEnviar = document.getElementById("arquivosEnviar");
   const contagemAnexos = document.getElementById("contagemAnexos");
   const quantidadeAnexos = inputAnexos.files.length;

   if (quantidadeAnexos > 0) {
      // Mostra o .arquivosEnviar se houver anexos
      arquivosEnviar.style.display = "flex";
      // Atualiza a contagem de anexos
      contagemAnexos.textContent = `(${quantidadeAnexos}) Carregados`;
   } else {
      // Esconde o .arquivosEnviar se não houver anexos
      arquivosEnviar.style.display = "none";
   }
}

// Função para desvincular todos os anexos
function desvincularAnexos() {
   const inputAnexos = document.getElementById("anexos");
   const arquivosEnviar = document.getElementById("arquivosEnviar");
   const contagemAnexos = document.getElementById("contagemAnexos");

   // Limpa o input de arquivos
   inputAnexos.value = "";
   // Esconde o .arquivosEnviar
   arquivosEnviar.style.display = "none";
   // Reseta a contagem de anexos
   contagemAnexos.textContent = "(0) Carregados";
}