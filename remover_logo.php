<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Remove todas as logos
    $stmt = $conn->prepare("DELETE FROM logos");
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao remover a logo');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 