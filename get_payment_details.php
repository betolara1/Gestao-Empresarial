<?php
include 'conexao.php';

header('Content-Type: application/json');

if (!isset($_GET['numero_proposta'])) {
    echo json_encode(['success' => false, 'message' => 'Número da proposta não fornecido']);
    exit;
}

$numero_proposta = $_GET['numero_proposta'];

$sql = "SELECT parcela_num, valor_parcela, data_pagamento, status_pagamento 
        FROM pagamentos 
        WHERE numero_proposta = ?
        ORDER BY parcela_num";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $numero_proposta);
$stmt->execute();
$result = $stmt->get_result();

$parcelas = [];
while ($row = $result->fetch_assoc()) {
    $parcelas[] = [
        'parcela_num' => $row['parcela_num'],
        'valor_parcela' => number_format($row['valor_parcela'], 2, ',', '.'),
        'dia_pagamento' => date('d/m/Y', strtotime($row['data_pagamento'])),
        'status_pagamento' => $row['status_pagamento']
    ];
}

echo json_encode(['success' => true, 'parcelas' => $parcelas]);

$stmt->close();
$conn->close();

