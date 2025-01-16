<?php
include 'conexao.php';

// Total de entrada
$sqlTotalEntrada = "SELECT SUM(valor_total) AS total_entrada FROM servicos";
$resultTotalEntrada = $conn->query($sqlTotalEntrada);
$totalEntrada = $resultTotalEntrada->fetch_assoc()['total_entrada'] ?? 0;

// Total recebido (valor entrada + parcelas pagas)
$sqlTotalRecebido = "
    SELECT 
        SUM(valor_entrada) +
        (SELECT COALESCE(SUM(valor_parcela), 0) FROM pagamentos WHERE status_pagamento = 'Pago') AS total_recebido
    FROM servicos";
$resultTotalRecebido = $conn->query($sqlTotalRecebido);
$totalRecebido = $resultTotalRecebido->fetch_assoc()['total_recebido'] ?? 0;

// Parcelas em aberto
$sqlParcelasAberto = "SELECT SUM(valor_parcela) AS total_aberto FROM pagamentos WHERE status_pagamento = 'Aberto'";
$resultParcelasAberto = $conn->query($sqlParcelasAberto);
$parcelasAberto = $resultParcelasAberto->fetch_assoc()['total_aberto'] ?? 0;

// Total despesas fixas
$sqlDespesasFixas = "SELECT SUM(valor) AS total FROM despesas_fixas";
$resultDespesasFixas = $conn->query($sqlDespesasFixas);
$totalDespesasFixas = $resultDespesasFixas->fetch_assoc()['total'] ?? 0;

// Total despesas gerais
$sqlDespesasGerais = "SELECT SUM(valor) AS total FROM despesas";
$resultDespesasGerais = $conn->query($sqlDespesasGerais);
$totalDespesasGerais = $resultDespesasGerais->fetch_assoc()['total'] ?? 0;

$totalDespesas = $totalDespesasFixas + $totalDespesasGerais;
$saldo = $totalRecebido - $totalDespesas;

// Despesas mensais fixas
$sqlDespesasMensaisFixas = "
    SELECT 
        mes, 
        SUM(valor) AS total_despesas 
    FROM despesas_fixas 
    GROUP BY mes
    ORDER BY FIELD(mes, '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12')";
$resultDespesasMensaisFixas = $conn->query($sqlDespesasMensaisFixas);
$despesasMensaisFixas = [];
while ($row = $resultDespesasMensaisFixas->fetch_assoc()) {
    $despesasMensaisFixas[(int)$row['mes']] = $row['total_despesas'];
}

// Projeção financeira
$projecaoFinanceira = ($totalEntrada - ($totalDespesasFixas + $totalDespesasGerais)) + $totalRecebido;



// Quantidade de serviços por status
$sqlServicosStatus = "
    SELECT 
        status_servico, 
        COUNT(*) AS total 
    FROM servicos 
    GROUP BY status_servico";
$resultServicosStatus = $conn->query($sqlServicosStatus);

// Organizar os dados
$statusServicos = [];
while ($row = $resultServicosStatus->fetch_assoc()) {
    $statusServicos[$row['status_servico']] = $row['total'];
}


// Entradas mensais
$sqlEntradasMensais = "
    SELECT 
        MONTH(data_inicio) AS mes, 
        SUM(valor_total) AS total_entrada 
    FROM servicos 
    WHERE status_servico = 'FINALIZADO'
    GROUP BY MONTH(data_inicio)
    ORDER BY FIELD(MONTH(data_inicio), 1,2,3,4,5,6,7,8,9,10,11,12)";
$resultEntradasMensais = $conn->query($sqlEntradasMensais);

// Despesas mensais
$sqlDespesasMensais = "
    SELECT 
        mes, 
        SUM(valor) AS total_despesas 
    FROM despesas_fixas 
    GROUP BY mes
    ORDER BY FIELD(mes, '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12')";
$resultDespesasMensais = $conn->query($sqlDespesasMensais);

