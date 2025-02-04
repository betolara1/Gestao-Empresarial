<?php
session_start();
require_once 'conexao.php';

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Método não permitido']));
}

// Verifica se os parâmetros necessários existem
if (!isset($_POST['card_id']) || !isset($_POST['is_favorito'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Parâmetros inválidos']));
}

// Obtém e valida os dados
$usuario_id = $_SESSION['usuario_id'] ?? 1; // Use 1 como padrão ou ajuste conforme sua lógica
$card_id = $_POST['card_id'];
$is_favorito = $_POST['is_favorito'] === 'true';

try {
    if ($is_favorito) {
        // Verifica se já existe
        $check = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND card_id = ?");
        $check->bind_param("is", $usuario_id, $card_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows === 0) {
            // Obtém a próxima ordem
            $stmt = $conn->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 as proxima_ordem FROM favoritos WHERE usuario_id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $ordem = $stmt->get_result()->fetch_assoc()['proxima_ordem'];
            
            // Insere novo favorito
            $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, card_id, ordem) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $usuario_id, $card_id, $ordem);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Card adicionado aos favoritos']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Card já está nos favoritos']);
        }
    } else {
        // Remove dos favoritos
        $stmt = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND card_id = ?");
        $stmt->bind_param("is", $usuario_id, $card_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Card removido dos favoritos']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Card não estava nos favoritos']);
        }
    }
    
} catch (Exception $e) {
    error_log("Erro ao atualizar favorito: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar favorito: ' . $e->getMessage()]);
} 