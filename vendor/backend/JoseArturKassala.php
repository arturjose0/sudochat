<?php
header('Content-Type: application/json');
// Define o fuso horário para Luanda, Angola (UTC+1)
// date_default_timezone_set('Africa/Luanda');

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

// definir a chave de encriptação
define("DB_ENCRYPTION_KEY", getenv("DB_ENCRYPTION_KEY"));

//tabela utilizadores
define("TB_USER", getenv("TB_USER"));
define("USER_ATTB_NOME", getenv("USER_ATTB_NOME"));
define("USER_ATTB_ID", getenv("USER_ATTB_ID"));
define("USER_ATTB_EMAIL", getenv("USER_ATTB_EMAIL"));
define("USER_ATTB_PASSWORD", getenv("USER_ATTB_PASSWORD"));
define("USER_ATTB_FOTO", getenv("USER_ATTB_FOTO"));
define("USER_ATTB_CRIADO_AOS", getenv("USER_ATTB_CRIADO_AOS"));
define("USER_ATTB_ACTUALIZADO_AOS", getenv("USER_ATTB_ACTUALIZADO_AOS"));

//tabela mensagens
define("TB_MENSAGENS", getenv("TB_MENSAGENS"));
define("MENSAGENS_ATTB_ID", getenv("MENSAGENS_ATTB_ID"));
define("MENSAGENS_ATTB_MSG", getenv("MENSAGENS_ATTB_MSG"));
define("MENSAGENS_ATTB_DE", getenv("MENSAGENS_ATTB_DE"));
define("MENSAGENS_ATTB_PARA", getenv("MENSAGENS_ATTB_PARA"));
define("MENSAGENS_ATTB_ESTADO", getenv("MENSAGENS_ATTB_ESTADO"));
define("MENSAGENS_ATTB_CRIADO_AOS", getenv("MENSAGENS_ATTB_CRIADO_AOS"));
define("MENSAGENS_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_ATTB_ACTUALIZADO_AOS"));

//tabela que recebe os ficheiros das mensagens
// define("TB_MENSAGENS_ARQUIVOS", getenv("TB_MENSAGENS_ARQUIVOS"));
// define("MENSAGENS_ARQUIVOS_ATTB_ID", getenv("MENSAGENS_ARQUIVOS_ATTB_ID"));
// define("MENSAGENS_ARQUIVOS_ATTB_ANEXOS", getenv("MENSAGENS_ARQUIVOS_ATTB_ANEXOS"));
// define("MENSAGENS_ARQUIVOS_ATTB_DE", getenv("MENSAGENS_ARQUIVOS_ATTB_DE"));
// define("MENSAGENS_ARQUIVOS_ATTB_PARA", getenv("MENSAGENS_ARQUIVOS_ATTB_PARA"));
// define("MENSAGENS_ARQUIVOS_ATTB_ESTADO", getenv("MENSAGENS_ARQUIVOS_ATTB_ESTADO"));
// define("MENSAGENS_ARQUIVOS_ATTB_CRIADO_AOS", getenv("MENSAGENS_ARQUIVOS_ATTB_CRIADO_AOS"));
// define("MENSAGENS_ARQUIVOS_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_ARQUIVOS_ATTB_ACTUALIZADO_AOS"));

//tabela sessoes
define("TB_SESSOES", getenv("TB_SESSOES"));
define("SESSOES_ATTB_ID", getenv("SESSOES_ATTB_ID"));
define("SESSOES_ATTB_USER_ID", getenv("SESSOES_ATTB_USER_ID"));
define("SESSOES_ATTB_TEMPO", getenv("SESSOES_ATTB_TEMPO"));
define("SESSOES_ATTB_ESTADO", getenv("SESSOES_ATTB_ESTADO"));
define("SESSOES_ATTB_CRIADO_AOS", getenv("SESSOES_ATTB_CRIADO_AOS"));
define("SESSOES_ATTB_ACTUALIZADO_AOS", getenv("SESSOES_ATTB_ACTUALIZADO_AOS"));