// Organizar os dados
$entradasMensais = [];
while ($row = $resultEntradasMensais->fetch_assoc()) {
    $entradasMensais[(int)$row['mes']] = $row['total_entrada'];
}

$despesasMensais = [];
while ($row = $resultDespesasMensais->fetch_assoc()) {
    $despesasMensais[(int)$row['mes']] = $row['total_despesas'];
}

// Calcula a projeção mensal
$projecoesMensais = [];
for ($i = 1; $i <= 12; $i++) {
    $entradaMensal = $entradasMensais[$i] ?? 0;
    $despesaMensal = $despesasMensais[$i] ?? 0;
    $projecoesMensais[$i] = $entradaMensal - $despesaMensal;
}


$sqlEntradasMensais = "
    SELECT 
        MONTH(data_inicio) AS mes, 
        SUM(valor_total) AS total_entrada 
    FROM servicos 
    WHERE status_servico = 'FINALIZADO' -- Apenas serviços finalizados
    GROUP BY MONTH(data_inicio)
    ORDER BY FIELD(MONTH(data_inicio), 1,2,3,4,5,6,7,8,9,10,11,12)";

$resultEntradasMensais = $conn->query($sqlEntradasMensais);

// Organizar os dados por mês
$entradasMensais = array_fill(1, 12, 0); // Preenche os 12 meses com valor 0
while ($row = $resultEntradasMensais->fetch_assoc()) {
    $entradasMensais[(int)$row['mes']] = (float)$row['total_entrada'];
}

// Consulta para obter as despesas gerais
$sqlDespesasGerais = "
    SELECT nome_despesa AS nome, SUM(valor) AS total 
    FROM despesas 
    GROUP BY nome_despesa 
    ORDER BY total DESC";

$resultDespesasGerais = $conn->query($sqlDespesasGerais);

// Organizar os dados em arrays para o gráfico
$nomesDespesas = [];
$valoresDespesas = [];

while ($row = $resultDespesasGerais->fetch_assoc()) {
    $nomesDespesas[] = $row['nome'];
    $valoresDespesas[] = (float)$row['total'];
}


$sqlSaidasFuturas = "
    SELECT SUM(valor_parcela) AS total_saidas_futuras
    FROM pagamentos
    WHERE status_pagamento = 'Aberto'";
$resultSaidasFuturas = $conn->query($sqlSaidasFuturas);
$saidasFuturas = $resultSaidasFuturas->fetch_assoc()['total_saidas_futuras'] ?? 0;

// Despesas por Tipo
$sqlDespesasPorTipo = "
    SELECT nome_despesa, SUM(valor) AS total_despesa
    FROM despesas
    GROUP BY nome_despesa";
$resultDespesasPorTipo = $conn->query($sqlDespesasPorTipo);
$despesasPorTipo = [];
while ($row = $resultDespesasPorTipo->fetch_assoc()) {
    $despesasPorTipo[$row['nome_despesa']] = $row['total_despesa'];
}

// Quantidade de Serviços por Status
$sqlQuantidadeServicos = "
    SELECT status_servico, COUNT(*) AS total_servicos
    FROM servicos
    GROUP BY status_servico";
$resultQuantidadeServicos = $conn->query($sqlQuantidadeServicos);
$quantidadeServicos = [];
while ($row = $resultQuantidadeServicos->fetch_assoc()) {
    $quantidadeServicos[$row['status_servico']] = $row['total_servicos'];
}

$sqlQuantidadeTotalServicos = "
    SELECT COUNT(*) AS total FROM servicos";
$resultQuantidadeTotalServicos = $conn->query($sqlQuantidadeTotalServicos);
$totalServicos = $resultQuantidadeTotalServicos->fetch_assoc()['total'];



// Pega o mês selecionado ou o mês atual como padrão
$mesSelecionado = isset($_GET['mes']) ? $_GET['mes'] : date('m');

