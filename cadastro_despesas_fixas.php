<?php 
include 'conexao.php';

function getAnosCadastrados() {
    global $conn;
    $query = "SELECT DISTINCT YEAR(data) as ano FROM despesas_fixas ORDER BY ano DESC";
    $result = $conn->query($query);
    
    $anos = array();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $anos[] = $row['ano'];
        }
    }
    return $anos;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Despesas Fixas</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #838282;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --sidebar-width: 250px;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --primary-dark: #1e40af;
            --background-color: #ffffff;
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 2rem auto;
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

        .form {
            padding: 20px; /* Espaçamento interno */
            border-radius: 8px; /* Bordas arredondadas */
            background: #f8f9fa; /* Fundo suave para o formulário */
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1); /* Sombra leve */
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            transition: border-color 0.2s; /* Transição suave para a borda */
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus {
            border-color: var(--accent-color); /* Cor da borda ao focar */
            outline: none; /* Remove o contorno padrão */
        }

        .btn-group {
            display: flex;
            justify-content: center; /* Centraliza os botões horizontalmente */
            gap: 10px; /* Espaçamento entre os botões */
            margin-top: 20px; /* Adiciona um espaço acima dos botões */
        }

        .btn {
            padding: 10px 20px; /* Aumenta o padding para um botão mais espaçoso */
            border-radius: 5px; /* Bordas arredondadas */
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s; /* Transições suaves */
        }

        .btn-primary {
            background: #007bff; /* Cor do botão primário */
            color: white; /* Cor do texto */
        }

        .btn-primary:hover {
            background: #0056b3; /* Cor ao passar o mouse */
        }

        .btn-danger {
            background: #dc3545; /* Cor do botão de perigo */
            color: white; /* Cor do texto */
        }

        .btn-danger:hover {
            background: #c82333; /* Cor ao passar o mouse */
        }

        .alert {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Estilos para o formulário */
        .form-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eef2f7;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group label.required:after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dce0e4;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            margin-top: -30px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .total-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .popup {
            display: none; /* Inicialmente escondido */
            position: fixed; /* Fixa na tela */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fundo semi-transparente */
            justify-content: center; /* Centraliza horizontalmente */
            align-items: center; /* Centraliza verticalmente */
            z-index: 1000; /* Coloca o popup acima de outros elementos */
        }

        .popup-content {
            background: white; /* Fundo branco para o conteúdo do popup */
            padding: 20px;
            border-radius: 8px; /* Bordas arredondadas */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Sombra leve */
            width: 400px; /* Largura do popup */
            max-width: 90%; /* Largura máxima */
            text-align: center; /* Centraliza o texto */
        }

        .popup-content h2 {
            color: var(--primary-color); /* Cor do título */
            margin-bottom: 15px; /* Espaçamento abaixo do título */
        }

        .popup-content p {
            margin-bottom: 20px; /* Espaçamento abaixo do parágrafo */
            color: #495057; /* Cor do texto */
        }

        .popup-content label {
            display: block; /* Cada checkbox em uma nova linha */
            margin: 10px 0; /* Espaçamento entre os checkboxes */
            color: #333; /* Cor do texto dos checkboxes */
        }

        .btn-group {
            display: flex;
            justify-content: space-between; /* Espaçamento entre os botões */
            margin-top: 20px; /* Espaçamento acima dos botões */
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap; /* Permite que os checkboxes se movam para a próxima linha se não houver espaço */
            gap: 1rem; /* Espaçamento entre os checkboxes */
            margin-top: 0.5rem; /* Espaçamento acima do grupo de checkboxes */
        }

        .meses-container label {
            display: flex;
            align-items: center; /* Alinha o texto e o checkbox verticalmente */
            cursor: pointer; /* Muda o cursor para indicar que é clicável */
            font-size: 0.95rem; /* Tamanho da fonte */
            color: var(--text-color); /* Cor do texto */
        }

        .meses-container input[type="checkbox"] {
            margin-right: 0.5rem; /* Espaçamento entre o checkbox e o texto */
            transform: scale(1.2); /* Aumenta o tamanho do checkbox */
            cursor: pointer; /* Muda o cursor para indicar que é clicável */
        }

        /* Efeito de foco para acessibilidade */
        .meses-container input[type="checkbox"]:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.3); /* Sombra ao focar no checkbox */
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            color: white;
            cursor: pointer;
            z-index: 1;
            transition: transform 0.2s;
        }

        .close:hover {
            transform: scale(1.1);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        /* Estilo para os botões dentro do popup */
        .popup-content .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .popup-content .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
        }

        .popup-content .btn-primary:hover {
            background-color: #2980b9;
        }

        .popup-content .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
        }

        .popup-content .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-replicar {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-replicar:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Estilo para a seção de listagem */
        .form-section {
            margin-top: 30px; /* Espaçamento acima da seção */
            padding: 20px; /* Espaçamento interno */
            background: #f8f9fa; /* Fundo suave para a seção */
            border-radius: 8px; /* Bordas arredondadas */
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1); /* Sombra leve */
        }

        .form-section h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center; /* Centraliza o título */
            font-size: 1.8rem;
        }

        .table-responsive {
            overflow-x: auto; /* Permite rolagem horizontal em telas pequenas */
            margin: 20px 0; /* Margem acima e abaixo da tabela */
        }

        table {
            width: 100%;
            border-collapse: collapse; /* Remove espaços entre as células */
            background: white; /* Fundo branco para a tabela */
            border-radius: 8px; /* Bordas arredondadas */
            overflow: hidden; /* Para bordas arredondadas */
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1); /* Sombra leve */
        }

        th, td {
            padding: 12px; /* Espaçamento interno das células */
            text-align: left; /* Alinhamento do texto à esquerda */
            border-bottom: 1px solid #dee2e6; /* Linha de separação entre as linhas */
        }

        th {
            background: #f8f9fa; /* Fundo suave para o cabeçalho */
            font-weight: 600; /* Negrito para o cabeçalho */
            color: #495057; /* Cor do texto do cabeçalho */
        }

        tr:hover {
            background-color: #f1f1f1; /* Efeito de hover nas linhas */
        }

        .total-box {
            margin-top: 20px; /* Espaçamento acima da caixa de total */
            padding: 10px; /* Espaçamento interno */
            background: #e9ecef; /* Fundo suave para a caixa de total */
            border-radius: 5px; /* Bordas arredondadas */
            text-align: center; /* Centraliza o texto */
            font-size: 1.2rem; /* Tamanho da fonte */
            font-weight: bold; /* Negrito */
        }

        #total-despesas {
            color: #28a745; /* Cor verde para o total */
        }

        .meses-container {
            display: flex;
            flex-wrap: wrap; /* Permite que os itens quebrem para a próxima linha */
            justify-content: center; /* Centraliza os itens */
            margin: 20px 0; /* Margem acima e abaixo da lista de meses */
        }

        .meses-container label {
            flex: 0 0 30%; /* Cada label ocupa 30% da largura do contêiner */
            box-sizing: border-box; /* Inclui padding e border no cálculo da largura */
            margin: 5px; /* Margem entre os checkboxes */
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-shadow: var(--shadow-md);
            white-space: nowrap; /* Impede quebra de linha */
            overflow: hidden; /* Oculta texto que excede a largura */
            text-overflow: ellipsis; /* Adiciona reticências para texto que não cabe */
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <!-- Seção de Cadastro -->
            <div class="form-section">
                <h2>Cadastro de Despesas Fixas</h2>
                <form id="formDespesaFixa" onsubmit="return salvarDespesa(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mes" class="required">Mês</label>
                            <select name="mes" id="mes" required>
                                <option value="">Selecione...</option>
                                <option value="01">Janeiro</option>
                                <option value="02">Fevereiro</option>
                                <option value="03">Março</option>
                                <option value="04">Abril</option>
                                <option value="05">Maio</option>
                                <option value="06">Junho</option>
                                <option value="07">Julho</option>
                                <option value="08">Agosto</option>
                                <option value="09">Setembro</option>
                                <option value="10">Outubro</option>
                                <option value="11">Novembro</option>
                                <option value="12">Dezembro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ano" class="required">Ano</label>
                            <select name="ano" id="ano" required>
                                <option value="">Selecione...</option>
                                <?php
                                $anoAtual = date('Y');
                                for($i = $anoAtual - 1; $i <= $anoAtual + 1; $i++) {
                                    echo "<option value='$i'" . ($i == $anoAtual ? " selected" : "") . ">$i</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="descricao" class="required">Descrição da despesa</label>
                            <input type="text" name="descricao" id="descricao" required>
                        </div>
                        <div class="form-group">
                            <label for="valor" class="required">Valor</label>
                            <div class="input-money">
                                <input type="number" name="valor" id="valor" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Seção de Listagem -->
            <div class="form-section">
                <h2>Despesas Fixas Cadastradas</h2>
                <div class="table-responsive">
                    <table id="tabelaDespesas">
                        <thead>
                            <tr>
                                <th>Nome da Despesa</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- As despesas serão carregadas via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="total-box">
                    <strong>Total do Mês: </strong>
                    <span id="total-despesas">R$ 0,00</span>
                </div>
            </div>

            <!-- Seção de Exportação -->
            <div class="form-section">
                <h2>Exportar Despesas</h2>
                <form id="exportForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exportType">Tipo de Exportação</label>
                            <select name="exportType" id="exportType" required>
                                <option value="month">Mês Específico</option>
                                <option value="year">Ano Completo</option>
                                <option value="total">Total Geral</option>
                            </select>
                        </div>
                        <div class="form-group" id="exportMonthGroup">
                            <label for="exportMonth">Mês</label>
                            <select name="exportMonth" id="exportMonth">
                                <!-- Options mantidas como estão -->
                            </select>
                        </div>
                        <div class="form-group" id="exportYearGroup">
                            <label for="exportYear">Ano</label>
                            <select name="exportYear" id="exportYear" class="form-control">
                                <option value="">Selecione o ano...</option>
                                <?php
                                $anosCadastrados = getAnosCadastrados();
                                foreach($anosCadastrados as $ano) {
                                    echo "<option value='{$ano}'>{$ano}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> Exportar para PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Replicação -->
    <div class="popup" id="replicarDespesaPopup">
        <div class="popup-content">
            <h2>Replicar Despesa</h2>
            <p>Selecione os meses para replicar:</p>
            <form id="replicarForm" onsubmit="return false;">
                <div class="meses-container">
                    <label><input type="checkbox" name="meses[]" value="Janeiro"> Janeiro</label>
                    <label><input type="checkbox" name="meses[]" value="Fevereiro"> Fevereiro</label>
                    <label><input type="checkbox" name="meses[]" value="Março"> Março</label>
                    <label><input type="checkbox" name="meses[]" value="Abril"> Abril</label>
                    <label><input type="checkbox" name="meses[]" value="Maio"> Maio</label>
                    <label><input type="checkbox" name="meses[]" value="Junho"> Junho</label>
                    <label><input type="checkbox" name="meses[]" value="Julho"> Julho</label>
                    <label><input type="checkbox" name="meses[]" value="Agosto"> Agosto</label>
                    <label><input type="checkbox" name="meses[]" value="Setembro"> Setembro</label>
                    <label><input type="checkbox" name="meses[]" value="Outubro"> Outubro</label>
                    <label><input type="checkbox" name="meses[]" value="Novembro"> Novembro</label>
                    <label><input type="checkbox" name="meses[]" value="Dezembro"> Dezembro</label>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" onclick="closePopup('replicarDespesaPopup')">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarReplicacao()">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let despesaParaReplicar = null;
        $(document).ready(function() {
            function buscarDespesas() {
                const mes = $('#mes').val();
                const ano = $('#ano').val();
                
                if (mes && ano) {
                    $.ajax({
                        url: 'buscar_despesas.php',
                        type: 'GET',
                        data: { 
                            mes: mes,
                            ano: ano 
                        },
                        success: function(response) {
                            let html = '';
                            let total = 0;
                            
                            try {
                                // Verifica se response já é um objeto (não precisa fazer parse)
                                const despesas = typeof response === 'string' ? JSON.parse(response) : response;
                                
                                if (Array.isArray(despesas) && despesas.length > 0) {
                                    despesas.forEach(function(despesa) {
                                        // Converter o valor para número, removendo R$ e trocando , por .
                                        const valorNumerico = parseFloat(despesa.valor.replace('R$', '').replace('.', '').replace(',', '.'));
                                        total += valorNumerico;
                                        
                                        html += `
                                            <tr id="linha-${despesa.id}">
                                                <td>${despesa.descricao}</td>
                                                <td class="valor-despesa">R$ ${despesa.valor}</td>
                                                <td>
                                                    <div class="btn-actions">
                                                        <button type="button" class="btn-replicar" onclick="abrirModalReplicar(${despesa.id})">
                                                            <i class="fas fa-copy"></i> 
                                                        </button>
                                                        <button type="button" class="btn btn-danger" onclick="excluirDespesa(${despesa.id})">
                                                            <i class="fas fa-trash"></i> 
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                } else {
                                    html = '<tr><td colspan="3">Nenhuma despesa encontrada para o período selecionado.</td></tr>';
                                }
                                
                                $('#tabelaDespesas tbody').html(html);
                                $('#total-despesas').text(`R$ ${total.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`);
                            } catch (e) {
                                console.error('Erro ao processar resposta:', e);
                                console.error('Resposta recebida:', response);
                                alert('Erro ao processar os dados das despesas.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro na requisição:', error);
                            alert('Erro ao buscar as despesas.');
                        }
                    });
                }
            }

            // Atualiza a tabela quando mês ou ano são alterados
            $('#mes, #ano').change(buscarDespesas);
            
            // Busca inicial se já houver mês e ano selecionados
            if ($('#mes').val() && $('#ano').val()) {
                buscarDespesas();
            }

            // Preencher o select de meses
            const meses = [
                { value: "01", text: "Janeiro" },
                { value: "02", text: "Fevereiro" },
                { value: "03", text: "Março" },
                { value: "04", text: "Abril" },
                { value: "05", text: "Maio" },
                { value: "06", text: "Junho" },
                { value: "07", text: "Julho" },
                { value: "08", text: "Agosto" },
                { value: "09", text: "Setembro" },
                { value: "10", text: "Outubro" },
                { value: "11", text: "Novembro" },
                { value: "12", text: "Dezembro" }
            ];

            // Preencher o select de meses
            const $exportMonth = $('#exportMonth');
            meses.forEach(mes => {
                $exportMonth.append(new Option(mes.text, mes.value));
            });

            // Atualizar anos disponíveis quando uma nova despesa for cadastrada
            function atualizarAnosDisponiveis() {
                $.ajax({
                    url: 'buscar_anos.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(anos) {
                        const selectAno = $('#exportYear');
                        selectAno.empty();
                        selectAno.append($('<option>', {
                            value: '',
                            text: 'Selecione o ano...'
                        }));
                        
                        anos.forEach(function(ano) {
                            selectAno.append($('<option>', {
                                value: ano,
                                text: ano
                            }));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao buscar anos:', error);
                    }
                });
            }

            // Chamar a função inicialmente
            atualizarAnosDisponiveis();

            // Atualizar anos após salvar uma nova despesa
            $(document).on('despesaSalva', function() {
                atualizarAnosDisponiveis();
            });

            // Setar mês e ano atuais como padrão
            const currentMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
            $('#exportMonth').val(currentMonth);
            $('#exportYear').val(currentYear);

            // Trigger change event to set initial visibility
            $('#exportType').trigger('change');
        });

        function abrirModalReplicar(despesaId) {
            despesaParaReplicar = despesaId;
            // Limpar checkboxes anteriores
            document.querySelectorAll('.meses-container input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            // Exibir o modal
            document.getElementById('replicarDespesaPopup').style.display = 'flex';
        }

        function closePopup(id) {
            document.getElementById(id).style.display = 'none';
            despesaParaReplicar = null;
        }

        function confirmarReplicacao() {
            const mesesSelecionados = [];
            document.querySelectorAll('.meses-container input:checked').forEach(checkbox => {
                mesesSelecionados.push(checkbox.value);
            });

            if (mesesSelecionados.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione pelo menos um mês para replicar.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Adicionar console.log para debug
            console.log('Despesa ID:', despesaParaReplicar);
            console.log('Meses selecionados:', mesesSelecionados);
            console.log('Ano:', $('#ano').val());

            $.ajax({
                url: 'replicar_despesa.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    despesa_id: despesaParaReplicar,
                    meses: mesesSelecionados,
                    ano: $('#ano').val()
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                            confirmButtonColor: '#3085d6'
                        }).then((result) => {
                            closePopup('replicarDespesaPopup');
                            buscarDespesas();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.error || 'Erro ao replicar despesa',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao replicar a despesa. Verifique o console para mais detalhes.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        }
        
        function excluirDespesa(despesaId) {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: "Você tem certeza que deseja excluir esta despesa?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'excluir_despesa_fixa.php',
                        type: 'POST',
                        data: { id: despesaId },
                        success: function(response) {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    $(`#linha-${despesaId}`).remove();
                                    atualizarTotalDespesas();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro!',
                                    text: 'Erro ao excluir despesa: ' + data.message,
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: 'Erro ao realizar a requisição.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        }

        // Add this new function for PDF export
        function exportToPDF() {
            const exportType = $('#exportType').val();
            const exportMonth = $('#exportMonth').val();
            const exportYear = $('#exportYear').val();

            $.ajax({
                url: 'gerar_pdf.php',
                type: 'POST',
                data: {
                    exportType: exportType,
                    exportMonth: exportMonth,
                    exportYear: exportYear
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    const blob = new Blob([response], { type: 'application/pdf' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'despesas.pdf';
                    link.click();
                },
                error: function() {
                    alert('Erro ao gerar o PDF.');
                }
            });
        }

        // Show/hide month and year selects based on export type
        $('#exportType').change(function() {
            const exportType = $(this).val();
            if (exportType === 'month') {
                $('#exportMonthGroup, #exportYearGroup').show();
            } else if (exportType === 'year') {
                $('#exportMonthGroup').hide();
                $('#exportYearGroup').show();
            } else {
                $('#exportMonthGroup, #exportYearGroup').hide();
            }
        });

        function atualizarTotalDespesas() {
            let total = 0;

            // Iterar sobre as linhas da tabela e somar os valores
            $('#tabelaDespesas tbody tr').each(function() {
                const valor = parseFloat($(this).find('.valor-despesa').text().replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;
                total += valor;
            });

            // Atualizar o total no DOM
            $('#total-despesas').text('R$ ' + total.toFixed(2).replace('.', ','));
        }

        function salvarDespesa(event) {
            event.preventDefault();

            // Validação básica
            const form = document.getElementById('formDespesaFixa');
            if (!form.checkValidity()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Por favor, preencha todos os campos obrigatórios.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Salvando...',
                text: 'Por favor, aguarde.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar dados via AJAX
            $.ajax({
                url: 'salvar_despesa_fixa.php',
                type: 'POST',
                data: {
                    mes: $('#mes').val(),
                    ano: $('#ano').val(),
                    descricao: $('#descricao').val(),
                    valor: $('#valor').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Despesa salva com sucesso!',
                            confirmButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Limpar formulário
                                form.reset();
                                // Atualizar a lista de despesas
                                buscarDespesas();
                                // Disparar evento de despesa salva
                                $(document).trigger('despesaSalva');
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.error || 'Erro ao salvar despesa.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao salvar a despesa. Por favor, tente novamente.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });

            return false;
        }
    </script>

</body>
</html>
