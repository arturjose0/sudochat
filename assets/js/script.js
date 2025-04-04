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
        fetch("backends/users.php")
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
        fetch("backends/mensagem.php", {
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
        fetch("backends/obter_mensagens.php", {
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
          <div class="datahora">${ultimoId} 01:02:11 dia 31/07/2025</div>
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
    const som = new Audio('../../vendor/musicas/notification.mp3'); // Caminho para o ficheiro de áudio
    som.play().catch(function (error) {
        console.error("Erro ao tentar reproduzir o som:", error);
    });
}