// Total de entrada para o mês selecionado
$sqlTotalEntrada = "
    SELECT SUM(valor_total) AS total_entrada 
    FROM servicos 
    WHERE MONTH(data_inicio) = ? AND status_servico = 'FINALIZADO'";
$stmtTotalEntrada = $conn->prepare($sqlTotalEntrada);
$stmtTotalEntrada->bind_param("i", $mesSelecionado);
$stmtTotalEntrada->execute();
$resultTotalEntrada = $stmtTotalEntrada->get_result();
$totalEntrada = $resultTotalEntrada->fetch_assoc()['total_entrada'] ?? 0;


// Total de parcelas em aberto para o mês selecionado
$sqlParcelasAberto = "
    SELECT SUM(valor_parcela) AS total_aberto 
    FROM pagamentos 
    WHERE status_pagamento = 'Aberto' AND MONTH(data_pagamento) = ?";
$stmtParcelasAberto = $conn->prepare($sqlParcelasAberto);
$stmtParcelasAberto->bind_param("i", $mesSelecionado);
$stmtParcelasAberto->execute();
$resultParcelasAberto = $stmtParcelasAberto->get_result();
$parcelasEmAberto = $resultParcelasAberto->fetch_assoc()['total_aberto'] ?? 0;

// Despesas fixas do mês selecionado
$sqlDespesasFixas = "
    SELECT SUM(valor) AS total 
    FROM despesas_fixas 
    WHERE MONTH(data) = ?";
$stmtDespesasFixas = $conn->prepare($sqlDespesasFixas);
$stmtDespesasFixas->bind_param("i", $mesSelecionado);
$stmtDespesasFixas->execute();
$resultDespesasFixas = $stmtDespesasFixas->get_result();
$totalDespesasFixas = $resultDespesasFixas->fetch_assoc()['total'] ?? 0;


// Cálculo da projeção financeira do mês selecionado
$projecaoFinanceira = ($totalEntrada - $totalDespesasFixas) + $totalRecebido - $parcelasEmAberto;


// Consulta SQL para buscar faturamento por tipo
$sqlFaturamentoPorTipo = "
    SELECT ts.tipo_servico, SUM(s.valor_total) AS total_faturado
    FROM servicos s
    JOIN servico_tipo_servico sts ON s.id = sts.servico_id
    JOIN tipos_servicos ts ON sts.tipo_servico_id = ts.id
    GROUP BY ts.tipo_servico
    ORDER BY total_faturado DESC";

$resultFaturamentoPorTipo = $conn->query($sqlFaturamentoPorTipo);
$faturamentoPorTipo = [];
$totalGeral = 0;

while ($row = $resultFaturamentoPorTipo->fetch_assoc()) {
    $faturamentoPorTipo[$row['tipo_servico']] = $row['total_faturado'];
    $totalGeral += $row['total_faturado'];
}
// Preparar dados para o gráfico
$tiposServico = json_encode(array_keys($faturamentoPorTipo));
$valoresFaturamento = json_encode(array_values($faturamentoPorTipo));



// Clientes totais
$sqlTotalClientes = "SELECT COUNT(*) AS total FROM cliente";
$resultTotalClientes = $conn->query($sqlTotalClientes);
$totalClientes = $resultTotalClientes->fetch_assoc()['total'];


$sqlStatusServicos = "
    SELECT status_servico, COUNT(*) AS total
    FROM servicos
    GROUP BY status_servico";
$resultStatusServicos = $conn->query($sqlStatusServicos);
$statusServicos = [];
while ($row = $resultStatusServicos->fetch_assoc()) {
    $statusServicos[$row['status_servico']] = $row['total'];
}


$sqlStatusPagamento = "
    SELECT status_pagamento, COUNT(*) AS total
    FROM pagamentos
    GROUP BY status_pagamento";
$resultStatusPagamento = $conn->query($sqlStatusPagamento);
$statusPagamento = [];
while ($row = $resultStatusPagamento->fetch_assoc()) {
    $statusPagamento[$row['status_pagamento']] = $row['total'];
}



