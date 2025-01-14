<?php
include 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_despesa = $_POST['id_despesa'];
    
    $sql = "DELETE FROM despesas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_despesa);
    
    try {
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Registro excluído com sucesso!'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao excluir: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro: ' . $e->getMessage()
        ]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido'
    ]);
}
?>