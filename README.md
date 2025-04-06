# SudoChat

## Descrição

este é um chat simples com a possibilidade fácil de integração .
O SudoChat é um sistema de chat em tempo real que permite a comunicação entre usuários de forma segura e eficiente. Ele foi desenvolvido com foco na privacidade e na segurança, utilizando criptografia para proteger as mensagens e filtros rigorosos para evitar a execução de códigos maliciosos.

## Funcionalidades

* **Comunicação em tempo real:** Permite a troca de mensagens instantâneas entre usuários.
* **Criptografia de mensagens:** Todas as mensagens são criptografadas antes de serem armazenadas no banco de dados, garantindo a privacidade dos usuários.
* **Filtro de segurança:** Implementa um filtro robusto para evitar a exibição e execução de códigos HTML e PHP potencialmente perigosos, permitindo apenas tags de estilo básicas.
* **Recuperação do último ID:** Permite recuperar o último ID de mensagem para otimizar a busca por novas mensagens.
* **Interface amigável:** Interface intuitiva e fácil de usar, proporcionando uma experiência agradável para os usuários.

## Tecnologias Utilizadas

* **PHP:** Linguagem de programação do lado do servidor.
* **JavaScript:** Linguagem de programação do lado do cliente.
* **MySQL/MariaDB:** Banco de dados para armazenamento das mensagens e informações dos usuários.
* **HTML/CSS:** Para a estrutura e estilo da interface do usuário.
* **API Fetch:** Para realizar requisições assíncronas no javascript.
* **PDO:** Para realizar a conexão com o banco de dados de forma segura.

## Requisitos

* Servidor web com suporte a PHP.
* Banco de dados MySQL/MariaDB.
* Navegador web moderno com suporte a JavaScript.

## Instalação

1.  Clone o repositório para o seu servidor web.
2.  Crie um banco de dados MySQL/MariaDB e importe o arquivo `database.sql` (se houver) para criar as tabelas necessárias.
3.  Configure as credenciais do banco de dados no arquivo `config.php`.
4.  Abra o seu navegador web e acesse o diretório onde o projeto foi instalado.

## Contribuição

Contribuições são bem-vindas! Se você encontrar algum problema ou tiver alguma sugestão de melhoria, por favor, abra uma issue ou envie um pull request.

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE) (se aplicável).