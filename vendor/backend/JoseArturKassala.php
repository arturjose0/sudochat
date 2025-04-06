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

// definir a chave de encriptação
define("DB_ENCRYPTION_KEY", getenv("DB_ENCRYPTION_KEY"));

define("TB_USER", getenv("TB_USER"));
define("USER_ATTB_NOME", getenv("USER_ATTB_NOME"));
define("USER_ATTB_ID", getenv("USER_ATTB_ID"));
define("USER_ATTB_EMAIL", getenv("USER_ATTB_EMAIL"));
define("USER_ATTB_PASSWORD", getenv("USER_ATTB_PASSWORD"));
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