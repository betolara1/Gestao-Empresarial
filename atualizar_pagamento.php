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

        // Busca o próximo pagamento (primeira parcela em aberto)
        $sql_proximo = "SELECT data_pagamento 
                        FROM pagamentos 
                        WHERE numero_proposta = ? 
                        AND status_pagamento = 'Aberto'
                        ORDER BY parcela_num ASC
                        LIMIT 1";
        
        $stmt_proximo = $conn->prepare($sql_proximo);
        $stmt_proximo->bind_param("i", $numero_proposta);
        $stmt_proximo->execute();
        $result_proximo = $stmt_proximo->get_result();
        $proximo = $result_proximo->fetch_assoc();

        // Tratamento da data do próximo pagamento
        $proximo_pagamento = null;
        if ($proximo && isset($proximo['data_pagamento'])) {
            $data = DateTime::createFromFormat('Y-m-d', $proximo['data_pagamento']);
            if ($data !== false) {
                $proximo_pagamento = $data->format('d/m/Y');
            }
        }

        // Se ainda houver valor pendente mas não tiver próximo pagamento, 
        // significa que precisamos verificar se há parcelas em aberto
        if ($total_pendente > 0 && $proximo_pagamento === null) {
            $sql_check_parcelas = "SELECT COUNT(*) as total_parcelas_abertas 
                                  FROM pagamentos 
                                  WHERE numero_proposta = ? 
                                  AND status_pagamento = 'Aberto'";
            $stmt_check = $conn->prepare($sql_check_parcelas);
            $stmt_check->bind_param("i", $numero_proposta);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result()->fetch_assoc();
            
            if ($result_check['total_parcelas_abertas'] > 0) {
                // Se houver parcelas em aberto, mas não temos uma data válida,
                // vamos usar a data atual + 1 mês como próximo pagamento
                $proximo_pagamento = date('d/m/Y', strtotime('+1 month'));
            }
        }

        $response = [
            'success' => true,
            'total_pago' => $total_pago,
            'total_pendente' => $total_pendente,
            'proximo_pagamento' => $proximo_pagamento
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
