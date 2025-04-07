<?php

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
define("MENSAGENS_ATTB_ANEXOS", getenv("MENSAGENS_ATTB_ANEXOS"));
define("MENSAGENS_ATTB_DE", getenv("MENSAGENS_ATTB_DE"));
define("MENSAGENS_ATTB_PARA", getenv("MENSAGENS_ATTB_PARA"));
define("MENSAGENS_ATTB_ESTADO", getenv("MENSAGENS_ATTB_ESTADO"));
define("MENSAGENS_ATTB_CRIADO_AOS", getenv("MENSAGENS_ATTB_CRIADO_AOS"));
define("MENSAGENS_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_ATTB_ACTUALIZADO_AOS"));

//tabela sessoes
define("TB_SESSOES", getenv("TB_SESSOES"));
define("SESSOES_ATTB_ID", getenv("SESSOES_ATTB_ID"));
define("SESSOES_ATTB_USER_ID", getenv("SESSOES_ATTB_USER_ID"));
define("SESSOES_ATTB_TEMPO", getenv("SESSOES_ATTB_TEMPO"));
define("SESSOES_ATTB_ESTADO", getenv("SESSOES_ATTB_ESTADO"));
define("SESSOES_ATTB_CRIADO_AOS", getenv("SESSOES_ATTB_CRIADO_AOS"));
define("SESSOES_ATTB_ACTUALIZADO_AOS", getenv("SESSOES_ATTB_ACTUALIZADO_AOS"));

//tabela TB_MENSAGENS_GRUPO
define("TB_MENSAGENS_GRUPO", getenv("TB_MENSAGENS_GRUPO"));
define("MENSAGENS_GRUPO_ATTB_ID", getenv("MENSAGENS_GRUPO_ATTB_ID"));
define("MENSAGENS_GRUPO_ATTB_NOME", getenv("MENSAGENS_GRUPO_ATTB_NOME"));
define("MENSAGENS_GRUPO_ATTB_ESTADO", getenv("MENSAGENS_GRUPO_ATTB_ESTADO"));
define("MENSAGENS_GRUPO_ATTB_CRIADO_AOS", getenv("MENSAGENS_GRUPO_ATTB_CRIADO_AOS"));
define("MENSAGENS_GRUPO_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_GRUPO_ATTB_ACTUALIZADO_AOS"));

//tabela TB_MENSAGENS_USER_GRUPO
define("TB_MENSAGENS_USER_GRUPO", getenv("TB_MENSAGENS_USER_GRUPO"));
define("MENSAGENS_USER_GRUPO_ATTB_ID", getenv("MENSAGENS_USER_GRUPO_ATTB_ID"));
define("MENSAGENS_USER_GRUPO_ATTB_MSG", getenv("MENSAGENS_USER_GRUPO_ATTB_MSG"));
define("MENSAGENS_USER_GRUPO_ATTB_ANEXOS", getenv("MENSAGENS_USER_GRUPO_ATTB_ANEXOS"));
define("MENSAGENS_USER_GRUPO_ATTB_DE", getenv("MENSAGENS_USER_GRUPO_ATTB_DE"));
define("MENSAGENS_USER_GRUPO_ATTB_PARA", getenv("MENSAGENS_USER_GRUPO_ATTB_PARA"));
define("MENSAGENS_USER_GRUPO_ATTB_ESTADO", getenv("MENSAGENS_USER_GRUPO_ATTB_ESTADO"));
define("MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS", getenv("MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS"));
define("MENSAGENS_USER_GRUPO_ATTB_ACTUALIZADO_AOS", getenv("MENSAGENS_USER_GRUPO_ATTB_ACTUALIZADO_AOS"));

