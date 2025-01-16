<?php
session_start();
include 'conexao.php';

// Recebe e decodifica os dados JSON
$dados = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'] ?? 1; // Ajuste conforme seu sistema
$card_id = $dados['card_id'];
$favorito = $dados['favorito'];

try {
    if ($favorito) {
        // Adiciona aos favoritos
        $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, card_id, ordem) 
                              SELECT ?, ?, COALESCE(MAX(ordem), 0) + 1 
                              FROM favoritos WHERE usuario_id = ?");
        $stmt->bind_param("isi", $usuario_id, $card_id, $usuario_id);
    } else {
        // Remove dos favoritos
        $stmt = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND card_id = ?");
        $stmt->bind_param("is", $usuario_id, $card_id);
    }

    $success = $stmt->execute();
    echo json_encode(['success' => $success]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 