<?php
require 'config.php'; // Arquivo com a conexão ao banco de dados

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['de']) || !isset($_POST['para']) || !isset($_POST['ultimo_id'])) {
        die("Parâmetros inválidos!");
    }

    $de = (int) $_POST['de'];
    $para = (int) $_POST['para'];
    $ultimo_id = (int) $_POST['ultimo_id'];

    // Busca apenas mensagens novas
    $sql = "SELECT " . MENSAGENS_ATTB_ID . ", " . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . " as " . MENSAGENS_ATTB_DE . " FROM " . TB_MENSAGENS . " 
            WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de) 
            ) AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
            ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':de', $de, PDO::PARAM_INT);
    $stmt->bindParam(':para', $para, PDO::PARAM_INT);
    $stmt->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
    $stmt->execute();

    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($mensagens);
}