//tabela TB_MENSAGENS_GRUPO
define("TB_GRUPO", getenv("TB_GRUPO"));
define("GRUPO_ATTB_ID", getenv("GRUPO_ATTB_ID"));
define("GRUPO_ATTB_NOME", getenv("GRUPO_ATTB_NOME"));
define("GRUPO_ATTB_FOTO", getenv("GRUPO_ATTB_FOTO"));
define("GRUPO_ATTB_ESTADO", getenv("GRUPO_ATTB_ESTADO"));
define("GRUPO_ATTB_CRIADO_AOS", getenv("GRUPO_ATTB_CRIADO_AOS"));
define("GRUPO_ATTB_ACTUALIZADO_AOS", getenv("GRUPO_ATTB_ACTUALIZADO_AOS"));

//tabela TB_MENSAGENS_GRUPO
define("TB_GRUPO_MEMBROS", getenv("TB_GRUPO_MEMBROS"));
define("GRUPO_MEMBROS_ATTB_ID", getenv("GRUPO_MEMBROS_ATTB_ID"));
define("GRUPO_MEMBROS_ATTB_USER", getenv("GRUPO_MEMBROS_ATTB_USER"));
define("GRUPO_MEMBROS_ATTB_GRUPO", getenv("GRUPO_MEMBROS_ATTB_GRUPO"));
define("GRUPO_MEMBROS_ATTB_ADMIN", getenv("GRUPO_MEMBROS_ATTB_ADMIN"));
define("GRUPO_MEMBROS_ATTB_ESTADO", getenv("GRUPO_MEMBROS_ATTB_ESTADO"));
define("GRUPO_MEMBROS_ATTB_CRIADO_AOS", getenv("GRUPO_MEMBROS_ATTB_CRIADO_AOS"));
define("GRUPO_MEMBROS_ATTB_ACTUALIZADO_AOS", getenv("GRUPO_MEMBROS_ATTB_ACTUALIZADO_AOS"));

//tabela TB_MENSAGENS_USER_GRUPO
define("TB_MENSAGENS_USER_GRUPO", getenv("TB_MENSAGENS_USER_GRUPO"));
define("MENSAGENS_USER_GRUPO_ATTB_ID", getenv("MENSAGENS_USER_GRUPO_ATTB_ID"));
define("MENSAGENS_USER_GRUPO_ATTB_MSG", getenv("MENSAGENS_USER_GRUPO_ATTB_MSG"));
define("MENSAGENS_USER_GRUPO_ATTB_MEMBRO", getenv("MENSAGENS_USER_GRUPO_ATTB_MEMBRO"));
define("MENSAGENS_USER_GRUPO_ATTB_GRUPO", getenv("MENSAGENS_USER_GRUPO_ATTB_GRUPO"));
define("MENSAGENS_USER_GRUPO_ATTB_ESTADO", getenv("MENSAGENS_USER_GRUPO_ATTB_ESTADO"));
define("MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS", getenv("MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS"));
define("MENSAGENS_USER_GRUPO_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_USER_GRUPO_ATTB_ACTUALIZADO_AOS"));

//tabela TB_MENSAGENS_USER_GRUPO_ARQUIVOS que recebe os arquivos dos grupos
// define("TB_MENSAGENS_USER_GRUPO_ARQUIVOS", getenv("TB_MENSAGENS_USER_GRUPO_ARQUIVOS"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ID", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ID"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ANEXOS", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ANEXOS"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_MEMBRO", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_MEMBRO"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_GRUPO", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_GRUPO"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ESTADO", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ESTADO"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_CRIADO_AOS", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_CRIADO_AOS"));
// define("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ACTUALIZADO_AOS"));

