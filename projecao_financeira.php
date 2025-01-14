<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');
    $mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
    
    // Total de entrada para o período selecionado
    $sqlTotalEntrada = "
        SELECT COALESCE(SUM(valor_total), 0) AS total_entrada 
        FROM servicos 
        WHERE YEAR(data_inicio) = ? 
        AND MONTH(data_inicio) = ? ";
    $stmtTotalEntrada = $conn->prepare($sqlTotalEntrada);
    $stmtTotalEntrada->bind_param("ii", $ano, $mes);
    $stmtTotalEntrada->execute();
    $totalEntrada = $stmtTotalEntrada->get_result()->fetch_assoc()['total_entrada'];

    // Total recebido no período
    $sqlTotalRecebido = "
        SELECT COALESCE(SUM(p.valor_parcela), 0) AS total_recebido
        FROM pagamentos p
        WHERE YEAR(p.data_pagamento) = ?
        AND MONTH(p.data_pagamento) = ?
        AND p.status_pagamento = 'Pago'";
    $stmtTotalRecebido = $conn->prepare($sqlTotalRecebido);
    $stmtTotalRecebido->bind_param("ii", $ano, $mes);
    $stmtTotalRecebido->execute();
    $totalRecebido = $stmtTotalRecebido->get_result()->fetch_assoc()['total_recebido'];

    // Despesas fixas do período
    $sqlDespesasFixas = "
        SELECT COALESCE(SUM(valor), 0) AS total 
        FROM despesas_fixas 
        WHERE YEAR(data) = ? 
        AND MONTH(data) = ?";
    $stmtDespesasFixas = $conn->prepare($sqlDespesasFixas);
    $stmtDespesasFixas->bind_param("ii", $ano, $mes);
    $stmtDespesasFixas->execute();
    $totalDespesasFixas = $stmtDespesasFixas->get_result()->fetch_assoc()['total'];

    // Parcelas em aberto do período
    $sqlParcelasAberto = "
        SELECT COALESCE(SUM(valor_parcela), 0) AS total_aberto 
        FROM pagamentos 
        WHERE YEAR(data_pagamento) = ?
        AND MONTH(data_pagamento) = ?
        AND status_pagamento = 'Aberto'";
    $stmtParcelasAberto = $conn->prepare($sqlParcelasAberto);
    $stmtParcelasAberto->bind_param("ii", $ano, $mes);
    $stmtParcelasAberto->execute();
    $parcelasEmAberto = $stmtParcelasAberto->get_result()->fetch_assoc()['total_aberto'];

    // Anos disponíveis
    $sqlAnos = "
        SELECT DISTINCT ano FROM (
            SELECT YEAR(data_inicio) as ano FROM servicos
            UNION
            SELECT YEAR(data) as ano FROM despesas_fixas
        ) anos
        ORDER BY ano";
    $resultAnos = $conn->query($sqlAnos);
    $anosDisponiveis = [];
    while ($row = $resultAnos->fetch_assoc()) {
        $anosDisponiveis[] = $row['ano'];
    }

    // Cálculo da projeção
    $projecaoFinanceira = ($totalEntrada + $totalRecebido) - ($totalDespesasFixas + $parcelasEmAberto);

    $response = [
        'projecao' => number_format($projecaoFinanceira, 2, ',', '.'),
        'anos_disponiveis' => $anosDisponiveis,
        'detalhes' => [
            'total_entrada' => number_format($totalEntrada, 2, ',', '.'),
            'total_recebido' => number_format($totalRecebido, 2, ',', '.'),
            'total_despesas_fixas' => number_format($totalDespesasFixas, 2, ',', '.'),
            'parcelas_em_aberto' => number_format($parcelasEmAberto, 2, ',', '.')
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>