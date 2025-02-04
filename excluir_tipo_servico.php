<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('ID não fornecido');
    }

    $id = intval($_POST['id']);
    
    $conn->begin_transaction();
    
    // Remove registros dependentes
    $stmt = $conn->prepare("DELETE FROM servico_tipo_servico WHERE tipo_servico_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Remove o tipo de serviço
    $stmt = $conn->prepare("DELETE FROM tipos_servicos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erro ao excluir serviço');
    }
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) $stmt->close();
$conn->close();
?>