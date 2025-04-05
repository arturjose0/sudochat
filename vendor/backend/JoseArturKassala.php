<?php

// Função para carregar as variáveis do .env
function loadEnv($file = '../../.env')
{
    if (!file_exists($file)) {
        throw new Exception("ficheiro .env não encontrado.");
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Ignora comentários
        }

        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Carregar as configurações do .env
loadEnv();


// Definir constantes a partir do .env
define("SRV_HOST", getenv("DB_HOST"));
define("SRV_PORT", getenv("DB_PORT"));
define("SRV_DBNAME", getenv("DB_DATABASE"));
define("SRV_USERNAME", getenv("DB_USERNAME"));
define("SRV_PASSWORD", getenv("DB_PASSWORD"));

define("TB_USER", getenv("TB_USER"));
define("USER_ATTB_NOME", getenv("USER_ATTB_NOME"));
define("USER_ATTB_ID", getenv("USER_ATTB_ID"));
define("USER_ATTB_EMAIL", getenv("USER_ATTB_EMAIL"));
define("USER_ATTB_FOTO", getenv("USER_ATTB_FOTO"));

define("TB_MENSAGENS", getenv("TB_MENSAGENS"));
define("MENSAGENS_ATTB_ID", getenv("MENSAGENS_ATTB_ID"));
define("MENSAGENS_ATTB_MSG", getenv("MENSAGENS_ATTB_MSG"));
define("MENSAGENS_ATTB_ANEXOS", getenv("MENSAGENS_ATTB_ANEXOS"));
define("MENSAGENS_ATTB_DE", getenv("MENSAGENS_ATTB_DE"));
define("MENSAGENS_ATTB_PARA", getenv("MENSAGENS_ATTB_PARA"));
define("MENSAGENS_ATTB_CRIADO_AOS", getenv("MENSAGENS_ATTB_CRIADO_AOS"));
define("MENSAGENS_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_ATTB_ACTUALIZADO_AOS"));

try {
    $pdo = new PDO("mysql:host=" . SRV_HOST . ";port=" . SRV_PORT . ";dbname=" . SRV_DBNAME . ";charset=utf8", SRV_USERNAME, SRV_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}


// Criar a tabela se não existir
$sql = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS . " (
    " . MENSAGENS_ATTB_ID . " bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . MENSAGENS_ATTB_MSG . " TEXT,
    " . MENSAGENS_ATTB_ANEXOS . " TEXT,
    " . MENSAGENS_ATTB_DE . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_PARA . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . MENSAGENS_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (" . MENSAGENS_ATTB_DE . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
    FOREIGN KEY (" . MENSAGENS_ATTB_PARA . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Executar a consulta para criar a tabela
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
