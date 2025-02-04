<?php
include 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = intval($_POST['id']);

        // Corrigido o nome da tabela de 'despesas' para 'despesas_fixas'
        $sql = "DELETE FROM despesas_fixas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . $conn->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Despesa excluída com sucesso!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Despesa não encontrada.']);
            }
        } else {
            throw new Exception("Erro ao executar a query: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir despesa: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido ou ID não fornecido.']);
}

$conn->close();
?>