<?php
require_once 'JoseArturKassala.php'; // Arquivo com a conexão ao banco de dados

if (($_SERVER["REQUEST_METHOD"] === "POST" && validarLogin($pdo)) || $_SERVER["REQUEST_METHOD"] === "POST" && !validarLogin($pdo) && isset($_POST['login']) && isset($_POST['password'])) {

    //pegar todas as mensagens enviadas e recebidas
    if (isset($_POST['para']) && isset($_POST['ultimo_id']) && isset($_POST['tipo'])) {

        try {
            $de = (int) decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
            $para = (int) $_POST['para'];
            $ultimo_id = (int) $_POST['ultimo_id'];

            // Busca apenas mensagens novas
            $sql = "SELECT " . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID, " . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, " . MENSAGENS_ATTB_DE . " as MENSAGENS_ATTB_DE, " . MENSAGENS_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS FROM " . TB_MENSAGENS . " 
            WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de) 
            ) AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
            ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

            if ($_POST['tipo'] == 2) {
                $sql = "SELECT tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " as MENSAGENS_ATTB_ID, tm." . MENSAGENS_USER_GRUPO_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " as MENSAGENS_ATTB_DE, tm." . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS, u." . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_MENSAGENS_USER_GRUPO . " tm LEFT JOIN " . TB_USER . " u ON u.id=tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " WHERE tm." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . "=:para AND tm." . MENSAGENS_USER_GRUPO_ATTB_ID . ">:ultimo_id";
            }

            $stmt = $pdo->prepare($sql);
            if ($_POST['tipo'] != 2)
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
        if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_FILES['anexos']) && isset($_POST['tipo'])) {
            try {

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

                if ($_POST['tipo'] == 2) {
                    $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")"
                        . "VALUES (:msg, :de, :para)";
                }
                // Query corrigida (usando corretamente as constantes)

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
            if (isset($_POST['id']) && ctype_digit($_POST['id']) && isset($_POST['tipo']) && ctype_digit($_POST['tipo'])) {
                try {
                    define("ID", $_POST['id']);
                    $sql = "SELECT u." . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_USER . " u where u." . USER_ATTB_ID . "=" . ID;
                    // if($_POST['tipo']==2){

                    // }
                    // include_once '../backend/JoseArturKassala.php'; // Arquivo de conexão
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ATTB_NOME = $dados["USER_ATTB_NOME"];

                    $ATTB_ID = criptografar(ID);
                    require_once '../pages/mensagem.php'; // Arquivo da mensagem
                } catch (Exception $e) {
                    echo "Houve um erro";
                } finally {

                    exit;
                }
            } else
                //fazer login
                if (isset($_POST["login"]) && isset($_POST["password"])) {
                    try {
                        $utilizador = $_POST["login"];

                        $stmt = $pdo->prepare("SELECT * FROM " . TB_USER . " WHERE " . USER_ATTB_NOME . "=:utilizador OR " . USER_ATTB_EMAIL . "=:utilizador");
                        $stmt->bindParam(':utilizador', $utilizador);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);

                            if (password_verify($_POST["password"], $row[USER_ATTB_PASSWORD])) {
                                $userID = $row[USER_ATTB_ID];
                                $stmt = $pdo->prepare("UPDATE " . TB_SESSOES . "  SET " . SESSOES_ATTB_ESTADO . " = 0 WHERE " . SESSOES_ATTB_ESTADO . "=1 AND " . SESSOES_ATTB_USER_ID . "=:user");
                                $stmt->bindParam(':user', $row[USER_ATTB_ID], PDO::PARAM_INT);

                                $stmt->execute();

                                // Query corrigida (usando corretamente as constantes)
                                $sql = "INSERT INTO " . TB_SESSOES . " (" . SESSOES_ATTB_USER_ID . ", " . SESSOES_ATTB_TEMPO . ")  
            VALUES (:SESSOES_ATTB_USER_ID, :SESSOES_ATTB_TEMPO)";
                                // Chama a função dataActual
                                $dataAtual = dataActual($pdo);
                                $tempo = date('Y-m-d H:i:s', strtotime($dataAtual . ' +5 minutes'));

                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':SESSOES_ATTB_USER_ID', $row[USER_ATTB_ID], PDO::PARAM_INT);
                                $stmt->bindParam(':SESSOES_ATTB_TEMPO', $tempo, PDO::PARAM_STR);
                                // $stmt->execute();

                                if ($stmt->execute()) {
                                    $_SESSION['SUDOCHAT_SESSAO_ID'] = criptografar($row[USER_ATTB_ID]);
                                    $_SESSION['USER_ATTB_NOME'] = criptografar($row[USER_ATTB_NOME]);
                                    $_SESSION['USER_ATTB_EMAIL'] = criptografar($row[USER_ATTB_EMAIL]);
                                    $_SESSION['USER_ATTB_FOTO'] = criptografar($row[USER_ATTB_FOTO]);
                                    $_SESSION['SUDOCHAT_SESSAO'] = criptografar($pdo->lastInsertId());
                                    // header("location: ../dashboard.php");
                                    $resp["status"] = "success";
                                    $resp["msg"] = "Login Efectuado com Successo";
                                    $resp["url"] = "../";
                                } else {
                                    $resp["status"] = "error";
                                    $resp["msg"] = "Erro ao registar a sessão";
                                }
                            } else {
                                $resp["status"] = "error";
                                $resp["msg"] = "Password errada";
                            }
                        } else {
                            $resp["status"] = "error";
                            $resp["msg"] = "Utilizador Não consta na nossa base de dados";
                            // echo "ere name errado";
                        }
                    } catch (PDOException $e) {
                        $resp["status"] = "error";
                        $resp["msg"] = "Houve um erro ao tentar fazer login: " . $e;
                        // echo json_encode(["erro" => "Erro ao salvar mensagem: " . $e->getMessage()]);
                    } finally {
                        echo json_encode($resp);
                        exit;
                    }
                }
                //pegar o id da ultima mensagem
                else if (isset($_POST["ultimoID_do"]) && isset($_POST["tipo"])) {

                    try {
                        $ultimoID = $_POST["ultimoID_do"];
                        $sql = "SELECT tm." . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID FROM " . TB_MENSAGENS . " tm WHERE tm." . MENSAGENS_ATTB_DE . " =:id_mensagem OR tm." . MENSAGENS_ATTB_PARA . " =:id_mensagem ORDER BY tm." . MENSAGENS_ATTB_ID . " DESC LIMIT 1";

                        if ($_POST["tipo"] == 2) {
                            $sql = "SELECT tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " as MENSAGENS_ATTB_ID FROM " . TB_MENSAGENS_USER_GRUPO . " tm WHERE tm." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . "=:id_mensagem ORDER BY tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " DESC LIMIT 1";
                        }

                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id_mensagem', $ultimoID, PDO::PARAM_INT);
                        if ($stmt->execute()) {
                            if ($stmt->rowCount() > 0) {
                                $resp["status"] = "success";
                                $resp["ultimoID"] = $stmt->fetch(PDO::FETCH_ASSOC)["MENSAGENS_ATTB_ID"];
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

                            if (isset($_SESSION['SUDOCHAT_SESSAO_ID']) && isset($_SESSION['SUDOCHAT_SESSAO'])) {
                                $id = decriptografar($_SESSION['SUDOCHAT_SESSAO']);
                                $user = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);

                                $stmt = $pdo->prepare("UPDATE " . TB_SESSOES . "  SET " . SESSOES_ATTB_ESTADO . " = false WHERE " . SESSOES_ATTB_ESTADO . "=true AND " . SESSOES_ATTB_ID . "=:id AND " . SESSOES_ATTB_USER_ID . "=:user");
                                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                                $stmt->bindParam(':user', $user, PDO::PARAM_INT);

                                $stmt->execute();
                                // Destroi a sessão
                                session_unset(); // Remove todas as variáveis da sessão
                                session_destroy(); // Destroi a sessão completamente
                            }

                            $resp["status"] = "success";
                            $resp["url"] = "/login";
                            $resp["msg"] = "Sessão terminada com sucesso";
                        } catch (Exception $e) {
                            $resp["status"] = "error";
                            $resp["msg"] = "Erro ao terminar sessão: " . $e->getMessage();
                        } finally {
                            echo json_encode($resp);
                            exit;
                        }
                    }
    $input = json_decode(file_get_contents("php://input"), true);
    if ($input["acao"] === "criarGrupo") {
        $nome = $input["nome"];
        $membros = $input["membros"];
        $administradores = $input["administradores"] ?? []; // Lista de administradores

        try {
            // Insere o grupo
            $stmt = $pdo->prepare("INSERT INTO " . TB_GRUPO . " (" . GRUPO_ATTB_NOME . ") VALUES (?)");
            $stmt->execute([$nome]);
            $grupoId = $pdo->lastInsertId();

            // Adiciona os membros ao grupo
            foreach ($membros as $membroId) {
                $isAdmin = in_array($membroId, $administradores) ? 1 : 0;
                $stmt = $pdo->prepare("INSERT INTO " . TB_GRUPO_MEMBROS . " (" . GRUPO_MEMBROS_ATTB_GRUPO . ", " . GRUPO_MEMBROS_ATTB_USER . ", " . GRUPO_MEMBROS_ATTB_ADMIN . ") VALUES (?, ?, ?)");
                $stmt->execute([$grupoId, $membroId, $isAdmin]);
            }

            $id = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);

            $stmt = $pdo->prepare("INSERT INTO " . TB_GRUPO_MEMBROS . " (" . GRUPO_MEMBROS_ATTB_GRUPO . ", " . GRUPO_MEMBROS_ATTB_USER . ", " . GRUPO_MEMBROS_ATTB_ADMIN . ") VALUES (?, ?, 1)");
            $stmt->execute([$grupoId, $id]);

            echo json_encode(["success" => true]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        } finally {
            exit;
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (validarLogin($pdo)) {
        //listar todos os utilizadores
        // Verifica se o ID do usuário foi passado na URL
        if (isset($_GET["usuarios"])) {
            try {
                $tipo = filter_var($_GET["usuarios"], FILTER_SANITIZE_NUMBER_INT);
                //             $sql = "SELECT " . USER_ATTB_ID . " as USER_ATTB_ID, " . USER_ATTB_NOME . " as USER_ATTB_NOME, IFNULL((SELECT 
                // 	case when ts." . SESSOES_ATTB_ESTADO . "=1 AND ts." . SESSOES_ATTB_TEMPO . " > NOW() then 1 ELSE 0 END 
                //  FROM " . TB_SESSOES . " ts WHERE ts." . SESSOES_ATTB_USER_ID . "=u.id ORDER BY ts." . SESSOES_ATTB_ID . " desc LIMIT 1), 0) sessao FROM " . TB_USER . " u WHERE " . USER_ATTB_ID . " != :id";

                $sql = "SELECT " . USER_ATTB_ID . " as USER_ATTB_ID, " . USER_ATTB_NOME . " as USER_ATTB_NOME, IFNULL((SELECT 
        case when ts." . SESSOES_ATTB_ESTADO . "=1 AND ts." . SESSOES_ATTB_TEMPO . " > NOW() then 1 ELSE 0 END 
     FROM " . TB_SESSOES . " ts WHERE ts." . SESSOES_ATTB_USER_ID . "=u.id ORDER BY ts." . SESSOES_ATTB_ID . " desc LIMIT 1), 0) sessao FROM " . TB_USER . " u WHERE " . USER_ATTB_ID . " != :id ORDER BY sessao DESC";

                if ($tipo == 1) {
                    $sql = "SELECT 
    CASE 
        WHEN tm." . MENSAGENS_ATTB_DE . " =:id THEN tm." . MENSAGENS_ATTB_PARA . " 
        ELSE tm." . MENSAGENS_ATTB_DE . " 
    END AS USER_ATTB_ID,
    u." . USER_ATTB_NOME . " AS USER_ATTB_NOME,
    tm." . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG,
    tm." . MENSAGENS_ATTB_CRIADO_AOS . " AS MENSAGENS_ATTB_CRIADO_AOS,
    tm." . MENSAGENS_ATTB_ESTADO . " AS MENSAGENS_ATTB_ESTADO,
    tm." . MENSAGENS_ATTB_ID . " AS MENSAGENS_ATTB_ID,
    IFNULL((SELECT 
		case when ts." . SESSOES_ATTB_ESTADO . "=1 AND ts." . SESSOES_ATTB_TEMPO . " > NOW() then 1 ELSE 0 END 
	 FROM " . TB_SESSOES . " ts WHERE ts." . SESSOES_ATTB_USER_ID . "=u.id ORDER BY ts." . SESSOES_ATTB_ID . " desc LIMIT 1), 0) sessao
FROM 
    " . TB_MENSAGENS . " tm
JOIN 
    " . TB_USER . " u ON (u.id = CASE 
                          WHEN tm." . MENSAGENS_ATTB_DE . " = :id THEN tm." . MENSAGENS_ATTB_PARA . " 
                          ELSE tm." . MENSAGENS_ATTB_DE . " 
                       END)
WHERE 
    (tm." . MENSAGENS_ATTB_DE . " = :id OR tm." . MENSAGENS_ATTB_PARA . " = :id)
    AND tm." . MENSAGENS_ATTB_CRIADO_AOS . " = (
        SELECT MAX(m2." . MENSAGENS_ATTB_CRIADO_AOS . ")
        FROM " . TB_MENSAGENS . " m2
        WHERE 
            (m2." . MENSAGENS_ATTB_DE . " = tm." . MENSAGENS_ATTB_DE . " AND m2." . MENSAGENS_ATTB_PARA . " = tm." . MENSAGENS_ATTB_PARA . ")
            OR 
            (m2." . MENSAGENS_ATTB_DE . " = tm." . MENSAGENS_ATTB_PARA . " AND m2." . MENSAGENS_ATTB_PARA . " = tm." . MENSAGENS_ATTB_DE . ")
    )
GROUP BY 
    " . USER_ATTB_ID . ", u.name, tm." . MENSAGENS_ATTB_MSG . ", tm." . MENSAGENS_ATTB_CRIADO_AOS . ", tm." . MENSAGENS_ATTB_ESTADO . ", tm." . MENSAGENS_ATTB_ID . "
ORDER BY 
    tm." . MENSAGENS_ATTB_CRIADO_AOS . " DESC;";
                } else if ($tipo == 2) {
                    $sql = "
    SELECT 
        tg." . GRUPO_ATTB_ID . " AS USER_ATTB_ID, 
        tg." . GRUPO_ATTB_NOME . " AS USER_ATTB_NOME, 
        ifnull(tmg." . MENSAGENS_USER_GRUPO_ATTB_MSG . ", '') AS MENSAGENS_ATTB_MSG,
        IFnull(tmg." . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . ", '') AS MENSAGENS_ATTB_CRIADO_AOS,
        ifnull(u." . USER_ATTB_NOME . ", '') AS sessao
    FROM " . TB_GRUPO . " tg 
    LEFT JOIN " . TB_GRUPO_MEMBROS . " tgm 
        ON tgm." . GRUPO_MEMBROS_ATTB_GRUPO . " = tg." . GRUPO_ATTB_ID . " 
        AND tgm." . GRUPO_MEMBROS_ATTB_USER . " = :id
    LEFT JOIN (
        SELECT 
            " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ", 
            " . MENSAGENS_USER_GRUPO_ATTB_MSG . ", 
            " . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . ", 
            " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . "
        FROM " . TB_MENSAGENS_USER_GRUPO . "
        WHERE (" . MENSAGENS_USER_GRUPO_ATTB_ID . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ") IN (
            SELECT MAX(" . MENSAGENS_USER_GRUPO_ATTB_ID . "), " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . "
            FROM " . TB_MENSAGENS_USER_GRUPO . "
            GROUP BY " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . "
        )
    ) tmg 
        ON tmg." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . " = tg." . GRUPO_ATTB_ID . "
    LEFT JOIN " . TB_USER . " u 
        ON u.id = tmg." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . "
    WHERE tgm." . GRUPO_MEMBROS_ATTB_GRUPO . " IS NOT NULL;
";
                }

                $stmt = $pdo->prepare($sql);
                $id = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($tipo == 1 || $tipo == 2) {
                    // Descriptografar as mensagens
                    foreach ($mensagens as &$mensagem) {
                        // Descriptografando a mensagem
                        $mensagem['MENSAGENS_ATTB_MSG'] = htmlspecialchars(decriptografar($mensagem[MENSAGENS_ATTB_MSG]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                        // $mensagem['MENSAGENS_ATTB_MSG'] = exibirCodigoSeguro(decriptografar($mensagem[MENSAGENS_ATTB_MSG]));
                    }
                }

                echo json_encode($mensagens);
            } catch (PDOException $e) {
                die("Erro na consulta: " . $e->getMessage());
            } finally {
                exit;
            }
        }
    }
    //verificar se a pessoa esta logada
    if (isset($_GET["isLoggedIn"])) {
        // sleep(15);
        try {

            if (isset($_SESSION['SUDOCHAT_SESSAO_ID']) && isset($_SESSION['SUDOCHAT_SESSAO'])) {
                $id = decriptografar($_SESSION['SUDOCHAT_SESSAO']);
                $user = decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
                $dataAtual = dataActual($pdo);
                $tempo = date('Y-m-d H:i:s', strtotime($dataAtual . ' +5 minutes'));

                $stmt = $pdo->prepare("UPDATE " . TB_SESSOES . "  SET " . SESSOES_ATTB_TEMPO . " = :tempo, " . SESSOES_ATTB_ESTADO . "=1 WHERE " . SESSOES_ATTB_ID . "=:id AND " . SESSOES_ATTB_USER_ID . "=:user");
                $stmt->bindParam(':tempo', $tempo, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':user', $user, PDO::PARAM_INT);
                if ($stmt->execute() && $stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $resp['SUDOCHAT_SESSAO_ID'] = $user;
                    $resp['SUDOCHAT_SESSAO_NOME'] = decriptografar($_SESSION['USER_ATTB_NOME']);
                    $resp['SUDOCHAT_SESSAO_EMAIL'] = decriptografar($_SESSION['USER_ATTB_EMAIL']);
                    $resp["status"] = "success";
                } else {
                    // Destroi a sessão
                    session_unset(); // Remove todas as variáveis da sessão
                    session_destroy(); // Destroi a sessão completamente
                }
            }

            $stmt = $pdo->prepare("UPDATE " . TB_SESSOES . "  SET " . SESSOES_ATTB_ESTADO . " = 0 WHERE " . SESSOES_ATTB_ESTADO . "=1 AND " . SESSOES_ATTB_TEMPO . "<NOW() ");
            $stmt->execute();

            $resp["status"] = isset($resp["status"]) ? $resp["status"] : "error";
        } catch (Exception $e) {
            $resp["status"] = "error";
            $resp["msg"] = $e;
            // Destroi a sessão
            session_unset(); // Remove todas as variáveis da sessão
            session_destroy(); // Destroi a sessão completamente
        } finally {
            echo json_encode($resp);
            exit;
        }
    }
}

// if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
// }