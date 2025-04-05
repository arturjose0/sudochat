<?php
require_once 'JoseArturKassala.php'; // Arquivo com a conexão ao banco de dados


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    //pegar todas as mensagens enviadas e recebidas
    if (isset($_POST['de']) && isset($_POST['para']) && isset($_POST['ultimo_id'])) {

        try {
            $de = (int) $_POST['de'];
            $para = (int) $_POST['para'];
            $ultimo_id = (int) $_POST['ultimo_id'];

            // Busca apenas mensagens novas
            $sql = "SELECT " . MENSAGENS_ATTB_ID . ", " . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . " as " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_CRIADO_AOS . " as " . MENSAGENS_ATTB_CRIADO_AOS . " FROM " . TB_MENSAGENS . " 
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
        } catch (PDOException $e) {
            echo json_encode(["erro" => "Erro ao buscar mensagens: " . $e->getMessage()]);
        } finally {
            exit;
        }
    } else

        // enviar mensagem
        if (isset($_POST['mensagem']) && isset($_POST['receptor'])) {

            $mensagem = trim($_POST['mensagem']); // Remover espaços em branco extras
            $emissor = 1; // ID do usuário que enviou a mensagem
            $receptor = filter_var(base64_decode($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT); // ID do usuário que recebe a mensagem

            // Query corrigida (usando corretamente as constantes)
            $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
            VALUES (:msg, :de, :para)";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
                $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);
                $stmt->bindParam(':msg', $mensagem, PDO::PARAM_STR);
                // $stmt->execute();

                if ($stmt->execute()) {
                    $resp["status"] = "success";
                    // $resp["msg"] = "Registado com Sucesso";
                } else {
                    $resp["status"] = "error";
                    $resp["msg"] = "Mensagem não enviada";
                }

                echo json_encode($resp);
            } catch (PDOException $e) {
                echo json_encode(["erro" => "Erro ao salvar mensagem: " . $e->getMessage()]);
            } finally {
                exit;
            }
        } else
            //abrir a janela de mensagens
            if (isset($_POST['id']) && ctype_digit($_POST['id'])) {

                define("ID", $_POST['id']);

                // include_once '../backend/JoseArturKassala.php'; // Arquivo de conexão
                $stmt = $pdo->prepare("SELECT " . USER_ATTB_ID . " as USER_ATTB_ID, " . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_USER . " where " . USER_ATTB_ID . "=" . ID);
                $stmt->execute();
                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                $ATTB_NOME = $dados["USER_ATTB_NOME"];

                $ATTB_ID = base64_encode($dados["USER_ATTB_ID"]);
                require_once '../pages/mensagem.php'; // Arquivo da mensagem
            }
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Verifica se o ID do usuário foi passado na URL
    try {
        $stmt = $pdo->prepare("SELECT " . USER_ATTB_ID . " as USER_ATTB_ID, " . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_USER);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        die("Erro na consulta: " . $e->getMessage());
    } finally {
        exit;
    }

    // print_r(select());
}