$sqlMelhoresClientes = "
    SELECT 
        c.nome,
        c.razao_social,
        COUNT(DISTINCT s.id) as total_servicos,
        SUM(s.valor_total) as valor_total,
        COALESCE(
            (SELECT COUNT(*) 
             FROM pagamentos p 
             WHERE p.numero_proposta = s.numero_proposta 
            ) / 
            (SELECT COUNT(*) 
             FROM pagamentos p 
             WHERE p.numero_proposta = s.numero_proposta
            ) * 100,
            100
        ) as percentual_pagamentos_em_dia
    FROM cliente c
    JOIN servicos s ON c.id = s.cliente_id
    GROUP BY c.id
    HAVING percentual_pagamentos_em_dia >= 90
    ORDER BY valor_total DESC
    LIMIT 5";

$resultMelhoresClientes = $conn->query($sqlMelhoresClientes);
$melhoresClientes = [];
while ($row = $resultMelhoresClientes->fetch_assoc()) {
    $melhoresClientes[] = $row;
}

// Serviços e despesas por ano
$sqlIntervaloAnos = "
    SELECT 
        MIN(ano) as ano_inicial,
        MAX(ano) as ano_final
    FROM (
        SELECT YEAR(data_inicio) as ano FROM servicos
        UNION
        SELECT YEAR(data) as ano FROM despesas_fixas
    ) anos";

$resultIntervalo = $conn->query($sqlIntervaloAnos);
$intervalo = $resultIntervalo->fetch_assoc();
$anoInicial = $intervalo['ano_inicial'];
$anoFinal = $intervalo['ano_final'];

$sqlTodosAnos = "
    WITH RECURSIVE anos AS (
        SELECT $anoInicial as ano
        UNION ALL
        SELECT ano + 1
        FROM anos
        WHERE ano < $anoFinal
    )
    SELECT * FROM anos";

$sqlServicosAnuais = "
    SELECT 
        a.ano,
        COUNT(s.id) as total_servicos,
        COALESCE(SUM(s.valor_total), 0) as valor_total_servicos,
        COALESCE((
            SELECT SUM(d.valor)
            FROM despesas d
            WHERE YEAR(d.data) = a.ano
        ), 0) as total_despesas,
        COALESCE((
            SELECT SUM(df.valor)
            FROM despesas_fixas df
            WHERE YEAR(df.data) = a.ano
        ), 0) as total_despesas_fixas
    FROM ($sqlTodosAnos) a
    LEFT JOIN servicos s ON YEAR(s.data_inicio) = a.ano
    GROUP BY a.ano
    ORDER BY a.ano";

$resultServicosAnuais = $conn->query($sqlServicosAnuais);
$servicosAnuais = [];
while ($row = $resultServicosAnuais->fetch_assoc()) {
    $servicosAnuais[$row['ano']] = $row;
}



// Consultar as coordenadas da empresa (você pode filtrar por id ou outro critério)
$stmt = $conn->query("SELECT coordenada FROM empresa WHERE id = 1"); // Exemplo de filtro pelo ID
$empresa = $stmt->fetch_assoc();

// Verificar se a coordenada foi encontrada
if ($empresa) {
    $coordenada = explode(",", $empresa['coordenada']); // Separar latitude e longitude
    $latitude = $coordenada[0];
    $longitude = $coordenada[1];
}

// Consulta para quantidade de serviços mensais
$sqlServicosQuantidadeMensal = "
    SELECT 
        MONTH(data_inicio) AS mes, 
        COUNT(*) AS quantidade_servicos 
    FROM servicos 
    WHERE YEAR(data_inicio) = YEAR(CURRENT_DATE)
    GROUP BY MONTH(data_inicio)
    ORDER BY FIELD(MONTH(data_inicio), 1,2,3,4,5,6,7,8,9,10,11,12)";

$resultServicosQuantidadeMensal = $conn->query($sqlServicosQuantidadeMensal);

