<?php
include 'conexao.php';

// Verifica se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

try {
    // Inicia a transação
    $conn->begin_transaction();

    // Primeiro exclui as retiradas associadas
    $sql_retiradas = "DELETE FROM retiradas_socios WHERE socio_id = ?";
    $stmt = $conn->prepare($sql_retiradas);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Depois exclui o sócio
    $sql_socio = "DELETE FROM socios WHERE id = ?";
    $stmt = $conn->prepare($sql_socio);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Confirma as alterações
    $conn->commit();

    echo "<script>
            alert('Sócio excluído com sucesso!');
            window.location.href = 'gerenciar_empresa.php';
          </script>";

} catch (Exception $e) {
    // Em caso de erro, desfaz as alterações
    $conn->rollback();
    echo "<script>
            alert('Erro ao excluir sócio: " . $e->getMessage() . "');
            window.location.href = 'gerenciar_empresa.php';
          </script>";
}

$conn->close();
?>