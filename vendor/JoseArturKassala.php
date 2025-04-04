<?php
require '../backends/config.php'; // Arquivo com a conexão ao banco de dados

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['mensagem'])) {
        die(json_encode(["erro" => "Mensagem não informada!"]));
    }

    $mensagem = trim($_POST['mensagem']); // Remover espaços em branco extras
    $emissor = 1; // ID do usuário que enviou a mensagem
    $receptor = 2; // ID do usuário que recebe a mensagem

    // Query corrigida (usando corretamente as constantes)
    $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
            VALUES (:msg, :de, :para)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
        $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);
        $stmt->bindParam(':msg', $mensagem, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(["sucesso" => "Mensagem enviada com sucesso!", "mensagem" => $mensagem]);
    } catch (PDOException $e) {
        echo json_encode(["erro" => "Erro ao salvar mensagem: " . $e->getMessage()]);
    }
}
