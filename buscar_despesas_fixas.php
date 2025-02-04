<?php
include 'conexao.php';

header('Content-Type: application/json');

// Verifica se o número da proposta foi fornecido
if (!isset($_GET['numero_proposta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Número da proposta é obrigatório']);
    exit;
}

$numero_proposta = $_GET['numero_proposta'];

try {
    // Consulta SQL para buscar as despesas da proposta
    $sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $despesas = [];
    while ($row = $result->fetch_assoc()) {
        $despesas[] = [
            'id' => $row['id'],
            'nome_despesa' => $row['nome_despesa'],
            'valor' => number_format($row['valor'], 2, '.', '')
        ];
    }
    
    echo json_encode($despesas);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar despesas: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();