<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Verifica se o número da proposta foi fornecido
    if (!isset($_GET['numero_proposta']) || empty($_GET['numero_proposta'])) {
        throw new Exception('Número da proposta não fornecido');
    }

    $proposta = intval($_GET['numero_proposta']);
    
    // Consulta SQL usando o nome correto da coluna (proposta)
    $sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $proposta);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $despesas = array();
    
    while ($row = $result->fetch_assoc()) {
        $despesas[] = array(
            'id' => $row['id'],
            'nome_despesa' => htmlspecialchars($row['nome_despesa']),
            'valor' => number_format($row['valor'], 2, '.', '')
        );
    }
    
    // Debug
    error_log("Proposta: " . $proposta);
    error_log("SQL: " . $sql);
    error_log("Resultados: " . json_encode($despesas));
    
    echo json_encode($despesas);

} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>