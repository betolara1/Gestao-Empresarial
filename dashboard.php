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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Financeiro</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>Dashboard Financeiro</h1>
            
            <div class="dashboard">
                <!-- Indicadores Financeiros Consolidados -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <div class="header-content">
                            <h3>Indicadores Financeiros</h3>
                            <div class="periodo-selector">
                                <button class="periodo-btn active" data-periodo="mes">Mês Atual</button>
                                <button class="periodo-btn" data-periodo="ano">Ano <?php echo $anoAtual; ?></button>
                                <button class="periodo-btn" data-periodo="futuro">Projeções</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="indicadoresFinanceirosChart"></canvas>
                        </div>
                        <div class="saldo-info">
                            <div class="saldo-item" id="saldoMes">
                                Saldo do Mês: 
                                <span class="<?php echo $saldoMes >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    R$ <?php echo number_format($saldoMes, 2, ',', '.'); ?>
                                </span>
                            </div>
                            <div class="saldo-item" id="saldoAno" style="display: none;">
                                Saldo do Ano: 
                                <span class="<?php echo $saldoAnoTotal >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    R$ <?php echo number_format($saldoAnoTotal, 2, ',', '.'); ?>
                                </span>
                            </div>
                            <div class="saldo-item" id="saldoFuturo" style="display: none;">
                                Saldo Futuro: 
                                <span class="<?php echo $saldoFuturo >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    R$ <?php echo number_format($saldoFuturo, 2, ',', '.'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visão Geral em Gráfico de Rosca -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <h3>Visão Geral</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="visaoGeralChart"></canvas>
                    </div>
                </div>

                <!-- Status dos Serviços em Gráfico de Pizza -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <h3>Status dos Serviços</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="statusServicosChart"></canvas>
                    </div>
                </div>

                <!-- Despesas por Tipo em Gráfico de Barras -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <h3>Despesas por Tipo</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="despesasTipoChart"></canvas>
                    </div>
                </div>

                <!-- Status de Pagamento em Gráfico de Rosca -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <h3>Status de Pagamento</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="statusPagamentoChart"></canvas>
                    </div>
                </div>

                <!-- Faturamento por Tipo de Serviço -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <div class="header-content">
                            <h3>Faturamento por Tipo de Serviço</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="faturamentoTipoChart"></canvas>
                        </div>
                        <div class="total-info">
                            Total: R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <!-- Melhores Clientes -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <div class="header-content">
                            <h3>Melhores Clientes</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="melhoresClientesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Comparativo Anual -->
                <div class="card card-grafico card-wide">
                    <div class="card-header">
                        <div class="header-content">
                            <h3>Comparativo Anual</h3>
                            <div class="chart-toggles">
                                <button class="toggle-btn active" data-type="valores">Valores</button>
                                <button class="toggle-btn" data-type="servicos">Serviços</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="comparativoAnualChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Projeção Financeira -->
                <div class="card card-grafico">
                    <div class="card-header">
                        <div class="header-content">
                            <h3>Projeção Financeira</h3>
                            <div class="filtros-projecao">
                                <select name="ano" id="anoSelect" class="select-periodo">
                                    <option value="">Selecione o Ano</option>
                                </select>
                                <select name="mes" id="mesSelect" class="select-periodo">
                                    <option value="">Selecione o Mês</option>
                                    <?php 
                                    $meses = [
                                        1 => 'Janeiro',
                                        2 => 'Fevereiro',
                                        3 => 'Março',
                                        4 => 'Abril',
                                        5 => 'Maio',
                                        6 => 'Junho',
                                        7 => 'Julho',
                                        8 => 'Agosto',
                                        9 => 'Setembro',
                                        10 => 'Outubro',
                                        11 => 'Novembro',
                                        12 => 'Dezembro'
                                    ];
                                    
                                    foreach($meses as $num => $nome): ?>
                                        <option value="<?= $num ?>"><?= $nome ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="projecaoFinanceiraChart"></canvas>
                        </div>
                        <div class="projecao-info">
                            <div class="projecao-total">
                                Projeção Total: R$ <span id="valorProjecao">0,00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard de Sócios -->
                <div class="card card-grafico card-wide">
                    <div class="card-header">
                        <div class="header-content">
                            <h3>Dashboard de Sócios - <span id="periodoSelecionado"></span></h3>
                            <div class="filtros-socios">
                                <select name="ano" id="anoSelectSocios" class="select-periodo">
                                    <option value="">Selecione o Ano</option>
                                    <?php 
                                    $anoAtual = date('Y');
                                    for($ano = $anoAtual; $ano >= 2024; $ano--) {
                                        echo "<option value='$ano'" . ($ano == $anoAtual ? " selected" : "") . ">$ano</option>";
                                    }
                                    ?>
                                </select>
                                <select name="mes" id="mesSelectSocios" class="select-periodo">
                                    <option value="">Selecione o Mês</option>
                                    <?php foreach($meses as $num => $nome): ?>
                                        <option value="<?= $num ?>"><?= $nome ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button id="btnVisualizacao" class="toggle-btn">
                                    Alternar Visualização
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="sociosChart"></canvas>
                        </div>
                        <div class="faturamento-info">
                            Faturamento do Período: R$ <span id="faturamentoTotal">0,00</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('indicadoresFinanceirosChart').getContext('2d');
        let chartInstance = null;

        const dadosFinanceiros = {
            mes: {
                labels: ['Entrada', 'Saída'],
                valores: [<?php echo $entradaMesTotal; ?>, <?php echo $saidaMesTotal; ?>],
                cores: ['rgba(46, 204, 113, 0.7)', 'rgba(231, 76, 60, 0.7)'],
                bordesCores: ['rgba(46, 204, 113, 1)', 'rgba(231, 76, 60, 1)']
            },
            ano: {
                labels: ['Entrada', 'Saída'],
                valores: [<?php echo $entradaAnoTotal; ?>, <?php echo $saidaAnoTotal; ?>],
                cores: ['rgba(52, 152, 219, 0.7)', 'rgba(231, 76, 60, 0.7)'],
                bordesCores: ['rgba(52, 152, 219, 1)', 'rgba(231, 76, 60, 1)']
            },
            futuro: {
                labels: ['Entrada Futura', 'Saída Futura'],
                valores: [<?php echo $entradaFutura; ?>, <?php echo $saidaFutura; ?>],
                cores: ['rgba(155, 89, 182, 0.7)', 'rgba(243, 156, 18, 0.7)'],
                bordesCores: ['rgba(155, 89, 182, 1)', 'rgba(243, 156, 18, 1)']
            }
        };

        function criarGrafico(periodo) {
            if (chartInstance) {
                chartInstance.destroy();
            }

            const dados = dadosFinanceiros[periodo];

            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dados.labels,
                    datasets: [{
                        data: dados.valores,
                        backgroundColor: dados.cores,
                        borderColor: dados.bordesCores,
                        borderWidth: 1,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.raw.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 500
                    }
                }
            });
        }

        // Gerenciar botões de período
        document.querySelectorAll('.periodo-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.periodo-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const periodo = this.dataset.periodo;
                criarGrafico(periodo);
                
                // Mostrar/ocultar saldos correspondentes
                document.querySelectorAll('.saldo-item').forEach(item => item.style.display = 'none');
                document.getElementById('saldo' + periodo.charAt(0).toUpperCase() + periodo.slice(1)).style.display = 'block';
            });
        });

        // Iniciar com o gráfico do mês
        criarGrafico('mes');

        // Gráfico de Visão Geral
        new Chart(document.getElementById('visaoGeralChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Serviços', 'Clientes', 'Clientes Ativos'],
                datasets: [{
                    data: [
                        <?php echo $totalServicos; ?>,
                        <?php echo $totalClientes; ?>,
                        <?php echo $totalClientes; ?>
                    ],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(155, 89, 182, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(155, 89, 182, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Gráfico de Status dos Serviços
        new Chart(document.getElementById('statusServicosChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($statusServicos)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($statusServicos)); ?>,
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(155, 89, 182, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(155, 89, 182, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Gráfico de Despesas por Tipo
        new Chart(document.getElementById('despesasTipoChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($despesasPorTipo)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($despesasPorTipo)); ?>,
                    backgroundColor: 'rgba(231, 76, 60, 0.7)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.raw.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Status de Pagamento
        new Chart(document.getElementById('statusPagamentoChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_keys($statusPagamento))); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($statusPagamento)); ?>,
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(241, 196, 15, 0.7)'
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(241, 196, 15, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' parcelas';
                            }
                        }
                    }
                }
            }
        });

        // Faturamento por Tipo de Serviço
        const faturamentoData = {
            valores: <?php echo json_encode(array_values($faturamentoPorTipo)); ?>,
            labels: <?php echo json_encode(array_keys($faturamentoPorTipo)); ?>,
            total: <?php echo $totalGeral; ?>
        };

        new Chart(document.getElementById('faturamentoTipoChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: faturamentoData.labels,
                datasets: [{
                    data: faturamentoData.valores,
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const valor = context.raw;
                                const percentual = ((valor / faturamentoData.total) * 100).toFixed(1);
                                return `${context.label}: R$ ${valor.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })} (${percentual}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Melhores Clientes
        new Chart(document.getElementById('melhoresClientesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($cliente) {
                    return $cliente['razao_social'] ?: $cliente['nome'];
                }, $melhoresClientes)); ?>,
                datasets: [{
                    label: 'Total de Serviços',
                    data: <?php echo json_encode(array_map(function($cliente) {
                        return $cliente['total_servicos'];
                    }, $melhoresClientes)); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Barras horizontais
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    y: {
                        ticks: {
                            callback: function(value) {
                                // Limita o tamanho do texto do nome do cliente
                                const label = this.getLabelForValue(value);
                                if (label.length > 20) {
                                    return label.substr(0, 20) + '...';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        const dadosAnuais = {
            anos: <?php echo json_encode(array_keys($servicosAnuais)); ?>,
            dados: <?php echo json_encode(array_values($servicosAnuais)); ?>
        };

        let comparativoChart = null;

        function criarGraficoValores() {
            const ctx = document.getElementById('comparativoAnualChart').getContext('2d');
            
            if (comparativoChart) {
                comparativoChart.destroy();
            }

            comparativoChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dadosAnuais.anos,
                    datasets: [
                        {
                            label: 'Faturamento',
                            data: dadosAnuais.dados.map(d => d.valor_total_servicos),
                            backgroundColor: 'rgba(46, 204, 113, 0.7)',
                            borderColor: 'rgba(46, 204, 113, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        },
                        {
                            label: 'Despesas Variáveis',
                            data: dadosAnuais.dados.map(d => d.total_despesas),
                            backgroundColor: 'rgba(231, 76, 60, 0.7)',
                            borderColor: 'rgba(231, 76, 60, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        },
                        {
                            label: 'Despesas Fixas',
                            data: dadosAnuais.dados.map(d => d.total_despesas_fixas),
                            backgroundColor: 'rgba(241, 196, 15, 0.7)',
                            borderColor: 'rgba(241, 196, 15, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        },
                        {
                            label: 'Resultado',
                            data: dadosAnuais.dados.map(d => 
                                d.valor_total_servicos - (d.total_despesas + d.total_despesas_fixas)
                            ),
                            type: 'line',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            backgroundColor: 'rgba(52, 152, 219, 0.2)',
                            borderWidth: 2,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: R$ ${context.raw.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        function criarGraficoServicos() {
            const ctx = document.getElementById('comparativoAnualChart').getContext('2d');
            
            if (comparativoChart) {
                comparativoChart.destroy();
            }

            comparativoChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dadosAnuais.anos,
                    datasets: [{
                        label: 'Total de Serviços',
                        data: dadosAnuais.dados.map(d => d.total_servicos),
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Event listeners para os botões de toggle
        document.querySelectorAll('.toggle-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                if (this.dataset.type === 'valores') {
                    criarGraficoValores();
                } else {
                    criarGraficoServicos();
                }
            });
        });

        // Iniciar com o gráfico de valores
        criarGraficoValores();

        const anoSelect = document.getElementById('anoSelect');
        const mesSelect = document.getElementById('mesSelect');
        let projecaoChart = null;

        // Função para criar/atualizar o gráfico
        function atualizarGraficoProjecao(dados) {
            const ctx = document.getElementById('projecaoFinanceiraChart').getContext('2d');
            
            if (projecaoChart) {
                projecaoChart.destroy();
            }

            projecaoChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Entradas', 'Recebido', 'Despesas Fixas', 'Parcelas em Aberto'],
                    datasets: [{
                        data: [
                            parseFloat(dados.detalhes.total_entrada.replace(/[^0-9,-]/g, '').replace(',', '.')),
                            parseFloat(dados.detalhes.total_recebido.replace(/[^0-9,-]/g, '').replace(',', '.')),
                            parseFloat(dados.detalhes.total_despesas_fixas.replace(/[^0-9,-]/g, '').replace(',', '.')),
                            parseFloat(dados.detalhes.parcelas_em_aberto.replace(/[^0-9,-]/g, '').replace(',', '.'))
                        ],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',  // Verde para entradas
                            'rgba(52, 152, 219, 0.7)',  // Azul para recebido
                            'rgba(231, 76, 60, 0.7)',   // Vermelho para despesas
                            'rgba(241, 196, 15, 0.7)'   // Amarelo para parcelas
                        ],
                        borderColor: [
                            'rgba(46, 204, 113, 1)',
                            'rgba(52, 152, 219, 1)',
                            'rgba(231, 76, 60, 1)',
                            'rgba(241, 196, 15, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.raw.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        // Função para carregar anos disponíveis
        function carregarAnos() {
            fetch('projecao_financeira.php')
                .then(response => response.json())
                .then(data => {
                    const anosDisponiveis = data.anos_disponiveis;
                    const anoAtual = new Date().getFullYear();
                    
                    anoSelect.innerHTML = '<option value="">Selecione o Ano</option>';
                    anosDisponiveis.forEach(ano => {
                        const option = document.createElement('option');
                        option.value = ano;
                        option.textContent = ano;
                        if (ano == anoAtual) option.selected = true;
                        anoSelect.appendChild(option);
                    });
                    
                    atualizarProjecao();
                });
        }

        // Função para atualizar a projeção
        function atualizarProjecao() {
            const ano = anoSelect.value || new Date().getFullYear();
            const mes = mesSelect.value || new Date().getMonth() + 1;
            
            fetch(`projecao_financeira.php?ano=${ano}&mes=${mes}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('valorProjecao').textContent = data.projecao;
                    atualizarGraficoProjecao(data);
                });
        }

        // Event listeners
        anoSelect.addEventListener('change', atualizarProjecao);
        mesSelect.addEventListener('change', atualizarProjecao);

        // Inicialização
        carregarAnos();
        mesSelect.value = new Date().getMonth() + 1;

        const anoSelectSocios = document.getElementById('anoSelectSocios');
        const mesSelectSocios = document.getElementById('mesSelectSocios');
        const btnVisualizacao = document.getElementById('btnVisualizacao');
        const periodoSelecionado = document.getElementById('periodoSelecionado');
        let sociosChart = null;
        let tipoVisualizacao = 'geral';

        function atualizarPeriodoSelecionado() {
            const ano = anoSelectSocios.value;
            const mes = mesSelectSocios.options[mesSelectSocios.selectedIndex].text;
            periodoSelecionado.textContent = `${mes}/${ano}`;
        }

        function criarGraficoSocios(data) {
            const ctx = document.getElementById('sociosChart').getContext('2d');
            
            if (sociosChart) {
                sociosChart.destroy();
            }

            const socios = data.socios;
            const labels = socios.map(s => s.nome);
            
            let datasets = [];
            
            if (tipoVisualizacao === 'geral') {
                datasets = [
                    {
                        label: 'Pró-Labore Retirado',
                        data: socios.map(s => s.pro_labore_retirado),
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    },
                    {
                        label: 'Comissão',
                        data: socios.map(s => s.comissao),
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    },
                    {
                        label: 'Valor Disponível',
                        data: socios.map(s => s.valor_disponivel),
                        backgroundColor: 'rgba(155, 89, 182, 0.7)',
                        borderColor: 'rgba(155, 89, 182, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }
                ];
            } else {
                const mesesLabels = [
                    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ];
                
                datasets = socios.map(socio => ({
                    label: socio.nome,
                    data: socio.retiradas_mensais || Array(12).fill(0),
                    borderColor: gerarCor(socio.id),
                    backgroundColor: gerarCor(socio.id, 0.1),
                    fill: true,
                    tension: 0.4
                }));

                labels = mesesLabels;
            }

            sociosChart = new Chart(ctx, {
                type: tipoVisualizacao === 'geral' ? 'bar' : 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': R$ ' + 
                                        context.raw.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        function gerarCor(seed, alpha = 0.7) {
            const cores = [
                `rgba(52, 152, 219, ${alpha})`,
                `rgba(46, 204, 113, ${alpha})`,
                `rgba(155, 89, 182, ${alpha})`,
                `rgba(231, 76, 60, ${alpha})`,
                `rgba(241, 196, 15, ${alpha})`
            ];
            return cores[seed % cores.length];
        }

        function atualizarDashboardSocios() {
            const ano = anoSelectSocios.value || new Date().getFullYear();
            const mes = mesSelectSocios.value || (new Date().getMonth() + 1);
            
            atualizarPeriodoSelecionado();
            
            fetch(`dashboard_socios.php?ano=${ano}&mes=${mes}&tipo=${tipoVisualizacao}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        criarGraficoSocios(data);
                        document.getElementById('faturamentoTotal').textContent = 
                            data.periodo.faturamento_total.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Event listeners
        anoSelectSocios.addEventListener('change', atualizarDashboardSocios);
        mesSelectSocios.addEventListener('change', atualizarDashboardSocios);
        btnVisualizacao.addEventListener('click', function() {
            tipoVisualizacao = tipoVisualizacao === 'geral' ? 'retiradas' : 'geral';
            this.textContent = tipoVisualizacao === 'geral' ? 'Ver Retiradas Mensais' : 'Ver Visão Geral';
            atualizarDashboardSocios();
        });

        // Inicialização
        mesSelectSocios.value = new Date().getMonth() + 1;
        atualizarDashboardSocios();
    });
    </script>

    <style>
    /* Container Principal */
    .dashboard {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 1.5rem;
        padding: 1.5rem;
        max-width: 100%;
        box-sizing: border-box;
    }

    /* Cards padrão */
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Cards de gráfico */
    .card-grafico {
        grid-column: span 6; /* 2 cards por linha por padrão */
        height: 400px;
        display: flex;
        flex-direction: column;
    }

    /* Card wide (ocupa linha inteira) */
    .card-wide {
        grid-column: span 12;
        height: 500px;
    }

    /* Header do card */
    .card-header {
        padding-bottom: 1rem;
    }

    /* Corpo do card */
    .card-body {
        flex: 1;
        position: relative;
        min-height: 0; /* Importante para evitar overflow */
    }

    /* Container do gráfico */
    .chart-container {
        position: relative;
        height: 100% !important;
        width: 100%;
    }

    /* Responsividade */
    @media (max-width: 1200px) {
        .card-grafico {
            grid-column: span 12; /* 1 card por linha em telas menores */
        }
    }

    @media (max-width: 768px) {
        .dashboard {
            padding: 1rem;
            gap: 1rem;
        }
        
        .card {
            padding: 1rem;
        }
    }

    /* Estilos específicos para elementos dentro dos cards */
    .header-content {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .periodo-selector,
    .chart-toggles {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .select-periodo,
    .toggle-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
    }

    .toggle-btn.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    /* Informações adicionais */
    .saldo-info,
    .total-info,
    .projecao-info {
        text-align: center;
        padding: 1rem 0;
        margin-top: auto;
    }

    .filtros-projecao {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .select-periodo {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #fff;
        min-width: 150px;
    }

    .projecao-info {
        text-align: center;
        margin-top: 1rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .projecao-total {
        font-size: 1.2em;
        font-weight: bold;
        color: #2c3e50;
    }

    .filtros-socios {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .faturamento-info {
        text-align: center;
        margin-top: 1rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 4px;
        font-weight: bold;
        font-size: 1.1em;
    }

    .toggle-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .toggle-btn:hover {
        background: #f8f9fa;
    }

    .periodo-selector {
        display: flex;
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .periodo-btn {
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: 6px;
        background: #f1f1f1;
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .periodo-btn:hover {
        background: #e4e4e4;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    .periodo-btn.active {
        background: #3498db;
        color: white;
        box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);
    }

    .periodo-btn.active:hover {
        background: #2980b9;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(52, 152, 219, 0.4);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .periodo-selector {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .periodo-btn {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }
    h1, h2 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: 700;
    }

    h1 {
        font-size: 2.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #eee;
    }

    h2 {
        font-size: 1.8rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background-color: var(--accent-color);
        border-radius: 2px;
    }
    </style>
</body>
</html>