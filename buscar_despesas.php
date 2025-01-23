<?php
include 'conexao.php';

header('Content-Type: application/json');

$numero_proposta = isset($_GET['numero_proposta']) ? (int)$_GET['numero_proposta'] : 0;

try {
    $sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();

    $despesas = [];
    while ($row = $result->fetch_assoc()) {
        $row['valor'] = number_format($row['valor'], 2, ',', '.');
        $despesas[] = $row;
    }

    echo json_encode($despesas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar despesas: ' . $e->getMessage()]);
}

$conn->close();
?>