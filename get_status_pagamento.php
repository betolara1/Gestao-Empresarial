<?php
include 'conexao.php';

header('Content-Type: application/json');

if (!isset($_GET['numero_proposta'])) {
    echo json_encode(['success' => false, 'message' => 'Número da proposta não fornecido']);
    exit;
}

$numero_proposta = intval($_GET['numero_proposta']);

try {
    // Verifica se existe alguma parcela em aberto
    $sql = "SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM pagamentos 
                    WHERE numero_proposta = ? 
                    AND status_pagamento = 'Aberto'
                ) THEN 'EM ABERTO'
                ELSE 'FINALIZADO'
            END AS status_pagamento";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();
    $status = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'status' => $status['status_pagamento']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close(); 