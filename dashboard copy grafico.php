<?php
include 'conexao.php';
include 'php/dashboard.php';
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
    <link rel="stylesheet" href="css/dashboard.css">
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
                            <h3>Dashboard de Sócios</h3>
                            <div class="filtros-socios">
                                <select name="ano" id="anoSelectSocios" class="select-periodo">
                                    <option value="">Selecione o Ano</option>
                                </select>
                                <select name="mes" id="mesSelectSocios" class="select-periodo">
                                    <option value="">Selecione o Mês</option>
                                    <?php 
                                    $meses = [
                                        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
                                        4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                                        7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
                                        10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
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
        let sociosChart = null;

        function criarGraficoSocios(data) {
            const ctx = document.getElementById('sociosChart').getContext('2d');
            
            if (sociosChart) {
                sociosChart.destroy();
            }

            // Preparar dados para o gráfico
            const socios = data.socios;
            const labels = socios.map(s => s.nome);
            
            sociosChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pró-Labore Base',
                            data: socios.map(s => s.pro_labore_base),
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: 'rgba(52, 152, 219, 1)',
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
                            borderRadius: 5,
                            type: 'bar'
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

            // Atualizar o faturamento total
            document.getElementById('faturamentoTotal').textContent = 
                data.periodo.faturamento_total.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
        }

        function carregarAnosSocios() {
            const anoAtual = new Date().getFullYear();
            anoSelectSocios.innerHTML = '<option value="">Selecione o Ano</option>';
            
            for (let ano = anoAtual; ano >= 2023; ano--) {
                const option = document.createElement('option');
                option.value = ano;
                option.textContent = ano;
                if (ano === anoAtual) option.selected = true;
                anoSelectSocios.appendChild(option);
            }
            
            atualizarDashboardSocios();
        }

        function atualizarDashboardSocios() {
            const ano = anoSelectSocios.value || new Date().getFullYear();
            const mes = mesSelectSocios.value || (new Date().getMonth() + 1);
            
            fetch(`dashboard_socios.php?ano=${ano}&mes=${mes}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        criarGraficoSocios(data);
                    } else {
                        console.error('Erro:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }

        // Event listeners
        anoSelectSocios.addEventListener('change', atualizarDashboardSocios);
        mesSelectSocios.addEventListener('change', atualizarDashboardSocios);
        
        // Inicialização
        mesSelectSocios.value = new Date().getMonth() + 1;
        carregarAnosSocios();
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
    </style>
</body>
</html>