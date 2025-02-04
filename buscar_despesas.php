<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Verifica se mês e ano foram fornecidos
    if (!isset($_GET['mes']) || !isset($_GET['ano'])) {
        throw new Exception('Mês e ano são obrigatórios');
    }

    $mes = intval($_GET['mes']);
    $ano = intval($_GET['ano']);
    
    // Consulta SQL para buscar despesas fixas do mês/ano específico
    $sql = "SELECT id, descricao, valor 
            FROM despesas_fixas 
            WHERE MONTH(data) = ? 
            AND YEAR(data) = ?
            ORDER BY data";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $mes, $ano);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $despesas = array();
    
    while ($row = $result->fetch_assoc()) {
        $despesas[] = array(
            'id' => $row['id'],
            'descricao' => htmlspecialchars($row['descricao']),
            'valor' => number_format($row['valor'], 2, ',', '.') // Formatação BR
        );
    }
    
    // Debug
    error_log("Mês: " . $mes . ", Ano: " . $ano);
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