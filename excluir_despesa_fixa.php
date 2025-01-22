<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Lógica para excluir a despesa
    $sql = "DELETE FROM despesas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Se a exclusão for bem-sucedida
        echo json_encode(['success' => true, 'message' => 'Despesa excluída com sucesso!']);
    } else {
        // Se houver um erro na exclusão
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir despesa.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido ou ID não fornecido.']);
}

$conn->close();
?>