try {
    $pdo = new PDO("mysql:host=" . SRV_HOST . ";port=" . SRV_PORT . ";dbname=" . SRV_DBNAME . ";charset=utf8", SRV_USERNAME, SRV_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start(); // Iniciar a sessão

    function validarLogin(PDO $pdo)
    {

        if (isset($_SESSION['SUDOCHAT_SESSAO_ID'])) {
            $id = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
            $stmt = $pdo->prepare("SELECT " . USER_ATTB_NOME . " as " . USER_ATTB_NOME . ", " . USER_ATTB_EMAIL . " as " . USER_ATTB_EMAIL . " FROM " . TB_USER . "  WHERE " . USER_ATTB_ID . " = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}


$sqlUser = "CREATE TABLE IF NOT EXISTS " . TB_USER . " (
    " . USER_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . USER_ATTB_NOME . " VARCHAR(255) NOT NULL,
    " . USER_ATTB_EMAIL . " VARCHAR(255) NOT NULL UNIQUE,
    " . USER_ATTB_PASSWORD . " VARCHAR(255) NOT NULL,
    " . USER_ATTB_FOTO . " TEXT DEFAULT NULL,
    " . USER_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . USER_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlSessoes = "CREATE TABLE IF NOT EXISTS " . TB_SESSOES . " (
    " . SESSOES_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . SESSOES_ATTB_USER_ID . " bigint UNSIGNED NOT NULL,
    " . SESSOES_ATTB_TEMPO . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . SESSOES_ATTB_ESTADO . " BOOLEAN DEFAULT TRUE,
    " . SESSOES_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . SESSOES_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (" . SESSOES_ATTB_USER_ID . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sql = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS . " (
    " . MENSAGENS_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . MENSAGENS_ATTB_MSG . " JSON,
    " . MENSAGENS_ATTB_DE . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_PARA . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 0,
    " . MENSAGENS_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . MENSAGENS_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (" . MENSAGENS_ATTB_DE . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
    FOREIGN KEY (" . MENSAGENS_ATTB_PARA . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// $sqlsqruivosMsg = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS_ARQUIVOS . " (
//     " . MENSAGENS_ARQUIVOS_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
//     " . MENSAGENS_ARQUIVOS_ATTB_ANEXOS . " TEXT,
//     " . MENSAGENS_ARQUIVOS_ATTB_DE . " bigint UNSIGNED NOT NULL,
//     " . MENSAGENS_ARQUIVOS_ATTB_PARA . " bigint UNSIGNED NOT NULL,
//     " . MENSAGENS_ARQUIVOS_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 0,
//     " . MENSAGENS_ARQUIVOS_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     " . MENSAGENS_ARQUIVOS_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     FOREIGN KEY (" . MENSAGENS_ARQUIVOS_ATTB_DE . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
//     FOREIGN KEY (" . MENSAGENS_ARQUIVOS_ATTB_PARA . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlGrupo = "CREATE TABLE IF NOT EXISTS " . TB_GRUPO . " (
    " . GRUPO_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . GRUPO_ATTB_NOME . " VARCHAR(255) NOT NULL UNIQUE,
    " . GRUPO_ATTB_FOTO . " TEXT,
    " . GRUPO_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 1,
    " . GRUPO_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . GRUPO_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlMembrosGrupo = "CREATE TABLE IF NOT EXISTS " . TB_GRUPO_MEMBROS . " (
    " . GRUPO_MEMBROS_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . GRUPO_MEMBROS_ATTB_USER . " bigint UNSIGNED NOT NULL,
    " . GRUPO_MEMBROS_ATTB_GRUPO . " bigint UNSIGNED NOT NULL,
    " . GRUPO_MEMBROS_ATTB_ADMIN . " int(1) NOT NULL DEFAULT 0,
    " . GRUPO_MEMBROS_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 1,
    " . GRUPO_MEMBROS_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . GRUPO_MEMBROS_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlMensagemGrupo = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS_USER_GRUPO . " (
    " . MENSAGENS_USER_GRUPO_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . MENSAGENS_USER_GRUPO_ATTB_MSG . " JSON,
    " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_USER_GRUPO_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 1,
    " . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . MENSAGENS_USER_GRUPO_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (" . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
    FOREIGN KEY (" . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ") REFERENCES " . TB_GRUPO . "(" . GRUPO_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// $sqlMensagemGrupo_arquivos = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS_USER_GRUPO_ARQUIVOS . " (
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ANEXOS . " TEXT,
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_MEMBRO . " bigint UNSIGNED NOT NULL,
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_GRUPO . " bigint UNSIGNED NOT NULL,
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 1,
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     " . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     FOREIGN KEY (" . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_MEMBRO . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
//     FOREIGN KEY (" . MENSAGENS_USER_GRUPO_ARQUIVOS_ATTB_GRUPO . ") REFERENCES " . TB_GRUPO . "(" . GRUPO_ATTB_ID . ") ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Executar a consulta para criar a tabela
try {

    $pdo->exec($sqlUser);
    $pdo->exec($sqlSessoes);
    $pdo->exec($sql);
    $pdo->exec($sqlGrupo);
    $pdo->exec($sqlMembrosGrupo);
    $pdo->exec($sqlMensagemGrupo);
    // $pdo->exec($sqlMensagemGrupo_arquivos);
    // $pdo->exec($sqlsqruivosMsg);
    // echo "Tabela verificada/criada com sucesso!";
} catch (PDOException $e) {

    // echo $sqlUser;
    $resp["status"] = "error";
    $resp["msg"] = $e;
    echo json_encode(TB_GRUPO);
    // echo "\n";
    // echo $sqlMensagemGrupo;
    // echo $sqlMensagemGrupo;
}


// Função para criptografar dados
function criptografar($data)
{
    // Gerar um vetor de inicialização (IV) aleatório
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Criptografar os dados
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', DB_ENCRYPTION_KEY, 0, $iv);

    // Retornar a criptografia juntamente com o IV (necessário para descriptografar)
    return base64_encode($iv . $encrypted);
}

// Função para descriptografar dados
function decriptografar($data)
{
    // Decodificar os dados
    $data = base64_decode($data);

    // Extrair o IV dos primeiros bytes (tamanho do IV depende do algoritmo utilizado)
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);

    // Extrair os dados criptografados
    $encrypted = substr($data, $ivLength);

    // Descriptografar os dados
    return openssl_decrypt($encrypted, 'aes-256-cbc', DB_ENCRYPTION_KEY, 0, $iv);
}

/**
 * Filtra tags HTML e remove códigos PHP e outros elementos perigosos.
 *
 * @param string $html O HTML a ser filtrado.
 * @return string O HTML filtrado.
 */
function exibirCodigoSeguro($html, array $tagsPermitidas = ['b', 'em', 'i', 'strong', 'u', 'br', 'p', 'span'])
{
    // 1. Remover tags PHP, <script>, <style>, etc.
    $html = preg_replace('/<\?(php)?(.*?)\?>/is', '', $html); // Remove código PHP
    $html = preg_replace('/<(script|style|iframe|object|embed|form|textarea|input|select|button|meta|link)[^>]*>.*?<\ /\1>
        /is', '', $html); // Tags perigosas

    // 2. Remover atributos "on..." (onclick, onerror etc.)
    $html = preg_replace('/\s+on\w+="[^"]*"/i', '', $html);
    $html = preg_replace("/\s+on\w+='[^']*'/i", '', $html);

    // 3. Escapar o conteúdo inteiro
    $htmlEscapado = htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // 4. Reverter as tags permitidas (removendo o escape delas)
    foreach ($tagsPermitidas as $tag) {
        $htmlEscapado = preg_replace_callback(
            "/&lt;\/?$tag(?: [^&]*)&gt;/i",
            function ($matches) {
                return html_entity_decode($matches[0]);
            },
            $htmlEscapado
        );
    }

    return $htmlEscapado;
}

/**
 * Obtém a data atual do banco de dados MySQL.
 *
 * @param PDO $pdo Conexão PDO ativa com o banco de dados
 * @return string|null Retorna a data atual no formato 'Y-m-d H:i:s' ou null em caso de erro
 */
function dataActual(PDO $pdo)
{
    try {
        $query = "SELECT NOW() AS data_atual";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorna a data no formato padrão 'Y-m-d H:i:s'
        return $result['data_atual'] ?? null;
    } catch (PDOException $e) {
        // Log do erro (opcional, ajuste conforme seu sistema)
        error_log("Erro ao obter a data atual do banco: " . $e->getMessage());
        return null;
    }
}
