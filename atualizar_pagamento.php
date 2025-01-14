<?php
include 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_proposta = isset($_POST['numero_proposta']) ? intval($_POST['numero_proposta']) : 0;
    $parcela_num = isset($_POST['parcela_num']) ? intval($_POST['parcela_num']) : 0;
    $valor_parcela = isset($_POST['valor_parcela']) ? floatval($_POST['valor_parcela']) : 0;

    try {
        // Atualiza o status da parcela para "Pago"
        $sql_update = "UPDATE pagamentos 
                       SET status_pagamento = 'Pago', valor_pago = valor_parcela, valor_pagar = 0 
                       WHERE numero_proposta = ? AND parcela_num = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $numero_proposta, $parcela_num);
        $stmt_update->execute();

        // Recalcula valores pagos e a pagar
        $sql_totais = "SELECT 
                            SUM(CASE WHEN status_pagamento = 'Pago' THEN valor_parcela ELSE 0 END) AS total_pago,
                            SUM(CASE WHEN status_pagamento = 'Aberto' THEN valor_parcela ELSE 0 END) AS total_pendente
                       FROM pagamentos 
                       WHERE numero_proposta = ?";
        $stmt_totais = $conn->prepare($sql_totais);
        $stmt_totais->bind_param("i", $numero_proposta);
        $stmt_totais->execute();
        $result_totais = $stmt_totais->get_result()->fetch_assoc();

        $total_pago = $result_totais['total_pago'];
        $total_pendente = $result_totais['total_pendente'];

        echo json_encode([
            'success' => true,
            'total_pago' => number_format($total_pago, 2, '.', ''),
            'total_pendente' => number_format($total_pendente, 2, '.', ''),
            'parcela_num' => $parcela_num
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
