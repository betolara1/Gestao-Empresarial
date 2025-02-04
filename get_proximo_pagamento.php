<?php
include 'conexao.php';

header('Content-Type: application/json');

if (!isset($_GET['numero_proposta'])) {
    echo json_encode(['success' => false, 'message' => 'Número da proposta não fornecido']);
    exit;
}

$numero_proposta = intval($_GET['numero_proposta']);

try {
    // Busca a próxima data de pagamento em aberto
    $sql = "SELECT MIN(data_pagamento) as proxima_data 
            FROM pagamentos 
            WHERE numero_proposta = ? 
            AND status_pagamento = 'Aberto'
            ORDER BY parcela_num ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();
    $proximo = $result->fetch_assoc();

    if ($proximo && !empty($proximo['proxima_data']) && $proximo['proxima_data'] != '0000-00-00') {
        $proximo_pagamento = date('d/m/Y', strtotime($proximo['proxima_data']));
        echo json_encode([
            'success' => true,
            'proximo_pagamento' => $proximo_pagamento
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'proximo_pagamento' => null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close(); 