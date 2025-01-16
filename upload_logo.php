<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_FILES['logo'])) {
        throw new Exception('Nenhum arquivo enviado');
    }

    $file = $_FILES['logo'];
    
    // Validações
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande (máximo 2MB)');
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipo de arquivo não permitido');
    }

    // Processa a imagem
    $imageData = file_get_contents($file['tmp_name']);
    
    // Limpa logos antigas
    $conn->query("DELETE FROM logos");
    
    // Insere nova logo
    $stmt = $conn->prepare("INSERT INTO logos (image_data) VALUES (?)");
    $stmt->bind_param("s", $imageData);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao salvar a logo');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 