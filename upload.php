<?php
session_start();
require_once 'conexao.php'; // Arquivo com as configurações do banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    
    // Verificar se é uma imagem válida
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
        exit;
    }
    
    // Limitar tamanho do arquivo (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Arquivo muito grande']);
        exit;
    }
    
    try {
        $imageData = file_get_contents($file['tmp_name']);
        
        $stmt = $conn->prepare("INSERT INTO logos (image_data) VALUES (?)");
        $stmt->execute([$imageData]);
        
        echo json_encode(['success' => true, 'message' => 'Logo atualizada com sucesso']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar a imagem']);
    }
}