try {
    $pdo = new PDO("mysql:host=" . SRV_HOST . ";port=" . SRV_PORT . ";dbname=" . SRV_DBNAME . ";charset=utf8", SRV_USERNAME, SRV_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start(); // Iniciar a sessão

    function validarLogin()
    {

        if (isset($_SESSION['SUDOCHAT_SESSAO_ID'])) {
            $pdo = new PDO("mysql:host=" . SRV_HOST . ";port=" . SRV_PORT . ";dbname=" . SRV_DBNAME . ";charset=utf8", SRV_USERNAME, SRV_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    " . MENSAGENS_ATTB_MSG . " TEXT,
    " . MENSAGENS_ATTB_ANEXOS . " TEXT,
    " . MENSAGENS_ATTB_DE . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_PARA . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 0,
    " . MENSAGENS_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . MENSAGENS_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (" . MENSAGENS_ATTB_DE . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
    FOREIGN KEY (" . MENSAGENS_ATTB_PARA . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlGrupo = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS_GRUPO . " (
    " . MENSAGENS_GRUPO_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . MENSAGENS_GRUPO_ATTB_NOME . " VARCHAR(255) NOT NULL UNIQUE,
    " . MENSAGENS_GRUPO_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 0,
    " . MENSAGENS_GRUPO_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . MENSAGENS_GRUPO_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlUserGrupo = "CREATE TABLE IF NOT EXISTS " . TB_MENSAGENS_USER_GRUPO . " (
    " . MENSAGENS_USER_GRUPO_ATTB_ID . " bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    " . MENSAGENS_USER_GRUPO_ATTB_MSG . " TEXT,
    " . MENSAGENS_USER_GRUPO_ATTB_ANEXOS . " TEXT,
    " . MENSAGENS_USER_GRUPO_ATTB_DE . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_USER_GRUPO_ATTB_PARA . " bigint UNSIGNED NOT NULL,
    " . MENSAGENS_USER_GRUPO_ATTB_ESTADO . " int(1) NOT NULL DEFAULT 0,
    " . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    " . MENSAGENS_USER_GRUPO_ATTB_ACTUALIZADO_AOS . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (" . MENSAGENS_USER_GRUPO_ATTB_DE . ") REFERENCES " . TB_USER . "(" . USER_ATTB_ID . ") ON DELETE CASCADE,
    FOREIGN KEY (" . MENSAGENS_USER_GRUPO_ATTB_PARA . ") REFERENCES " . TB_MENSAGENS_GRUPO . "(" . MENSAGENS_GRUPO_ATTB_ID . ") ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Executar a consulta para criar a tabela
try {

    $query = "
    SELECT 
    u.id AS user_id,
    u.nome AS user_nome,
    m.msg AS ultima_mensagem,
    m.criado_aos AS data_ultima_mensagem,
    m.estado AS estado_mensagem,
    CASE 
        WHEN s.tempo > NOW() AND s.estado = 1 THEN 1 
        ELSE 0 
    END AS sessao_ativa
FROM 
    " . TB_USER . " u
LEFT JOIN 
    " . TB_SESSOES . " s ON u.id = s." . SESSOES_ATTB_USER_ID . "
JOIN 
    (SELECT 
         " . MENSAGENS_ATTB_DE . " AS usuario, 
         " . MENSAGENS_ATTB_PARA . " AS outro_usuario,
         " . MENSAGENS_ATTB_MSG . " AS msg,
         " . MENSAGENS_ATTB_CRIADO_AOS . " AS criado_aos,
         " . MENSAGENS_ATTB_ESTADO . " AS estado
     FROM 
         " . TB_MENSAGENS . " 
     WHERE 
         (" . MENSAGENS_ATTB_DE . " = 1 OR " . MENSAGENS_ATTB_PARA . " = 1)
     AND 
         " . MENSAGENS_ATTB_CRIADO_AOS . " = (
             SELECT MAX(" . MENSAGENS_ATTB_CRIADO_AOS . ")
             FROM " . TB_MENSAGENS . " m2
             WHERE 
                 (m2." . MENSAGENS_ATTB_DE . " = " . TB_MENSAGENS . "." . MENSAGENS_ATTB_DE . " AND m2." . MENSAGENS_ATTB_PARA . " = " . TB_MENSAGENS . "." . MENSAGENS_ATTB_PARA . ")
                 OR 
                 (m2." . MENSAGENS_ATTB_DE . " = " . TB_MENSAGENS . "." . MENSAGENS_ATTB_PARA . " AND m2." . MENSAGENS_ATTB_PARA . " = " . TB_MENSAGENS . "." . MENSAGENS_ATTB_DE . ")
         )
    ) m 
ON 
    (u.id = m.usuario AND m.outro_usuario = 1) 
    OR 
    (u.id = m.outro_usuario AND m.usuario = 1)
WHERE 
    u.id != 1
GROUP BY 
    u.id, u.nome, m.msg, m.criado_aos, m.estado, s.tempo, s.estado
ORDER BY 
    m.criado_aos DESC;
    ";

    // echo $query;

    // exit;

    $pdo->exec($sqlUser);
    $pdo->exec($sqlSessoes);
    $pdo->exec($sql);
    $pdo->exec($sqlGrupo);
    $pdo->exec($sqlUserGrupo);
    // echo "Tabela verificada/criada com sucesso!";
} catch (PDOException $e) {

    // echo $sqlUser;
    $resp["status"] = "error";
    $resp["msg"] = $e;
    echo json_encode($resp);
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
