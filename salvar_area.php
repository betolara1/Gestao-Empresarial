<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['nome']) || empty($_POST['nome'])) {
        throw new Exception('Nome da área é obrigatório');
    }

    $nome = trim($_POST['nome']);
    
    $stmt = $conn->prepare("INSERT INTO areas_atuacao (nome) VALUES (?)");
    $stmt->bind_param("s", $nome);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Área cadastrada com sucesso!'
        ]);
    } else {
        throw new Exception('Erro ao cadastrar área');
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