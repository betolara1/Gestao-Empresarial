<?php
include 'conexao.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Validar dados recebidos
    $mes = filter_input(INPUT_POST, 'mes', FILTER_SANITIZE_STRING);
    $ano = filter_input(INPUT_POST, 'ano', FILTER_SANITIZE_STRING);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $valor = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if (!$mes || !$ano || !$descricao || !$valor) {
        throw new Exception('Todos os campos são obrigatórios');
    }

    // Formatar a data
    $data = sprintf('%04d-%02d-01', $ano, $mes);

    // Preparar e executar a query
    $stmt = $conn->prepare("INSERT INTO despesas_fixas (descricao, valor, data) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $conn->error);
    }

    $stmt->bind_param("sds", $descricao, $valor, $data);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao salvar despesa: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Despesa salva com sucesso!'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>

    