// Organizar os dados por mês
$servicosQuantidadeMensal = array_fill(1, 12, 0); // Preenche os 12 meses com valor 0
while ($row = $resultServicosQuantidadeMensal->fetch_assoc()) {
    $servicosQuantidadeMensal[(int)$row['mes']] = (int)$row['quantidade_servicos'];
}

// Ano atual
$anoAtual = date('Y');
$mesAtual = date('m');

// Entrada total do ano
$sqlEntradaAno = "
    SELECT COALESCE(SUM(valor_total), 0) as total 
    FROM servicos 
    WHERE YEAR(data_inicio) = ?";
$stmtEntradaAno = $conn->prepare($sqlEntradaAno);
$stmtEntradaAno->bind_param("i", $anoAtual);
$stmtEntradaAno->execute();
$entradaAnoTotal = $stmtEntradaAno->get_result()->fetch_assoc()['total'];

// Entrada do mês
$sqlEntradaMes = "
    SELECT COALESCE(SUM(valor_total), 0) as total 
    FROM servicos 
    WHERE YEAR(data_inicio) = ? AND MONTH(data_inicio) = ?";
$stmtEntradaMes = $conn->prepare($sqlEntradaMes);
$stmtEntradaMes->bind_param("ii", $anoAtual, $mesAtual);
$stmtEntradaMes->execute();
$entradaMesTotal = $stmtEntradaMes->get_result()->fetch_assoc()['total'];

// Saída total do ano (despesas fixas + despesas gerais)
$sqlSaidaAno = "
    SELECT (
        COALESCE((SELECT SUM(valor) FROM despesas_fixas WHERE YEAR(data) = ?), 0) +
        COALESCE((SELECT SUM(valor) FROM despesas WHERE YEAR(data) = ?), 0)
    ) as total";
$stmtSaidaAno = $conn->prepare($sqlSaidaAno);
$stmtSaidaAno->bind_param("ii", $anoAtual, $anoAtual);
$stmtSaidaAno->execute();
$saidaAnoTotal = $stmtSaidaAno->get_result()->fetch_assoc()['total'];

// Saída do mês
$sqlSaidaMes = "
    SELECT (
        COALESCE((SELECT SUM(valor) FROM despesas_fixas WHERE YEAR(data) = ? AND MONTH(data) = ?), 0) +
        COALESCE((SELECT SUM(valor) FROM despesas WHERE YEAR(data) = ? AND MONTH(data) = ?), 0)
    ) as total";
$stmtSaidaMes = $conn->prepare($sqlSaidaMes);
$stmtSaidaMes->bind_param("iiii", $anoAtual, $mesAtual, $anoAtual, $mesAtual);
$stmtSaidaMes->execute();
$saidaMesTotal = $stmtSaidaMes->get_result()->fetch_assoc()['total'];

// Entrada futura (parcelas a receber)
$sqlEntradaFutura = "
    SELECT COALESCE(SUM(valor_parcela), 0) as total 
    FROM pagamentos 
    WHERE status_pagamento = 'Aberto' 
    AND data_pagamento > CURRENT_DATE";
$resultEntradaFutura = $conn->query($sqlEntradaFutura);
$entradaFutura = $resultEntradaFutura->fetch_assoc()['total'];

// Saída futura (despesas futuras)
$sqlSaidaFutura = "
    SELECT COALESCE(SUM(valor), 0) as total 
    FROM despesas_fixas 
    WHERE data > CURRENT_DATE";
$resultSaidaFutura = $conn->query($sqlSaidaFutura);
$saidaFutura = $resultSaidaFutura->fetch_assoc()['total'];

// Cálculo dos saldos
$saldoMes = $entradaMesTotal - $saidaMesTotal;
$saldoFuturo = $entradaFutura - $saidaFutura;
$saldoAnoTotal = $entradaAnoTotal - $saidaAnoTotal;

$conn->close();
?>