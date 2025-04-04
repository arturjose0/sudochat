<?php
$host = "127.0.0.1"; // Ou o IP do servidor
$dbname = "chance"; // Nome do banco criado pelo Laravel
$username = "sa"; // Usuário do banco (verifique no .env do Laravel)
$password = "Goo22oogle#"; // Senha do banco (verifique no .env do Laravel)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}


//definir a tabela utilizador, se nao tens nenhuma criada nao mecher
define("TB_UTILIZADORES", "users");

//definir os atributos da tabela utilizador
define("USER_ATTB_NOME", "name");
define("USER_ATTB_ID", "id");
define("USER_ATTB_EMAIL", "email");
define("USER_ATTB_FOTO", "");


//definir a tabela das mensagens, se nao tens nenhuma criada nao mecher
define("TB_MENSAGENS", "TB_MENSAGENS");

define("MENSAGENS_ATTB_ID", "MENSAGENS_ATTB_ID");
define("MENSAGENS_ATTB_MSG", "MENSAGENS_ATTB_MSG");
define("MENSAGENS_ATTB_ANEXOS", "MENSAGENS_ATTB_ANEXOS");
define("MENSAGENS_ATTB_DE", "MENSAGENS_ATTB_DE");
define("MENSAGENS_ATTB_PARA", "MENSAGENS_ATTB_PARA");





// Criar a tabela se não existir
$sql = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS . " (
    " . MENSAGENS_ATTB_ID . " bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . MENSAGENS_ATTB_MSG . " TEXT,
    " . MENSAGENS_ATTB_ANEXOS . " TEXT,
    " . MENSAGENS_ATTB_DE . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_PARA . " bigint UNSIGNED NOT NULL,
    FOREIGN KEY (" . MENSAGENS_ATTB_DE . ") REFERENCES " . TB_UTILIZADORES . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
    FOREIGN KEY (" . MENSAGENS_ATTB_PARA . ") REFERENCES " . TB_UTILIZADORES . "(" . USER_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";


try {
    $pdo->exec($sql);
    // echo "Tabela verificada/criada com sucesso!";
} catch (PDOException $e) {
    echo <<<HTML
    <script>
    alert("Erros");
    </script>
    HTML;
}
