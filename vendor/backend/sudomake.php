<?php
require_once 'JoseArturKassala.php'; // Arquivo com a conexão ao banco de dados

if (($_SERVER["REQUEST_METHOD"] === "POST" && validarLogin($pdo)) || $_SERVER["REQUEST_METHOD"] === "POST" && !validarLogin($pdo) && isset($_POST['login']) && isset($_POST['password'])) {

    if (isset($_POST['para']) && isset($_POST['ultimo_id']) && isset($_POST['tipo'])) {
        try {
            $de = (int) decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
            $para = (int) $_POST['para'];
            $ultimo_id = (int) $_POST['ultimo_id'];
            $tipo = $_POST['tipo'];

            // Query para mensagens individuais
            $sql = "SELECT 
                " . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID, 
                " . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, 
                " . MENSAGENS_ATTB_DE . " as MENSAGENS_ATTB_DE, 
                " . MENSAGENS_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS,
                " . MENSAGENS_ATTB_APAGADA . " as MENSAGENS_ATTB_APAGADA,
                " . MENSAGENS_ATTB_APAGADA_POR . " as MENSAGENS_ATTB_APAGADA_POR,
                " . MENSAGENS_ATTB_APAGADA_EM . " as MENSAGENS_ATTB_APAGADA_EM,
                u1." . USER_ATTB_NOME . " as APAGADA_POR_NOME
            FROM " . TB_MENSAGENS . " m
            LEFT JOIN " . TB_USER . " u1 ON u1.id = m." . MENSAGENS_ATTB_APAGADA_POR . "
            WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) 
                OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de)) 
                AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
            ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

            // Query para mensagens de grupo
            if ($tipo == 2) {
                $sql = "SELECT 
                    tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " as MENSAGENS_ATTB_ID, 
                    tm." . MENSAGENS_USER_GRUPO_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, 
                    tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " as MENSAGENS_ATTB_DE, 
                    tm." . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS,
                    tm." . MENSAGENS_USER_GRUPO_ATTB_APAGADA . " as MENSAGENS_ATTB_APAGADA,
                    tm." . MENSAGENS_USER_GRUPO_ATTB_APAGADA_POR . " as MENSAGENS_ATTB_APAGADA_POR,
                    tm." . MENSAGENS_USER_GRUPO_ATTB_APAGADA_EM . " as MENSAGENS_ATTB_APAGADA_EM,
                    u." . USER_ATTB_NOME . " as USER_ATTB_NOME,
                    u2." . USER_ATTB_NOME . " as APAGADA_POR_NOME
                FROM " . TB_MENSAGENS_USER_GRUPO . " tm 
                LEFT JOIN " . TB_USER . " u 
                    ON u.id = tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " 
                LEFT JOIN " . TB_USER . " u2 
                    ON u2.id = tm." . MENSAGENS_USER_GRUPO_ATTB_APAGADA_POR . "
                WHERE tm." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . " = :para 
                    AND tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " > :ultimo_id
                ORDER BY tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " ASC";
            }

            $stmt = $pdo->prepare($sql);
            if ($tipo != 2) {
                $stmt->bindParam(':de', $de, PDO::PARAM_INT);
            }
            $stmt->bindParam(':para', $para, PDO::PARAM_INT);
            $stmt->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
            $stmt->execute();

            $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Busca os administradores do grupo (apenas para mensagens de grupo)
            $admins = [];
            if ($tipo == 2) {
                $sqlAdmins = "SELECT " . GRUPO_MEMBROS_ATTB_USER . " 
                          FROM " . TB_GRUPO_MEMBROS . " 
                          WHERE " . GRUPO_MEMBROS_ATTB_GRUPO . " = :grupo 
                          AND " . GRUPO_MEMBROS_ATTB_ADMIN . " = 1";
                $stmtAdmins = $pdo->prepare($sqlAdmins);
                $stmtAdmins->bindParam(':grupo', $para, PDO::PARAM_INT);
                $stmtAdmins->execute();
                $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN, 0);
            }

            // Processa as mensagens
            foreach ($mensagens as &$mensagem) {
                // Decodifica o JSON
                $mensagemData = json_decode($mensagem['MENSAGENS_ATTB_MSG'], true);

                // Descriptografa o texto, se existir e a mensagem não estiver apagada
                $mensagem['MENSAGENS_ATTB_MSG'] = !empty($mensagemData['texto']) && $mensagem['MENSAGENS_ATTB_APAGADA'] != 1
                    ? htmlspecialchars(decriptografar($mensagemData['texto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    : null;

                // Adiciona os anexos (somente se a mensagem não estiver apagada)
                $mensagem['ANEXOS'] = $mensagem['MENSAGENS_ATTB_APAGADA'] != 1 ? ($mensagemData['anexos'] ?? []) : [];

                // Adiciona a lista de administradores (apenas para mensagens de grupo)
                if ($tipo == 2) {
                    $mensagem['ADMINS'] = $admins;
                }
            }

            header('Content-Type: application/json');
            echo json_encode($mensagens);
        } catch (PDOException $e) {
            echo json_encode(["erro" => "Erro ao buscar mensagens: " . $e->getMessage()]);
        } finally {
            exit;
        }
    }

    // Pegar todas as mensagens enviadas e recebidas
    // Pegar todas as mensagens enviadas e recebidas
    // if (isset($_POST['para']) && isset($_POST['ultimo_id']) && isset($_POST['tipo'])) {
    //     try {
    //         $de = (int) decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
    //         $para = (int) $_POST['para'];
    //         $ultimo_id = (int) $_POST['ultimo_id'];
    //         $tipo = $_POST['tipo'];

    //         // Query para mensagens individuais
    //         $sql = "SELECT 
    //             " . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID, 
    //             " . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, 
    //             " . MENSAGENS_ATTB_DE . " as MENSAGENS_ATTB_DE, 
    //             " . MENSAGENS_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS 
    //         FROM " . TB_MENSAGENS . " 
    //         WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) 
    //             OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de)) 
    //             AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
    //         ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

    //         // Query para mensagens de grupo
    //         if ($tipo == 2) {
    //             $sql = "SELECT 
    //                 tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " as MENSAGENS_ATTB_ID, 
    //                 tm." . MENSAGENS_USER_GRUPO_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, 
    //                 tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " as MENSAGENS_ATTB_DE, 
    //                 tm." . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS, 
    //                 u." . USER_ATTB_NOME . " as USER_ATTB_NOME 
    //             FROM " . TB_MENSAGENS_USER_GRUPO . " tm 
    //             LEFT JOIN " . TB_USER . " u 
    //                 ON u.id = tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " 
    //             WHERE tm." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . " = :para 
    //                 AND tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " > :ultimo_id
    //             ORDER BY tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " ASC";
    //         }

    //         $stmt = $pdo->prepare($sql);
    //         if ($tipo != 2) {
    //             $stmt->bindParam(':de', $de, PDO::PARAM_INT);
    //         }
    //         $stmt->bindParam(':para', $para, PDO::PARAM_INT);
    //         $stmt->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
    //         $stmt->execute();

    //         $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //         // Busca os administradores do grupo (apenas para mensagens de grupo)
    //         $admins = [];
    //         if ($tipo == 2) {
    //             $sqlAdmins = "SELECT " . GRUPO_MEMBROS_ATTB_USER . " 
    //                       FROM " . TB_GRUPO_MEMBROS . " 
    //                       WHERE " . GRUPO_MEMBROS_ATTB_GRUPO . " = :grupo 
    //                       AND " . GRUPO_MEMBROS_ATTB_ADMIN . " = 1";
    //             $stmtAdmins = $pdo->prepare($sqlAdmins);
    //             $stmtAdmins->bindParam(':grupo', $para, PDO::PARAM_INT);
    //             $stmtAdmins->execute();
    //             $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN, 0); // Array com IDs dos administradores
    //         }

    //         // Processa as mensagens
    //         foreach ($mensagens as &$mensagem) {
    //             // Decodifica o JSON
    //             $mensagemData = json_decode($mensagem['MENSAGENS_ATTB_MSG'], true);

    //             // Descriptografa o texto, se existir
    //             $mensagem['MENSAGENS_ATTB_MSG'] = !empty($mensagemData['texto'])
    //                 ? htmlspecialchars(decriptografar($mensagemData['texto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
    //                 : null;

    //             // Adiciona os anexos
    //             $mensagem['ANEXOS'] = $mensagemData['anexos'] ?? [];

    //             // Adiciona a lista de administradores (apenas para mensagens de grupo)
    //             if ($tipo == 2) {
    //                 $mensagem['ADMINS'] = $admins;
    //             }
    //         }

    //         header('Content-Type: application/json');
    //         echo json_encode($mensagens);
    //     } catch (PDOException $e) {
    //         echo json_encode(["erro" => "Erro ao buscar mensagens: " . $e->getMessage()]);
    //     } finally {
    //         exit;
    //     }
    // } 
    else
        // Apagar mensagem
        if (isset($_POST['acao']) && $_POST['acao'] === 'apagar_mensagem' && isset($_POST['mensagem_id']) && isset($_POST['tipo'])) {
            try {
                $mensagemId = filter_var($_POST['mensagem_id'], FILTER_SANITIZE_NUMBER_INT);
                $tipo = $_POST['tipo'];
                $logado = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);
                $dataHora = date('Y-m-d H:i:s'); // Data e hora atual

                if ($tipo == 2) {
                    // Mensagem de grupo
                    $sql = "SELECT " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ", " . MENSAGENS_USER_GRUPO_ATTB_APAGADA . " 
                    FROM " . TB_MENSAGENS_USER_GRUPO . " 
                    WHERE " . MENSAGENS_USER_GRUPO_ATTB_ID . " = :mensagem_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':mensagem_id', $mensagemId, PDO::PARAM_INT);
                    $stmt->execute();
                    $mensagem = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$mensagem) {
                        throw new Exception("Mensagem não encontrada.");
                    }

                    if ($mensagem[MENSAGENS_USER_GRUPO_ATTB_APAGADA] == 1) {
                        throw new Exception("Esta mensagem já foi apagada.");
                    }

                    $emissor = $mensagem[MENSAGENS_USER_GRUPO_ATTB_MEMBRO];
                    $grupo = $mensagem[MENSAGENS_USER_GRUPO_ATTB_GRUPO];

                    // Verifica se o usuário é administrador do grupo
                    $sqlAdmin = "SELECT " . GRUPO_MEMBROS_ATTB_ADMIN . " 
                         FROM " . TB_GRUPO_MEMBROS . " 
                         WHERE " . GRUPO_MEMBROS_ATTB_GRUPO . " = :grupo 
                         AND " . GRUPO_MEMBROS_ATTB_USER . " = :logado";
                    $stmtAdmin = $pdo->prepare($sqlAdmin);
                    $stmtAdmin->bindParam(':grupo', $grupo, PDO::PARAM_INT);
                    $stmtAdmin->bindParam(':logado', $logado, PDO::PARAM_INT);
                    $stmtAdmin->execute();
                    $isAdmin = $stmtAdmin->fetchColumn();

                    if ($emissor != $logado && !$isAdmin) {
                        throw new Exception("Você não tem permissão para apagar esta mensagem.");
                    }

                    // Marca a mensagem como apagada e registra quem a apagou
                    $sqlUpdate = "UPDATE " . TB_MENSAGENS_USER_GRUPO . " 
                          SET " . MENSAGENS_USER_GRUPO_ATTB_APAGADA . " = 1, 
                              " . MENSAGENS_USER_GRUPO_ATTB_APAGADA_POR . " = :apagada_por, 
                              " . MENSAGENS_USER_GRUPO_ATTB_APAGADA_EM . " = :apagada_em 
                          WHERE " . MENSAGENS_USER_GRUPO_ATTB_ID . " = :mensagem_id";
                } else {
                    // Mensagem individual
                    $sql = "SELECT " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_APAGADA . " 
                    FROM " . TB_MENSAGENS . " 
                    WHERE " . MENSAGENS_ATTB_ID . " = :mensagem_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':mensagem_id', $mensagemId, PDO::PARAM_INT);
                    $stmt->execute();
                    $mensagem = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$mensagem) {
                        throw new Exception("Mensagem não encontrada.");
                    }

                    if ($mensagem[MENSAGENS_ATTB_APAGADA] == 1) {
                        throw new Exception("Esta mensagem já foi apagada.");
                    }

                    $emissor = $mensagem[MENSAGENS_ATTB_DE];
                    if ($emissor != $logado) {
                        throw new Exception("Você não tem permissão para apagar esta mensagem.");
                    }

                    // Marca a mensagem como apagada e registra quem a apagou
                    $sqlUpdate = "UPDATE " . TB_MENSAGENS . " 
                          SET " . MENSAGENS_ATTB_APAGADA . " = 1, 
                              " . MENSAGENS_ATTB_APAGADA_POR . " = :apagada_por, 
                              " . MENSAGENS_ATTB_APAGADA_EM . " = :apagada_em 
                          WHERE " . MENSAGENS_ATTB_ID . " = :mensagem_id";
                }

                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':apagada_por', $logado, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':apagada_em', $dataHora, PDO::PARAM_STR);
                $stmtUpdate->bindParam(':mensagem_id', $mensagemId, PDO::PARAM_INT);
                if ($stmtUpdate->execute()) {
                    $resp["status"] = "success";
                    $resp["msg"] = "Mensagem apagada com sucesso.";
                } else {
                    throw new Exception("Erro ao apagar a mensagem.");
                }
            } catch (Exception $e) {
                $resp["status"] = "error";
                $resp["msg"] = "Erro ao apagar mensagem: " . $e->getMessage();
            } finally {
                echo json_encode($resp);
                exit;
            }
        }
        // if (isset($_POST['acao']) && $_POST['acao'] === 'apagar_mensagem' && isset($_POST['mensagem_id']) && isset($_POST['tipo'])) {
        //     try {
        //         $mensagemId = filter_var($_POST['mensagem_id'], FILTER_SANITIZE_NUMBER_INT);
        //         $tipo = $_POST['tipo'];
        //         $logado = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);

        //         if ($tipo == 2) {
        //             // Mensagem de grupo
        //             $sql = "SELECT " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . " 
        //             FROM " . TB_MENSAGENS_USER_GRUPO . " 
        //             WHERE " . MENSAGENS_USER_GRUPO_ATTB_ID . " = :mensagem_id";
        //             $stmt = $pdo->prepare($sql);
        //             $stmt->bindParam(':mensagem_id', $mensagemId, PDO::PARAM_INT);
        //             $stmt->execute();
        //             $mensagem = $stmt->fetch(PDO::FETCH_ASSOC);

        //             if (!$mensagem) {
        //                 throw new Exception("Mensagem não encontrada.");
        //             }

        //             $emissor = $mensagem[MENSAGENS_USER_GRUPO_ATTB_MEMBRO];
        //             $grupo = $mensagem[MENSAGENS_USER_GRUPO_ATTB_GRUPO];

        //             // Verifica se o usuário é administrador do grupo
        //             $sqlAdmin = "SELECT " . GRUPO_MEMBROS_ATTB_ADMIN . " 
        //                  FROM " . TB_GRUPO_MEMBROS . " 
        //                  WHERE " . GRUPO_MEMBROS_ATTB_GRUPO . " = :grupo 
        //                  AND " . GRUPO_MEMBROS_ATTB_USER . " = :logado";
        //             $stmtAdmin = $pdo->prepare($sqlAdmin);
        //             $stmtAdmin->bindParam(':grupo', $grupo, PDO::PARAM_INT);
        //             $stmtAdmin->bindParam(':logado', $logado, PDO::PARAM_INT);
        //             $stmtAdmin->execute();
        //             $isAdmin = $stmtAdmin->fetchColumn();

        //             if ($emissor != $logado && !$isAdmin) {
        //                 throw new Exception("Você não tem permissão para apagar esta mensagem.");
        //             }

        //             // Apaga a mensagem
        //             $sqlDelete = "DELETE FROM " . TB_MENSAGENS_USER_GRUPO . " 
        //                   WHERE " . MENSAGENS_USER_GRUPO_ATTB_ID . " = :mensagem_id";
        //         } else {
        //             // Mensagem individual
        //             $sql = "SELECT " . MENSAGENS_ATTB_DE . " 
        //             FROM " . TB_MENSAGENS . " 
        //             WHERE " . MENSAGENS_ATTB_ID . " = :mensagem_id";
        //             $stmt = $pdo->prepare($sql);
        //             $stmt->bindParam(':mensagem_id', $mensagemId, PDO::PARAM_INT);
        //             $stmt->execute();
        //             $mensagem = $stmt->fetch(PDO::FETCH_ASSOC);

        //             if (!$mensagem) {
        //                 throw new Exception("Mensagem não encontrada.");
        //             }

        //             $emissor = $mensagem[MENSAGENS_ATTB_DE];
        //             if ($emissor != $logado) {
        //                 throw new Exception("Você não tem permissão para apagar esta mensagem.");
        //             }

        //             // Apaga a mensagem
        //             $sqlDelete = "DELETE FROM " . TB_MENSAGENS . " 
        //                   WHERE " . MENSAGENS_ATTB_ID . " = :mensagem_id";
        //         }

        //         $stmtDelete = $pdo->prepare($sqlDelete);
        //         $stmtDelete->bindParam(':mensagem_id', $mensagemId, PDO::PARAM_INT);
        //         if ($stmtDelete->execute()) {
        //             $resp["status"] = "success";
        //             $resp["msg"] = "Mensagem apagada com sucesso.";
        //         } else {
        //             throw new Exception("Erro ao apagar a mensagem.");
        //         }
        //     } catch (Exception $e) {
        //         $resp["status"] = "error";
        //         $resp["msg"] = "Erro ao apagar mensagem: " . $e->getMessage();
        //     } finally {
        //         echo json_encode($resp);
        //         exit;
        //     }
        // }
        // if (isset($_POST['para']) && isset($_POST['ultimo_id']) && isset($_POST['tipo'])) {
        //     try {

        //         $de = (int) decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
        //         $para = (int) $_POST['para'];
        //         $ultimo_id = (int) $_POST['ultimo_id'];
        //         $tipo = $_POST['tipo'];

        //         // Query para mensagens individuais
        //         $sql = "SELECT 
        //                 " . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID, 
        //                 " . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, 
        //                 " . MENSAGENS_ATTB_DE . " as MENSAGENS_ATTB_DE, 
        //                 " . MENSAGENS_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS 
        //             FROM " . TB_MENSAGENS . " 
        //             WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) 
        //                 OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de)) 
        //                 AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
        //             ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

        //         // Query para mensagens de grupo
        //         if ($tipo == 2) {
        //             $sql = "SELECT 
        //                     tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " as MENSAGENS_ATTB_ID, 
        //                     tm." . MENSAGENS_USER_GRUPO_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, 
        //                     tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " as MENSAGENS_ATTB_DE, 
        //                     tm." . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS, 
        //                     u." . USER_ATTB_NOME . " as USER_ATTB_NOME 
        //                 FROM " . TB_MENSAGENS_USER_GRUPO . " tm 
        //                 LEFT JOIN " . TB_USER . " u 
        //                     ON u.id = tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " 
        //                 WHERE tm." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . " = :para 
        //                     AND tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " > :ultimo_id
        //                 ORDER BY tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " ASC";
        //         }

        //         $stmt = $pdo->prepare($sql);
        //         if ($tipo != 2) {
        //             $stmt->bindParam(':de', $de, PDO::PARAM_INT);
        //         }
        //         $stmt->bindParam(':para', $para, PDO::PARAM_INT);
        //         $stmt->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
        //         $stmt->execute();

        //         $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //         // Processa as mensagens
        //         foreach ($mensagens as &$mensagem) {
        //             // Decodifica o JSON
        //             $mensagemData = json_decode($mensagem['MENSAGENS_ATTB_MSG'], true);

        //             // Descriptografa o texto, se existir
        //             $mensagem['MENSAGENS_ATTB_MSG'] = !empty($mensagemData['texto'])
        //                 ? htmlspecialchars(decriptografar($mensagemData['texto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        //                 : null;

        //             // Adiciona os anexos
        //             $mensagem['ANEXOS'] = $mensagemData['anexos'] ?? [];
        //         }

        //         header('Content-Type: application/json');
        //         echo json_encode($mensagens);
        //     } catch (PDOException $e) {
        //         echo json_encode(["erro" => "Erro ao buscar mensagens: " . $e->getMessage()]);
        //     } finally {
        //         exit;
        //     }
        // }
        //pegar todas as mensagens enviadas e recebidas
        // if (isset($_POST['para']) && isset($_POST['ultimo_id']) && isset($_POST['tipo'])) {

        //     try {
        //         $de = (int) decriptografar($_SESSION['SUDOCHAT_SESSAO_ID']);
        //         $para = (int) $_POST['para'];
        //         $ultimo_id = (int) $_POST['ultimo_id'];

        //         // Busca apenas mensagens novas
        //         $sql = "SELECT " . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID, " . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, " . MENSAGENS_ATTB_DE . " as MENSAGENS_ATTB_DE, " . MENSAGENS_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS FROM " . TB_MENSAGENS . " 
        //         WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de) 
        //         ) AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
        //         ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

        //         if ($_POST['tipo'] == 2) {
        //             $sql = "SELECT tm." . MENSAGENS_USER_GRUPO_ATTB_ID . " as MENSAGENS_ATTB_ID, tm." . MENSAGENS_USER_GRUPO_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " as MENSAGENS_ATTB_DE, tm." . MENSAGENS_USER_GRUPO_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS, u." . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_MENSAGENS_USER_GRUPO . " tm LEFT JOIN " . TB_USER . " u ON u.id=tm." . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . " WHERE tm." . MENSAGENS_USER_GRUPO_ATTB_GRUPO . "=:para AND tm." . MENSAGENS_USER_GRUPO_ATTB_ID . ">:ultimo_id";
        //         }

        //         $stmt = $pdo->prepare($sql);
        //         if ($_POST['tipo'] != 2)
        //             $stmt->bindParam(':de', $de, PDO::PARAM_INT);

        //         $stmt->bindParam(':para', $para, PDO::PARAM_INT);
        //         $stmt->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
        //         $stmt->execute();

        //         $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //         // Descriptografar as mensagens
        //         foreach ($mensagens as &$mensagem) {
        //             // Descriptografando a mensagem
        //             $mensagem['MENSAGENS_ATTB_MSG'] = htmlspecialchars(decriptografar($mensagem[MENSAGENS_ATTB_MSG]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        //             // $mensagem['MENSAGENS_ATTB_MSG'] = exibirCodigoSeguro(decriptografar($mensagem[MENSAGENS_ATTB_MSG]));
        //         }

        //         header('Content-Type: application/json');
        //         echo json_encode($mensagens);
        //     } catch (PDOException $e) {
        //         echo json_encode(["erro" => "Erro ao buscar mensagens: " . $e->getMessage()]);
        //     } finally {
        //         exit;
        //     }
        // }
        // Enviar mensagem
        else
        if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_POST['tipo'])) {
            try {
                // Verifica se há mensagem ou anexos
                $mensagemTexto = trim($_POST['mensagem']);
                $temAnexos = !empty($_FILES['anexos']['name'][0]);
                if (empty($mensagemTexto) && !$temAnexos) {
                    $resp["status"] = "error";
                    $resp["msg"] = "A mensagem ou anexos não podem estar vazios";
                    echo json_encode($resp);
                    exit;
                }

                // Dados do emissor e receptor
                $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);
                $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT);
                $tipo = $_POST['tipo'];

                // Lista de extensões permitidas
                $extensoesPermitidas = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'bmp',
                    'webp',
                    'mp3',
                    'aac',
                    'm4a',
                    'ogg',
                    'flac',
                    'wav',
                    'opus',
                    'mp4',
                    'webm',
                    'mov',
                    'm4v',
                    'xlsx',
                    'xls',
                    'xlsm',
                    'xlsb',
                    'xltx',
                    'xltm',
                    'csv',
                    'docx',
                    'doc',
                    'docm',
                    'dotx',
                    'dotm',
                    'rtf',
                    'pptx',
                    'ppt',
                    'pptm',
                    'potx',
                    'potm',
                    'ppsx',
                    'ppsm',
                    'pps',
                    'pdf',
                    '7z',
                    'rar',
                    'zip'
                ];

                // Limite de tamanho do arquivo (500 MB)
                $maxFileSize = 500 * 1024 * 1024; // 500 MB em bytes

                // Processa os anexos, se houver
                $anexosData = [];
                if ($temAnexos) {
                    $uploadDir = "../uploads/";
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $files = $_FILES['anexos'];
                    for ($i = 0; $i < count($files['name']); $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            if ($files['size'][$i] > $maxFileSize) {
                                throw new Exception("Arquivo muito grande: " . $files['name'][$i] . ". O limite é 500 MB.");
                            }

                            $nomeOriginalCompleto = basename($files['name'][$i]);
                            $nomeOriginal = pathinfo($nomeOriginalCompleto, PATHINFO_FILENAME);
                            $extensao = strtolower(pathinfo($nomeOriginalCompleto, PATHINFO_EXTENSION));

                            $isPermitido = in_array($extensao, $extensoesPermitidas);
                            $nomeUnico = uniqid() . "_" . $nomeOriginalCompleto;
                            $filePath = $uploadDir . $nomeUnico;

                            if (!$isPermitido) {
                                $zipPath = $uploadDir . uniqid() . "_converted.zip";
                                $zip = new ZipArchive();
                                if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                    $zip->addFile($files['tmp_name'][$i], $nomeOriginalCompleto);
                                    $zip->close();
                                    $filePath = $zipPath;
                                    $nomeOriginal = pathinfo($nomeOriginalCompleto, PATHINFO_FILENAME);
                                    $extensao = "zip";
                                } else {
                                    throw new Exception("Erro ao criar o arquivo ZIP para: " . $nomeOriginalCompleto);
                                }
                            } else {
                                if (!move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                                    throw new Exception("Erro ao mover o arquivo: " . $nomeOriginalCompleto);
                                }
                            }

                            $anexosData[] = [
                                "caminho" => "uploads/" . basename($filePath),
                                "nome_original" => $nomeOriginal
                            ];
                        } else {
                            throw new Exception("Erro no upload do arquivo: " . $files['error'][$i]);
                        }
                    }
                }

                // Cria o objeto JSON com a mensagem e os anexos
                $mensagemData = [
                    "texto" => !empty($mensagemTexto) ? criptografar($mensagemTexto) : null,
                    "anexos" => $anexosData
                ];
                $mensagemJson = json_encode($mensagemData);

                // Insere a mensagem
                if ($tipo == 2) {
                    $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")
                    VALUES (:msg, :de, :para)";
                } else {
                    $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
                    VALUES (:msg, :de, :para)";
                }

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':msg', $mensagemJson, PDO::PARAM_STR);
                $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
                $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $resp["status"] = "success";
                    $resp["msg"] = "Mensagem e/ou anexos enviados com sucesso";
                } else {
                    throw new PDOException("Erro ao salvar mensagem");
                }
            } catch (Exception $e) {
                $resp["status"] = "error";
                $resp["msg"] = "Erro ao enviar: " . $e->getMessage();
            } finally {
                echo json_encode($resp);
                exit;
            }
        }
        // Enviar mensagem
        // Enviar mensagem
        // if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_POST['tipo'])) {
        //     try {
        //         // Verifica se há mensagem ou anexos
        //         $mensagemTexto = trim($_POST['mensagem']);
        //         $temAnexos = !empty($_FILES['anexos']['name'][0]);
        //         if (empty($mensagemTexto) && !$temAnexos) {
        //             $resp["status"] = "error";
        //             $resp["msg"] = "A mensagem ou anexos não podem estar vazios";
        //             echo json_encode($resp);
        //             exit;
        //         }

        //         // Dados do emissor e receptor
        //         $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);
        //         $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT);
        //         $tipo = $_POST['tipo'];

        //         // Lista de extensões permitidas
        //         $extensoesPermitidas = [
        //             'jpg',
        //             'jpeg',
        //             'png',
        //             'gif',
        //             'bmp',
        //             'webp',
        //             'mp3',
        //             'aac',
        //             'm4a',
        //             'ogg',
        //             'flac',
        //             'wav',
        //             'opus',
        //             'mp4',
        //             'webm',
        //             'mov',
        //             'm4v',
        //             'xlsx',
        //             'xls',
        //             'xlsm',
        //             'xlsb',
        //             'xltx',
        //             'xltm',
        //             'csv',
        //             'docx',
        //             'doc',
        //             'docm',
        //             'dotx',
        //             'dotm',
        //             'rtf',
        //             'pptx',
        //             'ppt',
        //             'pptm',
        //             'potx',
        //             'potm',
        //             'ppsx',
        //             'ppsm',
        //             'pps',
        //             'pdf',
        //             '7z',
        //             'rar',
        //             'zip'
        //         ];

        //         // Limite de tamanho do arquivo (500 MB)
        //         $maxFileSize = 500 * 1024 * 1024; // 500 MB em bytes

        //         // Processa os anexos, se houver
        //         $anexosData = [];
        //         if ($temAnexos) {
        //             $uploadDir = "../uploads/";
        //             if (!is_dir($uploadDir)) {
        //                 mkdir($uploadDir, 0755, true);
        //             }

        //             $files = $_FILES['anexos'];
        //             for ($i = 0; $i < count($files['name']); $i++) {
        //                 if ($files['error'][$i] === UPLOAD_ERR_OK) {
        //                     if ($files['size'][$i] > $maxFileSize) {
        //                         throw new Exception("Arquivo muito grande: " . $files['name'][$i] . ". O limite é 500 MB.");
        //                     }

        //                     $nomeOriginalCompleto = basename($files['name'][$i]);
        //                     $nomeOriginal = pathinfo($nomeOriginalCompleto, PATHINFO_FILENAME);
        //                     $extensao = strtolower(pathinfo($nomeOriginalCompleto, PATHINFO_EXTENSION));

        //                     $isPermitido = in_array($extensao, $extensoesPermitidas);
        //                     $nomeUnico = uniqid() . "_" . $nomeOriginalCompleto;
        //                     $filePath = $uploadDir . $nomeUnico;

        //                     if (!$isPermitido) {
        //                         $zipPath = $uploadDir . uniqid() . "_converted.zip";
        //                         $zip = new ZipArchive();
        //                         if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        //                             $zip->addFile($files['tmp_name'][$i], $nomeOriginalCompleto);
        //                             $zip->close();
        //                             $filePath = $zipPath;
        //                             $nomeOriginal = pathinfo($nomeOriginalCompleto, PATHINFO_FILENAME);
        //                             $extensao = "zip";
        //                         } else {
        //                             throw new Exception("Erro ao criar o arquivo ZIP para: " . $nomeOriginalCompleto);
        //                         }
        //                     } else {
        //                         if (!move_uploaded_file($files['tmp_name'][$i], $filePath)) {
        //                             throw new Exception("Erro ao mover o arquivo: " . $nomeOriginalCompleto);
        //                         }
        //                     }

        //                     $anexosData[] = [
        //                         "caminho" => "uploads/" . basename($filePath),
        //                         "nome_original" => $nomeOriginal
        //                     ];
        //                 } else {
        //                     throw new Exception("Erro no upload do arquivo: " . $files['error'][$i]);
        //                 }
        //             }
        //         }

        //         // Cria o objeto JSON com a mensagem e os anexos
        //         $mensagemData = [
        //             "texto" => !empty($mensagemTexto) ? criptografar($mensagemTexto) : null,
        //             "anexos" => $anexosData
        //         ];
        //         $mensagemJson = json_encode($mensagemData);

        //         // Insere a mensagem
        //         if ($tipo == 2) {
        //             $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")
        //         VALUES (:msg, :de, :para)";
        //         } else {
        //             $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
        //         VALUES (:msg, :de, :para)";
        //         }

        //         $stmt = $pdo->prepare($sql);
        //         $stmt->bindParam(':msg', $mensagemJson, PDO::PARAM_STR);
        //         $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
        //         $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);

        //         if ($stmt->execute()) {
        //             $resp["status"] = "success";
        //             $resp["msg"] = "Mensagem e/ou anexos enviados com sucesso";
        //         } else {
        //             throw new PDOException("Erro ao salvar mensagem");
        //         }
        //     } catch (Exception $e) {
        //         $resp["status"] = "error";
        //         $resp["msg"] = "Erro ao enviar: " . $e->getMessage();
        //     } finally {
        //         echo json_encode($resp);
        //         exit;
        //     }
        // }
        // if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_POST['tipo'])) {
        //     try {

        //         // Verifica se há mensagem ou anexos
        //         $mensagemTexto = trim($_POST['mensagem']);
        //         $temAnexos = !empty($_FILES['anexos']['name'][0]);
        //         if (empty($mensagemTexto) && !$temAnexos) {
        //             $resp["status"] = "error";
        //             $resp["msg"] = "A mensagem ou anexos não podem estar vazios";
        //             echo json_encode($resp);
        //             exit;
        //         }

        //         // Dados do emissor e receptor
        //         $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);
        //         $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT);
        //         $tipo = $_POST['tipo'];

        //         // Lista de extensões permitidas
        //         $extensoesPermitidas = [
        //             // Imagens
        //             'jpg',
        //             'jpeg',
        //             'png',
        //             'gif',
        //             'bmp',
        //             'webp',
        //             // Músicas
        //             'mp3',
        //             'aac',
        //             'm4a',
        //             'ogg',
        //             'flac',
        //             'wav',
        //             'opus',
        //             // Vídeos
        //             'mp4',
        //             'webm',
        //             'mov',
        //             'm4v',
        //             // Arquivos do Office
        //             'xlsx',
        //             'xls',
        //             'xlsm',
        //             'xlsb',
        //             'xltx',
        //             'xltm',
        //             'csv', // Excel
        //             'docx',
        //             'doc',
        //             'docm',
        //             'dotx',
        //             'dotm',
        //             'rtf', // Word
        //             'pptx',
        //             'ppt',
        //             'pptm',
        //             'potx',
        //             'potm',
        //             'ppsx',
        //             'ppsm',
        //             'pps', // PowerPoint
        //             // Outros formatos comuns
        //             'pdf',
        //             '7z',
        //             'rar',
        //             'zip'
        //         ];

        //         // Processa os anexos, se houver
        //         $anexosData = [];
        //         if ($temAnexos) {
        //             // Diretório onde os arquivos serão salvos
        //             $uploadDir = "../uploads/";
        //             if (!is_dir($uploadDir)) {
        //                 mkdir($uploadDir, 0755, true); // Cria o diretório se não existir
        //             }

        //             $files = $_FILES['anexos'];

        //             // Loop para processar cada arquivo
        //             for ($i = 0; $i < count($files['name']); $i++) {
        //                 if ($files['error'][$i] === UPLOAD_ERR_OK) {
        //                     // Nome original do arquivo (sem extensão)
        //                     $nomeOriginalCompleto = basename($files['name'][$i]);
        //                     $nomeOriginal = pathinfo($nomeOriginalCompleto, PATHINFO_FILENAME); // Remove a extensão
        //                     $extensao = strtolower(pathinfo($nomeOriginalCompleto, PATHINFO_EXTENSION));

        //                     // Verifica se a extensão é permitida
        //                     $isPermitido = in_array($extensao, $extensoesPermitidas);

        //                     // Gera um nome único para o arquivo
        //                     $nomeUnico = uniqid() . "_" . $nomeOriginalCompleto;
        //                     $filePath = $uploadDir . $nomeUnico;

        //                     if (!$isPermitido) {
        //                         // Se a extensão não for permitida, converte o arquivo para .zip
        //                         $zipPath = $uploadDir . uniqid() . "_converted.zip";
        //                         $zip = new ZipArchive();
        //                         if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        //                             $zip->addFile($files['tmp_name'][$i], $nomeOriginalCompleto);
        //                             $zip->close();

        //                             // Atualiza o caminho e o nome original para o arquivo .zip
        //                             $filePath = $zipPath;
        //                             $nomeOriginal = pathinfo($nomeOriginalCompleto, PATHINFO_FILENAME); // Remove a extensão
        //                             $extensao = "zip";
        //                         } else {
        //                             throw new Exception("Erro ao criar o arquivo ZIP para: " . $nomeOriginalCompleto);
        //                         }
        //                     } else {
        //                         // Se for permitido, apenas move o arquivo
        //                         if (!move_uploaded_file($files['tmp_name'][$i], $filePath)) {
        //                             throw new Exception("Erro ao mover o arquivo: " . $nomeOriginalCompleto);
        //                         }
        //                     }

        //                     // Armazena o caminho relativo (sem "vendor/") e o nome original (sem extensão)
        //                     $anexosData[] = [
        //                         "caminho" => "uploads/" . basename($filePath),
        //                         "nome_original" => $nomeOriginal
        //                     ];
        //                 } else {
        //                     throw new Exception("Erro no upload do arquivo: " . $files['error'][$i]);
        //                 }
        //             }
        //         }

        //         // Cria o objeto JSON com a mensagem e os anexos
        //         $mensagemData = [
        //             "texto" => !empty($mensagemTexto) ? criptografar($mensagemTexto) : null,
        //             "anexos" => $anexosData
        //         ];
        //         $mensagemJson = json_encode($mensagemData);

        //         // Insere a mensagem
        //         if ($tipo == 2) {
        //             $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")
        //             VALUES (:msg, :de, :para)";
        //         } else {
        //             $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
        //             VALUES (:msg, :de, :para)";
        //         }

        //         $stmt = $pdo->prepare($sql);
        //         $stmt->bindParam(':msg', $mensagemJson, PDO::PARAM_STR);
        //         $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
        //         $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);

        //         if ($stmt->execute()) {
        //             $resp["status"] = "success";
        //             $resp["msg"] = "Mensagem e/ou anexos enviados com sucesso";
        //         } else {
        //             throw new PDOException("Erro ao salvar mensagem");
        //         }
        //     } catch (Exception $e) {
        //         $resp["status"] = "error";
        //         $resp["msg"] = "Erro ao enviar: " . $e->getMessage();
        //     } finally {
        //         echo json_encode($resp);
        //         exit;
        //     }
        // }
        // if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_POST['tipo'])) {
        //         try {

        //             // Verifica se há mensagem ou anexos
        //             $mensagemTexto = trim($_POST['mensagem']);
        //             $temAnexos = !empty($_FILES['anexos']['name'][0]);
        //             if (empty($mensagemTexto) && !$temAnexos) {
        //                 $resp["status"] = "error";
        //                 $resp["msg"] = "A mensagem ou anexos não podem estar vazios";
        //                 echo json_encode($resp);
        //                 exit;
        //             }

        //             // Dados do emissor e receptor
        //             $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);
        //             $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT);
        //             $tipo = $_POST['tipo'];

        //             // Processa os anexos, se houver
        //             $anexosPaths = [];
        //             if ($temAnexos) {
        //                 // Diretório onde os arquivos serão salvos
        //                 $uploadDir = "../uploads/";
        //                 if (!is_dir($uploadDir)) {
        //                     mkdir($uploadDir, 0755, true); // Cria o diretório se não existir
        //                 }

        //                 $files = $_FILES['anexos'];

        //                 // Loop para processar cada arquivo
        //                 for ($i = 0; $i < count($files['name']); $i++) {
        //                     if ($files['error'][$i] === UPLOAD_ERR_OK) {
        //                         // Gera um nome único para o arquivo
        //                         $fileName = uniqid() . "_" . basename($files['name'][$i]);
        //                         $filePath = $uploadDir . $fileName;

        //                         // Move o arquivo para o diretório
        //                         if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
        //                             $anexosPaths[] = $filePath;
        //                         } else {
        //                             throw new Exception("Erro ao mover o arquivo: " . $files['name'][$i]);
        //                         }
        //                     } else {
        //                         throw new Exception("Erro no upload do arquivo: " . $files['error'][$i]);
        //                     }
        //                 }
        //             }

        //             // Cria o objeto JSON com a mensagem e os anexos
        //             $mensagemData = [
        //                 "texto" => !empty($mensagemTexto) ? criptografar($mensagemTexto) : null,
        //                 "anexos" => $anexosPaths
        //             ];
        //             $mensagemJson = json_encode($mensagemData);

        //             // Insere a mensagem
        //             if ($tipo == 2) {
        //                 $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")
        //                     VALUES (:msg, :de, :para)";
        //             } else {
        //                 $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
        //                     VALUES (:msg, :de, :para)";
        //             }

        //             $stmt = $pdo->prepare($sql);
        //             $stmt->bindParam(':msg', $mensagemJson, PDO::PARAM_STR);
        //             $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
        //             $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);

        //             if ($stmt->execute()) {
        //                 $resp["status"] = "success";
        //                 $resp["msg"] = "Mensagem e/ou anexos enviados com sucesso";
        //             } else {
        //                 throw new PDOException("Erro ao salvar mensagem");
        //             }
        //         } catch (Exception $e) {
        //             $resp["status"] = "error";
        //             $resp["msg"] = "Erro ao enviar: " . $e->getMessage();
        //         } finally {
        //             echo json_encode($resp);
        //             exit;
        //         }
        //     }
        // if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_POST['tipo'])) {
        //         try {

        //             // Verifica se há mensagem ou anexos
        //             $mensagemTexto = trim($_POST['mensagem']);
        //             $temAnexos = !empty($_FILES['anexos']['name'][0]);
        //             if (empty($mensagemTexto) && !$temAnexos) {
        //                 $resp["status"] = "error";
        //                 $resp["msg"] = "A mensagem ou anexos não podem estar vazios";
        //                 echo json_encode($resp);
        //                 exit;
        //             }

        //             // Dados do emissor e receptor
        //             $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT);
        //             $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT);
        //             $tipo = $_POST['tipo'];

        //             // Criptografa a mensagem, se existir
        //             $mensagemCriptografada = !empty($mensagemTexto) ? criptografar($mensagemTexto) : null;

        //             // Insere a mensagem (se houver texto)
        //             $mensagemId = null;
        //             if (!empty($mensagemTexto)) {
        //                 // Define a query com base no tipo (individual ou grupo)
        //                 if ($tipo == 2) {
        //                     $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")
        //                         VALUES (:msg, :de, :para)";
        //                 } else {
        //                     $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
        //                         VALUES (:msg, :de, :para)";
        //                 }

        //                 $stmt = $pdo->prepare($sql);
        //                 $stmt->bindParam(':msg', $mensagemCriptografada, PDO::PARAM_STR);
        //                 $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
        //                 $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);

        //                 if ($stmt->execute()) {
        //                     $mensagemId = $pdo->lastInsertId(); // Obtém o ID da mensagem inserida
        //                 } else {
        //                     throw new PDOException("Erro ao salvar mensagem");
        //                 }
        //             }

        //             // Processa os anexos, se houver
        //             if ($temAnexos) {
        //                 // Diretório onde os arquivos serão salvos
        //                 $uploadDir = "../uploads/";
        //                 if (!is_dir($uploadDir)) {
        //                     mkdir($uploadDir, 0755, true); // Cria o diretório se não existir
        //                 }

        //                 $anexosPaths = [];
        //                 $files = $_FILES['anexos'];

        //                 // Loop para processar cada arquivo
        //                 for ($i = 0; $i < count($files['name']); $i++) {
        //                     if ($files['error'][$i] === UPLOAD_ERR_OK) {
        //                         // Gera um nome único para o arquivo
        //                         $fileName = uniqid() . "_" . basename($files['name'][$i]);
        //                         $filePath = $uploadDir . $fileName;

        //                         // Move o arquivo para o diretório
        //                         if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
        //                             $anexosPaths[] = $filePath;
        //                         } else {
        //                             throw new Exception("Erro ao mover o arquivo: " . $files['name'][$i]);
        //                         }
        //                     } else {
        //                         throw new Exception("Erro no upload do arquivo: " . $files['error'][$i]);
        //                     }
        //                 }

        //                 // Insere os caminhos dos anexos no banco de dados
        //                 foreach ($anexosPaths as $path) {
        //                     $sqlAnexos = "INSERT INTO " . TB_MENSAGENS_ARQUIVOS . " (" . MENSAGENS_ARQUIVOS_ATTB_ANEXOS . ", " . MENSAGENS_ARQUIVOS_ATTB_DE . ", " . MENSAGENS_ARQUIVOS_ATTB_PARA . ")
        //                               VALUES (:anexo, :de, :para)";
        //                     $stmtAnexos = $pdo->prepare($sqlAnexos);
        //                     $stmtAnexos->bindParam(':anexo', $path, PDO::PARAM_STR);
        //                     $stmtAnexos->bindParam(':de', $emissor, PDO::PARAM_INT);
        //                     $stmtAnexos->bindParam(':para', $receptor, PDO::PARAM_INT);

        //                     if (!$stmtAnexos->execute()) {
        //                         throw new PDOException("Erro ao salvar o caminho do anexo");
        //                     }
        //                 }
        //             }

        //             // Resposta de sucesso
        //             $resp["status"] = "success";
        //             $resp["msg"] = "Mensagem e/ou anexos enviados com sucesso";
        //         } catch (Exception $e) {
        //             $resp["status"] = "error";
        //             $resp["msg"] = "Erro ao enviar: " . $e->getMessage();
        //         } finally {
        //             echo json_encode($resp);
        //             exit;
        //         }
        //     }
        // if (isset($_POST['mensagem']) && isset($_POST['receptor']) && isset($_FILES['anexos']) && isset($_POST['tipo'])) {
        //     try {

        //         if (empty($_FILES['anexos']['name'][0]) && empty($_POST['mensagem'])) {
        //             $resp["status"] = "error";
        //             $resp["msg"] = "A mensagem não pode ser vazia";
        //             echo json_encode($resp);

        //             exit;
        //         }

        //         $mensagem = criptografar(trim($_POST['mensagem'])); // Remover espaços em branco extras
        //         $anexos = trim($_POST['mensagem']); // Remover espaços em branco extras
        //         $emissor = filter_var(decriptografar($_SESSION["SUDOCHAT_SESSAO_ID"]), FILTER_SANITIZE_NUMBER_INT); // ID do usuário que enviou a mensagem
        //         $receptor = filter_var(decriptografar($_POST['receptor']), FILTER_SANITIZE_NUMBER_INT); // ID do usuário que recebe a mensagem

        //         // Query corrigida (usando corretamente as constantes)
        //         $sql = "INSERT INTO " . TB_MENSAGENS . " (" . MENSAGENS_ATTB_MSG . ", " . MENSAGENS_ATTB_DE . ", " . MENSAGENS_ATTB_PARA . ")  
        //     VALUES (:msg, :de, :para)";

        //         if ($_POST['tipo'] == 2) {
        //             $sql = "INSERT INTO " . TB_MENSAGENS_USER_GRUPO . " (" . MENSAGENS_USER_GRUPO_ATTB_MSG . ", " . MENSAGENS_USER_GRUPO_ATTB_MEMBRO . ", " . MENSAGENS_USER_GRUPO_ATTB_GRUPO . ")"
        //                 . "VALUES (:msg, :de, :para)";
        //         }
        //         // Query corrigida (usando corretamente as constantes)

        //         $stmt = $pdo->prepare($sql);
        //         $stmt->bindParam(':de', $emissor, PDO::PARAM_INT);
        //         $stmt->bindParam(':para', $receptor, PDO::PARAM_INT);
        //         $stmt->bindParam(':msg', $mensagem, PDO::PARAM_STR);
        //         // $stmt->execute();

        //         if ($stmt->execute()) {
        //             $resp["status"] = "success";
        //             $resp["msg"] = "Registado com Sucesso";
        //         } else {
        //             $resp["status"] = "error";
        //             $resp["msg"] = "Mensagem não enviada";
        //         }
        //     } catch (PDOException $e) {
        //         $resp["status"] = "error";
        //         $resp["msg"] = "Mensagem não enviada";
        //         // echo json_encode(["erro" => "Erro ao salvar mensagem: " . $e->getMessage()]);
        //     } finally {
        //         echo json_encode($resp);
        //         exit;
        //     }
        // } 
        //abrir a janela de mensagens
        else
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
                if ($stmt->execute()) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $resp['SUDOCHAT_SESSAO_ID'] = $user;
                    $resp['SUDOCHAT_SESSAO_NOME'] = decriptografar($_SESSION['USER_ATTB_NOME']);
                    $resp['SUDOCHAT_SESSAO_EMAIL'] = decriptografar($_SESSION['USER_ATTB_EMAIL']);
                    $resp["status"] = "success";
                } else {
                    // Destroi a sessão
                    $resp["status"] = "error";
                    $resp["msg"] = "Sem sessao";
                }
            } else {
                $resp["status"] = "error";
                $resp["msg"] = "Sem sessao";
            }


            // $resp["status"] = isset($resp["status"]) ? $resp["status"] : "error";
        } catch (Exception $e) {
            $resp["status"] = "error";
            $resp["msg"] = $e;
            // Destroi a sessão
            // session_unset(); // Remove todas as variáveis da sessão
            // session_destroy(); // Destroi a sessão completamente
        } finally {
            if (!isset($resp["status"]) && $resp["status"] == "error") {
                $resp["status"] = "error";
                $resp["msg"] = "Sem sessao";
                $stmt = $pdo->prepare("UPDATE " . TB_SESSOES . "  SET " . SESSOES_ATTB_ESTADO . " = 0 WHERE " . SESSOES_ATTB_ESTADO . "=1 AND " . SESSOES_ATTB_TEMPO . "<NOW() ");
                if ($stmt->execute()) {

                    session_unset(); // Remove todas as variáveis da sessão
                    session_destroy(); // Destroi a sessão completamente
                }
            }

            echo json_encode($resp);
            exit;
        }
    }
}

// Busca apenas mensagens novas
// $sql = "SELECT " . MENSAGENS_ATTB_ID . " as MENSAGENS_ATTB_ID, " . MENSAGENS_ATTB_MSG . " AS MENSAGENS_ATTB_MSG, " . MENSAGENS_ATTB_DE . " as MENSAGENS_ATTB_DE, " . MENSAGENS_ATTB_CRIADO_AOS . " as MENSAGENS_ATTB_CRIADO_AOS FROM " . TB_MENSAGENS . " 
//             WHERE ((" . MENSAGENS_ATTB_DE . " = :de AND " . MENSAGENS_ATTB_PARA . " = :para) OR (" . MENSAGENS_ATTB_DE . " = :para AND " . MENSAGENS_ATTB_PARA . " = :de) 
//             ) AND " . MENSAGENS_ATTB_ID . " > :ultimo_id 
//             ORDER BY " . MENSAGENS_ATTB_ID . " ASC";

// echo $sql;
// if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
// }