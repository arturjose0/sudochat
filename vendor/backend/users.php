<?php



function select()
{
    include 'config.php'; // Arquivo de conexão

    try {
        $stmt = $pdo->prepare("SELECT " . USER_ATTB_ID . " as USER_ATTB_ID, " . USER_ATTB_NOME . " as USER_ATTB_NOME FROM " . TB_USER);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erro na consulta: " . $e->getMessage());
    }
}

// print_r(select());
echo json_encode(select());
