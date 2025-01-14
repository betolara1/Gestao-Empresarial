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
                <!-- Indicadores Financeiros -->
                <div class="card">
                    <h3>Entrada</h3>
                    <p>R$ <?php echo number_format($totalRecebido, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h3>Saída</h3>
                    <p>R$ <?php echo number_format($totalDespesas, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h3>Saldo</h3>
                    <p>R$ <?php echo number_format($saldo, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h3>Saídas Futuras</h3>
                    <p>R$ <?php echo number_format($parcelasAberto, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h3>Quantidade de Serviços</h3>
                    <ul>
                        <li>Total: <?php echo $totalServicos; ?></li>
                    </ul>
                </div>
                <div class="card">
                    <h3>Quantidade de Clientes</h3>
                    <p>Total: <?php echo $totalClientes; ?></p>
                    <p>Ativos: <?php echo $totalClientes; ?></p>
                </div>
                <!-- Despesas por Tipo -->
                <div class="card">
                    <h3>Despesas por Tipo</h3>
                    <ul>
                        <?php foreach ($despesasPorTipo as $tipo => $valor): ?>
                            <li><?php echo $tipo; ?>: R$ <?php echo number_format($valor, 2, ',', '.'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card">
                    <h3>Status dos Serviços</h3>
                    <ul>
                        <?php foreach ($statusServicos as $status => $quantidade): ?>
                            <li><?php echo ucfirst($status); ?>: <?php echo $quantidade; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card">
                    <h3>Status de Pagamento</h3>
                    <ul>
                        <?php foreach ($statusPagamento as $status => $quantidade): ?>
                            <li><?php echo ucfirst($status); ?>: <?php echo $quantidade; ?> parcelas</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card">
                    <h3>Projeção Financeira</h3>
                    <form id="formProjecao">
                        <select name="ano" id="anoSelect">
                            <option value="">Selecione o Ano</option>
                        </select>
                        <select name="mes" id="mesSelect">
                            <option value="">Selecione o Mês</option>
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </form>
                    <div id="projecaoResultado"><br>
                        <li>Projeção: R$ <span id="valorProjecao">0,00</span></li>
                        <div id="detalhesProjecao">
                            <li>Entradas: R$ <span id="totalEntrada">0,00</span></li>
                            <li>Recebido: R$ <span id="totalRecebido">0,00</span></li>
                            <li>Despesas Fixas: R$ <span id="totalDespesasFixas">0,00</span></li>
                            <li>Parcelas em Aberto: R$ <span id="parcelasEmAberto">0,00</span></li>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <h3 class="titulo">Faturamento por Tipo de Serviço</h3>
                    <!-- Lista de Faturamento -->
                    <ul class="lista-faturamento">
                        <?php foreach ($faturamentoPorTipo as $tipo => $valor): 
                            $percentual = ($valor / $totalGeral) * 100;
                        ?>
                            <li class="item-faturamento">
                                <span><?php echo htmlspecialchars($tipo); ?>: R$ <?php echo number_format($valor, 2, ',', '.'); ?> (<?php echo number_format($percentual, 1); ?>%)</span>
                                <div>
                                    <span class="valor"></span>
                                    <small></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="total">
                        Total: R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?>
                    </div>
                </div>
                <div class="card">
                    <h3>Melhores Clientes</h3>
                    <ul>
                        <?php foreach ($melhoresClientes as $cliente): ?>
                            <li><strong>Cliente:</strong> <?php echo htmlspecialchars($cliente['razao_social'] ?: $cliente['nome']); ?><br></li>
                            <li><strong>Total de Serviços:</strong> <?php echo $cliente['total_servicos']; ?><br></li>
                            <br>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card">
                    <h3>Comparativo Anual</h3>
                    <ul>
                        <?php foreach ($servicosAnuais as $ano => $dados): ?>
                            <li>
                                <strong>Ano:</strong> <?php echo $ano; ?><br>
                                <strong>Total de Serviços:</strong> <?php echo $dados['total_servicos']; ?><br>
                                <strong>Faturamento:</strong> R$ <?php echo number_format($dados['valor_total_servicos'], 2, ',', '.'); ?><br>
                                <strong>Despesas Variáveis:</strong> R$ <?php echo number_format($dados['total_despesas'], 2, ',', '.'); ?><br>
                                <strong>Despesas Fixas:</strong> R$ <?php echo number_format($dados['total_despesas_fixas'], 2, ',', '.'); ?><br>
                                <strong>Despesas Totais:</strong> R$ <?php echo number_format($dados['total_despesas'] + $dados['total_despesas_fixas'], 2, ',', '.'); ?><br>
                                <strong>Resultado:</strong> R$ <?php echo number_format($dados['valor_total_servicos'] - ($dados['total_despesas'] + $dados['total_despesas_fixas']), 2, ',', '.'); ?>
                            </li><br>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!-- HTML do dashboard de sócios -->
                <div class="card">
                    <h3>Dashboard de Sócios</h3>
                    <form id="formPeriodoSocios" class="mb-4">
                        <select name="ano" id="anoSelectSocios">
                            <option value="">Selecione o Ano</option>
                        </select>
                        <select name="mes" id="mesSelectSocios">
                            <option value="">Selecione o Mês</option>
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </form>

                    <div id="sociosContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Cards dos sócios serão inseridos aqui via JavaScript -->
                    </div>
                </div>

                <!-- Template para o card de cada sócio -->
                <template id="socioCardTemplate">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-xl font-bold mb-3">Nome do Sócio</h4>
                        <div class="space-y-2">
                            <li><strong>Participação:</strong> <span class="porcentagem-sociedade">0</span>%</li>
                            <li><strong>Comissão:</strong> <span class="porcentagem-comissao">0</span>%</li>
                            <div class="border-t my-2"></div>
                            <li><strong>Pró-Labore Base:</strong> R$ <span class="pro-labore-base">0,00</span></li>
                            <li><strong>Pró-Labore Retirado:</strong> R$ <span class="pro-labore-retirado">0,00</span></li>
                            <li><strong>Pró-Labore Disponível:</strong> R$ <span class="pro-labore-disponivel">0,00</span></li>
                            <li><strong>Comissão do Mês:</strong> R$ <span class="comissao">0,00</span></li>
                            <div class="border-t my-2"></div>
                            <li class="text-lg font-semibold">
                                <strong>Disponível Total:</strong> R$ <span class="valor-disponivel">0,00</span>
                            </li>
                        </div>
                    </div>
                </template>
            </div>


            <br><br>
            <h1>Gráficos</h1>
            <div class="dashboard">
                <!-- Adicione os filtros aqui, antes dos gráficos -->
                <div class="card filtros-dashboard">
                    <h3>Filtros</h3>
                    <form id="filtrosGraficos" class="form-filtros">
                        <select name="ano" id="anoFiltro">
                            <option value="">Selecione o Ano</option>
                            <?php
                            $anoAtual = date('Y');
                            for($ano = $anoAtual; $ano >= 2023; $ano--) {
                                echo "<option value='$ano'>$ano</option>";
                            }
                            ?>
                        </select>
                        <select name="mes" id="mesFiltro">
                            <option value="">Todos os Meses</option>
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </form>
                </div>

                <!-- Seus gráficos existentes -->
                <div class="card">
                    <h3>Despesas Fixas Mensais</h3>
                    <canvas id="despesasFixasMensaisChart"></canvas><br>
                </div>
                <div class="card">
                    <h3>Serviços por Status</h3>
                    <canvas id="servicosStatusChart"></canvas><br>
                </div>
                <div class="card">
                    <h3>Projeção Financeira Mensal</h3>
                    <canvas id="projecaoMensalChart"></canvas>
                </div>
                <div class="card">
                    <h3>Entrada de Serviços Mensais</h3>
                    <canvas id="entradasMensaisChart"></canvas>
                </div>
                <div class="card">
                    <h3>Despesas Gerais</h3>
                    <canvas id="despesasGeraisChart"></canvas>
                </div>
                <div class="grafico">
                    <canvas id="graficoFaturamento"></canvas>
                </div>
            </div>

            <!-- Mapa -->
            <div class="card">
                <h3>Localização da Empresa</h3>
                <div id="map"></div>
            </div>
            <!-- Incluir o JS do Leaflet -->
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>


            <script>
                // Declare as variáveis dos gráficos globalmente
                let despesasFixasMensaisChart;
                let servicosStatusChart;
                let projecaoMensalChart;
                let entradasMensaisChart;
                let despesasGeraisChart;

                // Inicialização dos gráficos
                document.addEventListener('DOMContentLoaded', function() {
                    // Inicializar gráfico de Despesas Fixas Mensais
                    const ctx1 = document.getElementById('despesasFixasMensaisChart').getContext('2d');
                    despesasFixasMensaisChart = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                            datasets: [{
                                label: 'Despesas Fixas Mensais (R$)',
                                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], // Inicializa com zeros
                                backgroundColor: 'rgba(52, 152, 219, 0.5)',
                                borderColor: 'rgba(52, 152, 219, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Inicializar gráfico de Serviços por Status
                    const ctx2 = document.getElementById('servicosStatusChart').getContext('2d');
                    servicosStatusChart = new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Serviços por Status',
                                data: [],
                                backgroundColor: ['#27ae60', '#e67e22', '#e74c3c'],
                                borderColor: ['#2ecc71', '#d35400', '#c0392b'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });

                    // Inicializar gráfico de Projeção Financeira
                    const ctx3 = document.getElementById('projecaoMensalChart').getContext('2d');
                    projecaoMensalChart = new Chart(ctx3, {
                        type: 'line',
                        data: {
                            labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                            datasets: [{
                                label: 'Projeção Financeira Mensal (R$)',
                                data: <?php echo json_encode(array_values($projecoesMensais)); ?>,
                                backgroundColor: 'rgba(46, 204, 113, 0.5)',
                                borderColor: 'rgba(46, 204, 113, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Inicializar gráfico de Entradas Mensais
                    const ctx4 = document.getElementById('entradasMensaisChart').getContext('2d');
                    entradasMensaisChart = new Chart(ctx4, {
                        type: 'bar',
                        data: {
                            labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                            datasets: [{
                                label: 'Quantidade de Serviços',
                                data: <?php echo json_encode(array_values($servicosQuantidadeMensal)); ?>,
                                backgroundColor: 'rgba(52, 152, 219, 0.5)',
                                borderColor: 'rgba(41, 128, 185, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1 // Para garantir números inteiros no eixo Y
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `Quantidade: ${context.parsed.y} serviços`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Inicializar gráfico de Despesas Gerais
                    const ctx5 = document.getElementById('despesasGeraisChart').getContext('2d');
                    despesasGeraisChart = new Chart(ctx5, {
                        type: 'pie',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Despesas Gerais (R$)',
                                data: [],
                                backgroundColor: [
                                    '#3498db', '#2ecc71', '#e74c3c', '#9b59b6', '#f1c40f',
                                    '#34495e', '#e67e22', '#1abc9c', '#c0392b', '#95a5a6'
                                ],
                                borderColor: [
                                    '#2980b9', '#27ae60', '#c0392b', '#8e44ad', '#f39c12',
                                    '#2c3e50', '#d35400', '#16a085', '#b03a2e', '#7f8c8d'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });

                    // ... resto do código de atualização dos gráficos ...
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const anoSelect = document.getElementById('anoSelect');
                    const mesSelect = document.getElementById('mesSelect');
                    
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
                                
                                // Carrega os dados iniciais
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
                                document.getElementById('totalEntrada').textContent = data.detalhes.total_entrada;
                                document.getElementById('totalRecebido').textContent = data.detalhes.total_recebido;
                                document.getElementById('totalDespesasFixas').textContent = data.detalhes.total_despesas_fixas;
                                document.getElementById('parcelasEmAberto').textContent = data.detalhes.parcelas_em_aberto;
                            });
                    }
                    
                    // Event listeners
                    anoSelect.addEventListener('change', atualizarProjecao);
                    mesSelect.addEventListener('change', atualizarProjecao);
                    
                    // Carrega os anos disponíveis ao iniciar
                    carregarAnos();
                    
                    // Seleciona o mês atual por padrão
                    mesSelect.value = new Date().getMonth() + 1;
                });



                // Inicializar o mapa
                var map = L.map('map').setView([<?php echo $latitude; ?>, <?php echo $longitude; ?>], 13);

                // Adicionar camada de mapa (usando OpenStreetMap como base)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Adicionar marcador com as coordenadas da empresa
                var marker = L.marker([<?php echo $latitude; ?>, <?php echo $longitude; ?>]).addTo(map);

                // Centralizar o mapa nas coordenadas da empresa
                map.setView([<?php echo $latitude; ?>, <?php echo $longitude; ?>], 13);



                document.addEventListener('DOMContentLoaded', function() {
                    const anoSelectSocios = document.getElementById('anoSelectSocios');
                    const mesSelectSocios = document.getElementById('mesSelectSocios');
                    const sociosContainer = document.getElementById('sociosContainer');
                    const template = document.getElementById('socioCardTemplate');
                    
                    // Função para carregar anos disponíveis
                    function carregarAnosSocios() {
                        const anoAtual = new Date().getFullYear();
                        anoSelectSocios.innerHTML = '<option value="">Selecione o Ano</option>';
                        
                        // Carregar anos de 2023 até o ano atual
                        for (let ano = anoAtual; ano >= 2023; ano--) {
                            const option = document.createElement('option');
                            option.value = ano;
                            option.textContent = ano;
                            if (ano === anoAtual) option.selected = true;
                            anoSelectSocios.appendChild(option);
                        }
                        
                        // Carrega os dados iniciais
                        atualizarDashboardSocios();
                    }
                    
                    function atualizarDashboardSocios() {
                        const ano = anoSelectSocios.value || new Date().getFullYear();
                        const mes = mesSelectSocios.value || (new Date().getMonth() + 1);
                        
                        sociosContainer.innerHTML = '<div class="text-center">Carregando...</div>';
                        
                        fetch(`dashboard_socios.php?ano=${ano}&mes=${mes}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    sociosContainer.innerHTML = '';
                                    
                                    // Adiciona informação do faturamento total do período
                                    const headerDiv = document.createElement('div');
                                    headerDiv.className = 'mb-4 p-4 bg-blue-100 rounded';
                                    headerDiv.innerHTML = `
                                        <h4 class="font-bold">Faturamento do Período</h4>
                                        <p>R$ ${formatarMoeda(data.periodo.faturamento_total)}</p>
                                    `;
                                    sociosContainer.appendChild(headerDiv);
                                    
                                    const sociosGrid = document.createElement('div');
                                    sociosGrid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4';
                                    
                                    data.socios.forEach(socio => {
                                        const clone = template.content.cloneNode(true);
                                        
                                        clone.querySelector('h4').textContent = socio.nome;
                                        clone.querySelector('.porcentagem-sociedade').textContent = 
                                            Number(socio.porcentagem_sociedade).toFixed(2);
                                        clone.querySelector('.porcentagem-comissao').textContent = 
                                            Number(socio.porcentagem_comissao).toFixed(2);
                                        clone.querySelector('.pro-labore-base').textContent = formatarMoeda(socio.pro_labore_base);
                                        clone.querySelector('.pro-labore-retirado').textContent = formatarMoeda(socio.pro_labore_retirado);
                                        clone.querySelector('.pro-labore-disponivel').textContent = formatarMoeda(socio.pro_labore_disponivel);
                                        clone.querySelector('.comissao').textContent = formatarMoeda(socio.comissao);
                                        clone.querySelector('.valor-disponivel').textContent = 
                                            formatarMoeda(socio.valor_disponivel);
                                        
                                        sociosGrid.appendChild(clone);
                                    });
                                    
                                    sociosContainer.appendChild(sociosGrid);
                                } else {
                                    sociosContainer.innerHTML = 
                                        '<div class="text-center text-red-600">Erro ao carregar os dados: ' + 
                                        data.message + '</div>';
                                }
                            })
                            .catch(error => {
                                console.error('Erro:', error);
                                sociosContainer.innerHTML = 
                                    '<div class="text-center text-red-600">Erro ao carregar os dados</div>';
                            });
                    }
                    
                    function formatarMoeda(valor) {
                        return new Intl.NumberFormat('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(valor);
                    }
                    
                    // Event listeners
                    anoSelectSocios.addEventListener('change', atualizarDashboardSocios);
                    mesSelectSocios.addEventListener('change', atualizarDashboardSocios);
                    
                    // Define valores iniciais
                    mesSelectSocios.value = new Date().getMonth() + 1;
                    
                    // Inicializa o dashboard
                    carregarAnosSocios();
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const filtrosForm = document.getElementById('filtrosGraficos');
                    const anoFiltro = document.getElementById('anoFiltro');
                    const mesFiltro = document.getElementById('mesFiltro');
                    
                    // Definir valores iniciais
                    anoFiltro.value = new Date().getFullYear();
                    mesFiltro.value = new Date().getMonth() + 1;
                    
                    // Função para atualizar todos os gráficos
                    function atualizarGraficos() {
                        const ano = anoFiltro.value;
                        const mes = mesFiltro.value;
                        
                        fetch(`buscar_dados_graficos.php?ano=${ano}&mes=${mes}`)
                            .then(response => response.json())
                            .then(data => {
                                // Atualizar gráfico de Despesas Fixas Mensais
                                atualizarGraficoDespesasFixas(data.despesasFixas);
                                
                                // Atualizar gráfico de Serviços por Status
                                atualizarGraficoServicosStatus(data.servicosStatus);
                                
                                // Atualizar gráfico de Projeção Financeira
                                atualizarGraficoProjecao(data.projecaoFinanceira);
                                
                                // Atualizar gráfico de Entradas Mensais
                                atualizarGraficoEntradas(data.entradasMensais);
                                
                                // Atualizar gráfico de Despesas Gerais
                                atualizarGraficoDespesasGerais(data.projecoesMensais);
                            })
                            .catch(error => console.error('Erro ao atualizar gráficos:', error));
                    }
                    
                    // Funções para atualizar cada gráfico individualmente
                    function atualizarGraficoDespesasFixas(dados) {
                        despesasFixasMensaisChart.data.datasets[0].data = dados;
                        despesasFixasMensaisChart.update();
                    }
                    
                    function atualizarGraficoServicosStatus(dados) {
                        servicosStatusChart.data.labels = Object.keys(dados);
                        servicosStatusChart.data.datasets[0].data = Object.values(dados);
                        servicosStatusChart.update();
                    }
                    
                    function atualizarGraficoProjecao(dados) {
                        projecaoMensalChart.data.datasets[0].data = dados;
                        projecaoMensalChart.update();
                    }
                    
                    function atualizarGraficoEntradas(dados) {
                        entradasMensaisChart.data.datasets[0].data = dados;
                        entradasMensaisChart.update();
                    }
                    
                    function atualizarGraficoDespesasGerais(dados) {
                        try {
                            if (typeof projecaoMensalChart !== 'undefined' && projecaoMensalChart !== null) {
                                // Usar os dados recebidos ou um array de zeros como fallback
                                const valoresProjecao = Array(12).fill(0); // Array padrão com zeros
                                
                                // Se houver dados de projeção, atualizar o array
                                if (dados && Array.isArray(dados)) {
                                    dados.forEach((valor, index) => {
                                        valoresProjecao[index] = valor;
                                    });
                                }

                                // Atualizar o gráfico com os valores
                                projecaoMensalChart.data.datasets[0].data = valoresProjecao;
                                projecaoMensalChart.update();
                            } else {
                                console.error('Gráfico não inicializado');
                            }
                        } catch (error) {
                            console.error('Erro ao atualizar gráfico de projeção mensal:', error);
                        }
                    }
                    
                    // Event listeners para os filtros
                    anoFiltro.addEventListener('change', atualizarGraficos);
                    mesFiltro.addEventListener('change', atualizarGraficos);
                    
                    // Carregar dados iniciais
                    atualizarGraficos();
                });

                console.log('Valores da projeção:', <?php echo json_encode(array_values($projecoesMensais)); ?>);
            </script>
        </div>
    </div>
</body>
</html>