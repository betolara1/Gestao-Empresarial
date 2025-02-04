<?php
include 'conexao.php';

header('Content-Type: application/json');

$mes = isset($_GET['mes']) ? $_GET['mes'] : null;
$ano = isset($_GET['ano']) ? $_GET['ano'] : null;

if (!$mes || !$ano) {
    http_response_code(400);
    echo json_encode(['error' => 'Mês e ano são obrigatórios']);
    exit;
}

try {
    // Validar os parâmetros
    if (!preg_match("/^(0[1-9]|1[0-2])$/", $mes)) {
        throw new Exception("Mês inválido");
    }
    if (!preg_match("/^\d{4}$/", $ano)) {
        throw new Exception("Ano inválido");
    }

    // Usando MONTH() e YEAR() para extrair mês e ano do campo data
    $sql = "SELECT id, descricao, valor FROM despesas_fixas 
            WHERE MONTH(data) = ? AND YEAR(data) = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $mes, $ano);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro na execução da query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $despesas = [];
    while ($row = $result->fetch_assoc()) {
        // Converter o valor para float antes de formatar
        $valor = floatval($row['valor']);
        $despesas[] = [
            'id' => intval($row['id']), // Garantir que id seja número
            'descricao' => $row['descricao'],
            'valor' => number_format($valor, 2, ',', '.')
        ];
    }

    // Garantir que a resposta seja JSON válido
    $response = json_encode($despesas, JSON_UNESCAPED_UNICODE);
    
    if ($response === false) {
        throw new Exception("Erro ao converter para JSON: " . json_last_error_msg());
    }
    
    echo $response;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao buscar despesas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>