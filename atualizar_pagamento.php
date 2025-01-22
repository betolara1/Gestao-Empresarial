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

        // Obtenha a última parcela paga
        $sql_ultima_parcela = "SELECT dia_pagamento FROM pagamentos 
                                WHERE numero_proposta = ? AND status_pagamento = 'Pago' 
                                ORDER BY dia_pagamento DESC LIMIT 1";
        $stmt_ultima_parcela = $conn->prepare($sql_ultima_parcela);
        $stmt_ultima_parcela->bind_param("i", $numero_proposta);
        $stmt_ultima_parcela->execute();
        $result_ultima_parcela = $stmt_ultima_parcela->get_result();
        $ultima_parcela = $result_ultima_parcela->fetch_assoc();

        if ($ultima_parcela) {
            // Se houver uma última parcela paga, calcule a próxima data
            $data_ultima_parcela = $ultima_parcela['dia_pagamento'];
            $nova_data_pagamento = date('d/m/Y', strtotime("+1 month", strtotime($data_ultima_parcela))); // Adiciona 1 mês
        } else {
            // Se não houver parcelas pagas, defina uma data padrão ou lógica alternativa
            $nova_data_pagamento = date('d/m/Y'); // Ou outra lógica
        }

        $response = [
            'success' => true,
            'total_pago' => $total_pago,
            'total_pendente' => $total_pendente,
            'proximo_pagamento' => $nova_data_pagamento
        ];
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
