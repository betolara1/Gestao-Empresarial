<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['tipo_servico']) || empty($_POST['tipo_servico'])) {
        throw new Exception('Tipo de serviço é obrigatório');
    }

    $tipo = trim($_POST['tipo_servico']);
    
    $stmt = $conn->prepare("INSERT INTO tipos_servicos (tipo_servico) VALUES (?)");
    $stmt->bind_param("s", $tipo);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Serviço cadastrado com sucesso!'
        ]);
    } else {
        throw new Exception('Erro ao cadastrar serviço');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>