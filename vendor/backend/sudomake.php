<?php
require_once 'JoseArturKassala.php'; // Arquivo com a conexão ao banco de dados

if (($_SERVER["REQUEST_METHOD"] === "POST" && validarLogin()) || $_SERVER["REQUEST_METHOD"] === "POST" && !validarLogin() && isset($_POST['login']) && isset($_POST['password'])) {

    //pegar todas as mensagens enviadas e recebidas
    if (isset($_POST['para']) && isset($_POST['ultimo_id'])) {

        try {
            $de = (int) decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
            $para = (int) $_POST['para'];
            $ultimo_id = (int) $_POST['ultimo_id'];

            // Busca apenas mensagens novas
            $sql = "SELECT " . MENSAGENS_ATTB_ID . " as " . MENSAGENS_ATTB_ID . ", " . MENSAGENS_ATTB_MSG . " AS " . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . " as " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_CRIADO_AOS . " as " . MENSAGENS_ATTB_CRIADO_AOS . " FROM " . TB_MENSAGENS . " 
            WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de) 
            ) AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
            ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':de', $de, PDO::PARAM_INT);
            $stmt->bindParam(':para', $para, PDO::PARAM_INT);
            $stmt->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
            $stmt->execute();

            $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Descriptografar as mensagens
            foreach ($mensagens as &$mensagem) {
                // Descriptografando a mensagem
                $mensagem['MENSAGENS_ATTB_MSG'] = htmlspecialchars(decriptografar($mensagem[MENSAGENS_ATTB_MSG]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                // $mensagem['MENSAGENS_ATTB_MSG'] = exibirCodigoSeguro(decriptografar($mensagem[MENSAGENS_ATTB_MSG]));
            }

            header('Content-Type: application/json');
            echo json_encode($mensagens);
        } catch (PDOException $e) {
            echo json_encode(["erro" => "Erro ao buscar mensagens: " . $e->getMessage()]);
        } finally {
            exit;
        }
    } else

        // enviar mensagem
        if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_FILES['anexos'])) {

            if (empty($_FILES['anexos']['name'][0]) && empty($_POST['mensagem'])) {
                $resp["status"] = "error";
                $resp["msg"] = "A mensagem não pode ser vazia";
                echo json_encode($resp);

                exit;
            }

            $mensagem = criptografar(trim($_POST['mensagem'])); // Remover espaços em branco extras
            $anexos = trim($_POST['mensagem']); // Remover espaços em branco extras
            $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT); // ID do usuário que enviou a mensagem
            $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT); // ID do usuário que recebe a mensagem

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
                    $resp["msg"] = "Registado com Sucesso";
                } else {
                    $resp["status"] = "error";
                    $resp["msg"] = "Mensagem não enviada";
                }
            } catch (PDOException $e) {
                $resp["status"] = "error";
                $resp["msg"] = "Mensagem não enviada";
                // echo json_encode(["erro" => "Erro ao salvar mensagem: " . $e->getMessage()]);
            } finally {
                echo json_encode($resp);
                exit;
            }
        } else
            //abrir a janela de mensagens
            if (isset($_POST['id']) && ctype_digit($_POST['id'])) {

                define("ID", $_POST['id']);

                // include_once '../backend/JoseArturKassala.php'; // Arquivo de conexão
                $stmt = $pdo->prepare("SELECT u." . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_USER . " u where u." . USER_ATTB_ID . "=" . ID);
                $stmt->execute();
                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                $ATTB_NOME = $dados["USER_ATTB_NOME"];

                $ATTB_ID = criptografar(ID);
                require_once '../pages/mensagem.php'; // Arquivo da mensagem
            } else
                //fazer login
                if (isset($_POST["login"]) && isset($_POST["password"])) {
                    $utilizador = $_POST["login"];

                    $stmt = $pdo->prepare("SELECT * FROM " . TB_USER . " WHERE " . USER_ATTB_NOME . "=:utilizador OR " . USER_ATTB_EMAIL . "=:utilizador");
                    $stmt->bindParam(':utilizador', $utilizador);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (password_verify($_POST["password"], $row[USER_ATTB_PASSWORD])) {
                            $_SESSION['SUDOCHAT_SESSAO_ID'] = criptografar($row[USER_ATTB_ID]);
                            // header("location: ../dashboard.php");
                            $resp["status"] = "success";
                            $resp["msg"] = "Login Efectuado com Successo";
                            $resp["accao"] = "refresh";
                        } else {
                            $resp["status"] = "error";
                            $resp["msg"] = "Password errada";
                        }
                    } else {
                        $resp["status"] = "error";
                        $resp["msg"] = "Utilizador Não consta na nossa base de dados";
                        // echo "ere name errado";
                    }

                    echo json_encode($resp);
                    exit();
                } else if (isset($_POST["ultimoID_do"])) {

                    try {
                        $ultimoID = $_POST["ultimoID_do"];
                        $stmt = $pdo->prepare("SELECT tm." . MENSAGENS_ATTB_ID . " FROM " . TB_MENSAGENS . " tm WHERE tm." . MENSAGENS_ATTB_DE . " =:id_mensagem OR tm." . MENSAGENS_ATTB_PARA . " =:id_mensagem ORDER BY tm." . MENSAGENS_ATTB_ID . " DESC LIMIT 1");
                        $stmt->bindParam(':id_mensagem', $ultimoID, PDO::PARAM_INT);
                        if ($stmt->execute()) {
                            if ($stmt->rowCount() > 0) {
                                $resp["status"] = "success";
                                $resp["ultimoID"] = $stmt->fetch(PDO::FETCH_ASSOC)[MENSAGENS_ATTB_ID];
                            } else {
                                $resp["status"] = "error";
                            }
                        } else {
                            $resp["status"] = "success";
                            $resp["ultimoID"] = 0;
                        }
                    } catch (PDOException $e) {
                        $resp["status"] = "error";
                        $resp["msg"] = "Erro ao buscar o último ID: " . $e->getMessage();
                    } finally {
                        echo json_encode($resp);
                        exit;
                    }
                } else
                    // Lógica para terminar sessão
                    if (isset($_POST["terminarSessao"])) {
                        try {
                            // Destroi a sessão
                            session_unset(); // Remove todas as variáveis da sessão
                            session_destroy(); // Destroi a sessão completamente

                            $resp["status"] = "success";
                            $resp["msg"] = "Sessão terminada com sucesso";
                        } catch (Exception $e) {
                            $resp["status"] = "error";
                            $resp["msg"] = "Erro ao terminar sessão: " . $e->getMessage();
                        } finally {
                            echo json_encode($resp);
                            exit;
                        }
                    }
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {

    //listar todos os utilizadores
    // Verifica se o ID do usuário foi passado na URL
    if (isset($_GET["usuarios"]) && validarLogin()) {
        try {
            $stmt = $pdo->prepare("SELECT " . USER_ATTB_ID . " as USER_ATTB_ID, " . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_USER . " WHERE " . USER_ATTB_ID . " != :id");
            $id = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            die("Erro na consulta: " . $e->getMessage());
        } finally {
            exit;
        }
    } else if (isset($_GET["isLoggedIn"])) {
        if (isset($_SESSION['SUDOCHAT_SESSAO_ID'])) {
            $id = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
            $stmt = $pdo->prepare("SELECT " . USER_ATTB_NOME . " as " . USER_ATTB_NOME . ", " . USER_ATTB_EMAIL . " as " . USER_ATTB_EMAIL . " FROM " . TB_USER . "  WHERE " . USER_ATTB_ID . " = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $resp['SUDOCHAT_SESSAO_ID'] = $id;
                $resp['SUDOCHAT_SESSAO_NOME'] = $row[USER_ATTB_NOME];
                $resp['SUDOCHAT_SESSAO_EMAIL'] = $row[USER_ATTB_EMAIL];
                $resp["status"] = "success";
            }
        }

        $resp["status"] = isset($resp["status"]) ? $resp["status"] : "error";
        echo json_encode($resp);
        exit;
    }
}