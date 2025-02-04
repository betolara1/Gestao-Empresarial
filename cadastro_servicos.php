<?php
include 'conexao.php';

// Verifica se é uma requisição AJAX para buscar dados do cliente
if (isset($_POST['buscar_cliente']) && isset($_POST['cliente_id'])) {
    $cliente_id = $_POST['cliente_id'];
    $sql_cliente = "SELECT cnpj, cpf FROM cliente WHERE id = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente = $result_cliente->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($cliente);
    $stmt_cliente->close();
    exit;
}

try {
    // Busca o último número da proposta e incrementa para o próximo
    $sql_proposta = "SELECT COALESCE(MAX(numero_proposta), 0) + 1 AS proximo_numero FROM servicos FOR UPDATE";
    $result_proposta = $conn->query($sql_proposta);
    $row_proposta = $result_proposta->fetch_assoc();
    $numero_proposta = $row_proposta['proximo_numero'];

    // Consulta SQL para buscar todos os tipos de serviços
    $sqlTipoServico = "SELECT id, tipo_servico FROM tipos_servicos";
    $resultTipoServico = $conn->query($sqlTipoServico);
    $tipos_servico = $resultTipoServico->fetch_all(MYSQLI_ASSOC);

    // Buscar despesas existentes
    $sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();
    $despesas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Commit a transação
    $conn->commit();

    // Se for uma requisição AJAX, retorna JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'numero_proposta' => $numero_proposta,
            'tipos_servico' => $tipos_servico,
            'despesas' => $despesas
        ]);
        exit;
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Serviço</title>
    <style>
        /* Estilos Gerais */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #838282;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --background-color: #f4f7fa;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Estilos do Header */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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

        /* Estilos do Card */
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .form-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1; /* Cada grupo ocupa o mesmo espaço */
            margin-right: 15px; /* Espaçamento entre os grupos */
        }

        .form-group:last-child {
            margin-right: 0; /* Remove margem do último grupo */
        }

        .form-control {
            width: 100%; /* Largura total */
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            box-sizing: border-box; /* Inclui padding e border no cálculo da largura */
        }

        .form-actions {
            display: flex;
            justify-content: left;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            margin-top: -30px;
        }

        .btn {
            padding: 8px 16px; /* Ajuste o padding para combinar com o estilo */
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        /* Estilos dos Botões */
        .btn-primary {
            background: #007bff; /* Cor do botão primário */
            color: white; /* Cor do texto */
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Cor ao passar o mouse */
        }

        .btn-secondary {
            background-color: var(--accent-color); /* Cor do botão secundário */
            color: white; /* Cor do texto */
        }

        .btn-secondary:hover {
            background-color: #c82333; /* Cor ao passar o mouse */
        }

        /* Estilos das Células de Status */
        .status-cell {
            padding: 5px;
            border-radius: 4px;
            color: white; /* Cor do texto */
        }

        .status-concluido {
            background-color: #28a745; /* Verde para Concluído */
        }

        .status-em-andamento {
            background-color: #ffc107; /* Amarelo para Em Andamento */
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

        .disabled-field {
            background-color: #d3d3d3; /* Cor cinza */
            color: #a9a9a9; /* Cor do texto cinza */
            cursor: not-allowed; /* Cursor de não permitido */
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap; /* Permite que os checkboxes quebrem para a próxima linha */
            gap: 15px; /* Espaçamento entre os checkboxes */
            justify-content: center; /* Centraliza os checkboxes horizontalmente */
        }

        .form-check {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: white; /* Cor de fundo */
            transition: background-color 0.3s, border-color 0.3s; /* Transições suaves */
        }

        .form-check:hover {
            background-color: #f0f0f0; /* Cor de fundo ao passar o mouse */
            border-color: var(--primary-color); /* Cor da borda ao passar o mouse */
        }

        .form-check-input {
            margin-right: 10px; /* Espaçamento entre o checkbox e o texto */
            cursor: pointer; /* Cursor de ponteiro */
        }

        .form-check-label {
            cursor: pointer; /* Cursor de ponteiro */
        }

        .form-group input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 400px;
            position: relative;
        }

        .popup h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.5em;
            text-align: center;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        #despesaForm {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #despesaForm label {
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
        }

        #despesaForm input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        #despesaForm input:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        #despesaForm button {
            background-color: #4a90e2;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        #despesaForm button:hover {
            background-color: #357abd;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden; /* Para bordas arredondadas */
        }

        th, td {
            padding: 10px; /* Aumenta o espaçamento */
            text-align: center;
            border: 1px solid var(--border-color);
            width: 10%; /* Define uma largura mínima para as colunas */
            white-space: nowrap; /* Impede a quebra de linha */
            overflow: hidden; /* Oculta o texto que excede a largura da célula */
            text-overflow: ellipsis; /* Adiciona reticências (...) para texto que não cabe */
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; /* Cor de fundo alternada para linhas */
        }

        tr:hover {
            background-color: #e9ecef; /* Cor de fundo ao passar o mouse */
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-cancelar {
            background-color: #dc3545;
        }

        .btn-cancelar:hover {
            background-color: #c82333;
        }

        .btn-editar, .btn-excluir {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 0.9rem;
        }

        .btn-excluir {
            background-color: #e74c3c;
        }

        .btn-excluir:hover {
            background-color: #c0392b;
        }

        /* Adicione estes estilos CSS */
        .form-group label.required:after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

        .required-tooltip {
            position: relative;
        }

        .required-tooltip:hover:after {
            content: "Campo obrigatório";
            position: absolute;
            background: #34495e;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            bottom: 100%;
            left: 0;
            white-space: nowrap;
            margin-bottom: 5px;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">

            <h1>Cadastro de Serviços</h1>
            <form action="salvar_servico.php" method="POST">
                <div class="form-section">
                    <h2>Informações do Serviço</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_proposta" class="required"><i class="fas fa-file-alt"></i> Número da Proposta</label>
                            <input type="text" id="numero_proposta" name="numero_proposta" value="<?php echo htmlspecialchars($numero_proposta); ?>" required onchange="verificarNumeroProposta(this.value)">
                            <small id="numero_proposta_feedback" style="display: none;"></small>
                        </div>
                        <div class="form-group">
                            <label for="cliente" class="required"><i class="fas fa-user"></i> Cliente</label>
                            <select id="cliente" name="cliente" onchange="buscarCNPJCPF(this.value)" required>
                                <option value="">Selecione...</option>
                                <?php
                                $clientes = $conn->query("SELECT id, IFNULL(razao_social, nome) AS nome FROM cliente");
                                while ($cliente = $clientes->fetch_assoc()) {
                                    echo "<option value='{$cliente['id']}'>{$cliente['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cnpj_cpf" class="required"><i class="fas fa-id-card"></i> CNPJ/CPF</label>
                                <input type="text" id="cnpj_cpf" name="cnpj_cpf" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Tipos de Serviço</h2>
                    <div class="checkbox-group">
                        <?php foreach ($tipos_servico as $servico): ?>
                            <div class='form-check'>
                                <input class='form-check-input' type='checkbox' required
                                    name='tipo_servico[]' 
                                    id='servico_<?php echo htmlspecialchars($servico['id']); ?>' 
                                    value='<?php echo htmlspecialchars($servico['id']); ?>' >
                                <label class='form-check-label' 
                                    for='servico_<?php echo htmlspecialchars($servico['id']); ?>'>
                                    <?php echo htmlspecialchars($servico['tipo_servico']); ?> 
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Status do Serviço</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_inicio" class="required"><i class="fas fa-calendar-plus"></i> Data de Início do Serviço</label>
                            <input type="date" id="data_inicio" name="data_inicio" required>
                        </div>

                        <div class="form-group">
                            <label for="data_termino"><i class="fas fa-calendar-check"></i> Data de Término do Serviço</label>
                            <input type="date" id="data_termino" name="data_termino">
                        </div>

                        <div class="form-group">
                            <label for="status_servico"><i class="fas fa-info-circle"></i> Status do Serviço</label>
                            <input type="text" id="status_servico" name="status_servico" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Informações do Pagamento</h2>
                    <div class="form-row">
                            <div class="form-group">
                                <label for="valor_total" class="required"><i class="fas fa-dollar-sign"></i> Valor Total</label>
                                <input type="number" id="valor_total" name="valor_total" step="0.01" required onchange="verificarValores()">
                            </div>

                        <div class="form-group">
                            <label for="valor_entrada"><i class="fas fa-money-bill-wave"></i> Valor Entrada</label>
                            <input type="number" id="valor_entrada" name="valor_entrada" step="0.01" onchange="verificarValores()">
                        </div>

                        <div class="form-group">
                            <label for="data_pagamento"><i class="fas fa-calendar-alt"></i> Vencimento</label>
                            <input type="date" id="data_pagamento" name="data_pagamento">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="forma_pagamento" class="required"><i class="fas fa-credit-card"></i> Forma de Pagamento</label>
                            <select id="forma_pagamento" name="forma_pagamento" required>
                                <option value="">Selecione a forma de pagamento</option>
                                <option value="CARTÃO DE CRÉDITO">Cartão de Crédito</option>
                                <option value="CARTÃO DE DÉBITO">Cartão de Débito</option>
                                <option value="PIX">PIX</option>
                                <option value="DINHEIRO">Dinheiro</option>
                                <option value="BOLETO">Boleto</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="parcelamento"><i class="fas fa-clock"></i> Parcelamento</label>
                            <select id="parcelamento" name="parcelamento">
                                <option value="">Selecione o parcelamento</option>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    echo "<option value=\"$i\">{$i}x</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group"></div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Endereço do Serviço</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep" class="required"><i class="fas fa-map-marker-alt"></i> CEP:</label>
                            <input type="text" id="cep" name="cep" required placeholder="00000-000">
                            <small id="cep-feedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label for="rua"><i class="fas fa-road"></i> Rua:</label>
                            <input type="text" id="rua" name="rua" readonly placeholder="Endereço">
                        </div>
                        <div class="form-group">
                            <label for="numero" class="required"><i class="fas fa-home"></i> Número:</label>
                            <input type="text" id="numero" name="numero" required placeholder="Número">
                        </div>
                        <div class="form-group">
                            <label for="complemento"><i class="fas fa-info"></i> Complemento:</label>
                            <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc.">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro"><i class="fas fa-map"></i> Bairro:</label>
                            <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro">
                        </div>
                        <div class="form-group">
                            <label for="cidade"><i class="fas fa-city"></i> Cidade:</label>
                            <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade">
                        </div>
                        <div class="form-group">
                            <label for="estado"><i class="fas fa-flag"></i> Estado:</label>
                            <input type="text" id="estado" name="estado" readonly placeholder="Estado">
                        </div>
                        <div class="form-group">
                            <label for="coordenada"><i class="fas fa-map-pin"></i> Coordenada:</label>
                            <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude">
                            <small id="coordenadas-feedback" class="form-text"></small>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="responsavel_execucao" class="required"><i class="fas fa-user-tie"></i> Nome do Responsável pela Execução</label>
                        <input type="text" id="responsavel_execucao" name="responsavel_execucao" required>
                    </div>

                    <div class="form-group">
                        <label for="origem_demanda" class="required"><i class="fas fa-bullhorn"></i> Origem da Demanda</label>
                        <select id="origem_demanda" name="origem_demanda" required>
                            <option value="">Selecione...</option>
                            <option value="INTERNET">Internet</option>
                            <option value="FACEBOOK">Facebook</option>
                            <option value="INDICACAO">Indicação</option>
                            <option value="OUTRO">Outro</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="observacao"><i class="fas fa-comment"></i> Observação:</label>
                        <textarea id="observacao" name="observacao" class="form-control" rows="4" placeholder="Digite sua observação aqui"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Cadastro de Despesas</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <table id="tabelaDespesas" border="1">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Valor</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="despesasBody">
                                    <tr><td colspan="3">Nenhuma despesa cadastrada</td></tr>
                                </tbody>
                            </table>
                            <br>
                            <button type="button" class="btn btn-primary" onclick="openPopup()">
                                <i class="fa fa-plus"></i> Adicionar Despesa
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Cadastrar Serviço</button>
                </div>
            </form>
        </div>

        <!-- Popup -->
        <div id="popup" class="popup" onclick="closePopupOutside(event)">
            <div class="popup-content">
                <span class="close" onclick="closePopup()">&times;</span>
                <h2>Adicionar Nova Despesa</h2>
                <form id="despesaForm">
                    <div class="form-field">
                        <label for="nome_despesa">Nome da Despesa</label>
                        <input type="text" id="nome_despesa" name="nome_despesa" required>
                    </div>

                    <div class="form-field">
                        <label for="valor_despesa">Valor</label>
                        <input type="text" id="valor_despesa" name="valor_despesa" required onkeyup="formatarMoeda(this)">
                    </div>

                    <div class="btn-group">
                        <button class="btn" type="submit"><i class="fa fa-save"></i> Salvar</button>
                        <button class="btn btn-cancelar" type="button" onclick="closePopup()"><i class="fa fa-times"></i> Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mascaras de CPF, CNPJ e outros campos
        $('#cep').mask('00000-000');
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
        $('#celular').mask('(00) 00000-0000');

        $(document).ready(function() {
            function buscarCoordenadas(cep) {
                cep = cep.replace(/[^0-9]/g, '');
                
                if (cep.length === 8) {
                    $('#coordenada').val('Buscando coordenadas...');
                    
                    $.ajax({
                        url: `https://brasilapi.com.br/api/cep/v2/${cep}`,
                        method: 'GET',
                        success: function(response) {
                            if (response.location && response.location.coordinates) {
                                const latitude = response.location.coordinates.latitude;
                                const longitude = response.location.coordinates.longitude;
                                $('#coordenada').val(`${latitude}, ${longitude}`);
                            } else {
                                $('#coordenada').val('');
                            }
                        },
                        error: function() {
                            $('#coordenada').val('');
                        }
                    });
                }
            }

            // Trigger coordinate search when CEP changes
            $('#cep').on('blur', function() {
                buscarCoordenadas($(this).val());
            });

            // Also trigger when CEP field loses focus
            $('#cep').on('change', function() {
                buscarCoordenadas($(this).val());
            });
        });

        function openPopup() {
            document.getElementById('popup').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        function formatarMoeda(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = (valor/100).toFixed(2);
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            input.value = valor;
        }

        function closePopupOutside(event) {
            if (event.target.className === 'popup') {
                closePopup();
            }
        }

        // Cadastrar Despesa (requisição AJAX)
        function cadastrarDespesa(event) {
            event.preventDefault();
            const nome = document.getElementById('nome_despesa').value;
            const valor = document.getElementById('valor_despesa').value;
            const numeroProposta = document.getElementById('numero_proposta').value;

            if (!numeroProposta) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Por favor, informe o número da proposta primeiro.',
                    icon: 'warning'
                });
                return;
            }

            const formData = new FormData();
            formData.append('nome_despesa', nome);
            formData.append('valor_despesa', valor);
            formData.append('numero_proposta', numeroProposta);

            fetch('salvar_despesa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Adicionar despesa à tabela
                    const tbody = document.getElementById('tabelaDespesas').querySelector('tbody');
                    const novaLinha = document.createElement('tr');
                    novaLinha.id = `row-${data.id}`; // Alterado para manter consistência com a exclusão

                    const nomeTd = document.createElement('td');
                    nomeTd.textContent = data.nome_despesa;

                    const valorTd = document.createElement('td');
                    valorTd.textContent = `R$ ${parseFloat(data.valor_despesa).toFixed(2)}`;

                    const acoesTd = document.createElement('td');
                    const excluirBtn = document.createElement('button');
                    excluirBtn.className = 'btn btn-excluir';
                    excluirBtn.innerHTML = '<i class="fa fa-trash"></i>';
                    excluirBtn.onclick = (event) => excluirDespesa(data.id, event);
                    acoesTd.appendChild(excluirBtn);

                    novaLinha.appendChild(nomeTd);
                    novaLinha.appendChild(valorTd);
                    novaLinha.appendChild(acoesTd);

                    // Remove a mensagem "Nenhuma despesa cadastrada" se existir
                    if (tbody.querySelector('tr td[colspan="3"]')) {
                        tbody.innerHTML = '';
                    }
                    
                    tbody.appendChild(novaLinha);

                    // Fechar popup e mostrar mensagem de sucesso
                    closePopup();
                    document.getElementById('despesaForm').reset();
                    
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Despesa cadastrada com sucesso!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: data.message || 'Erro ao cadastrar despesa.',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro ao cadastrar despesa. Por favor, tente novamente.',
                    icon: 'error'
                });
            });
        }

        // Função para excluir despesa
        function excluirDespesa(id, event) {
            // Previne qualquer propagação do evento
            event.preventDefault();
            event.stopPropagation();
            
            Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja excluir este registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'excluir_despesa.php',
                        type: 'POST',
                        data: { id_despesa: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                // Remove a linha da tabela com animação
                                $('#row-' + id).fadeOut(400, function() {
                                    $(this).remove();
                                    
                                    // Verifica se a tabela ficou vazia
                                    const tbody = document.getElementById('despesasBody');
                                    if (!tbody.hasChildNodes()) {
                                        tbody.innerHTML = '<tr><td colspan="3">Nenhuma despesa cadastrada</td></tr>';
                                    }
                                });
                                
                                // Mostra mensagem de sucesso
                                Swal.fire({
                                    title: 'Sucesso!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro:', error);
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Erro ao excluir o registro. Tente novamente.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }

        // Adicionar evento de submit ao formulário
        document.getElementById('despesaForm').addEventListener('submit', cadastrarDespesa);

        $(document).ready(function() {
            // Consolidar a função de busca de CEP e preenchimento de endereço
            function buscarEPreencherEndereco(cep) {
                var validacep = /^[0-9]{8}$/;
                if (validacep.test(cep)) {
                    $("#rua").val("...");
                    $("#bairro").val("...");
                    $("#cidade").val("...");
                    $("#estado").val("...");

                    $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(dados) {
                        if (!("erro" in dados)) {
                            preencheCamposEndereco(dados);
                            buscarCoordenadas(dados);
                        } else {
                            limpaFormularioCep();
                            alert("CEP não encontrado.");
                        }
                    }).fail(function() {
                        limpaFormularioCep();
                        alert("Erro ao buscar CEP. Tente novamente mais tarde.");
                    });
                } else {
                    limpaFormularioCep();
                    alert("Formato de CEP inválido.");
                }
            }

            // Evento para o campo de CEP
            $("#cep").on('blur change', function() {
                var cep = $(this).val().replace(/\D/g, '');
                if (cep.length === 8) {
                    buscarEPreencherEndereco(cep);
                } else {
                    limpaFormularioCep();
                }
            });

            // Consolidar a lógica de atualização de status do serviço
            function atualizarStatusServico() {
                const dataInicio = $('#data_inicio').val();
                const dataTermino = $('#data_termino').val();
                const statusServico = $('#status_servico');
                const hoje = new Date().toISOString().split('T')[0];

                if (dataTermino && dataInicio) {
                    if (new Date(dataTermino) < new Date(dataInicio)) {
                        alert('Data de término não pode ser menor que a data de início');
                        $('#data_termino').val('');
                        statusServico.val('EM ANDAMENTO');
                        return;
                    }
                    if (new Date(dataTermino) > new Date()) {
                        alert('Data de término não pode ser maior que hoje');
                        $('#data_termino').val('');
                        statusServico.val('EM ANDAMENTO');
                        return;
                    }
                }

                if (!dataInicio) {
                    statusServico.val('');
                } else if (dataTermino) {
                    statusServico.val('CONCLUIDO');
                } else {
                    statusServico.val('EM ANDAMENTO');
                }
            }

            // Adiciona os event listeners
            $('#data_inicio, #data_termino').on('change', atualizarStatusServico);
            atualizarStatusServico(); // Inicializa o status ao carregar a página
        });

        function limpaFormularioCep() {
            // Limpa valores do formulário de cep.
            $("#rua").val("");
            $("#bairro").val("");
            $("#cidade").val("");
            $("#estado").val("");
            $("#coordenada").val("");
        }

        function preencheCamposEndereco(dados) {
            $("#rua").val(dados.street || dados.logradouro);
            $("#bairro").val(dados.neighborhood || dados.bairro);
            $("#cidade").val(dados.city || dados.cidade || dados.localidade);
            $("#estado").val(dados.state || dados.uf);
        }

        //RETORNA O CPF/CNPJ DO CLIENTE SELECIONADO
        function buscarCNPJCPF(clienteId) {
            if (clienteId) {
                const formData = new FormData();
                formData.append("buscar_cliente", true);
                formData.append("cliente_id", clienteId);

                fetch("cadastro_servicos.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("cnpj_cpf").value = data.cnpj ? data.cnpj : data.cpf;
                })
                .catch(error => console.error('Erro ao buscar CNPJ/CPF:', error));
            }
        }

        // Supondo que você tenha uma variável chamada numero_proposta
        let numero_proposta = document.getElementById('numero_proposta').value; // ou de onde você estiver pegando

        console.log("Número da Proposta:", numero_proposta);

        function verificarValores() {
            const valorTotal = parseFloat(document.getElementById('valor_total').value) || 0;
            const valorEntrada = parseFloat(document.getElementById('valor_entrada').value) || 0;
            const campoParcelamento = document.getElementById('parcelamento');

            if (valorEntrada === valorTotal && valorTotal !== 0) {
                campoParcelamento.value = '';
                campoParcelamento.disabled = true;
            } else {
                campoParcelamento.disabled = false;
            }
        }

        function verificarNumeroProposta(numero) {
            $.ajax({
                url: 'verificar_proposta.php',
                method: 'POST',
                data: { numero_proposta: numero },
                success: function(response) {
                    const feedback = $('#numero_proposta_feedback');
                    feedback.show();
                    
                    if (response.existe) {
                        feedback.text('Número da proposta já existe!');
                        feedback.css('color', 'red');
                        // Buscar despesas existentes para esta proposta
                        buscarDespesas(numero);
                    } else {
                        feedback.text('Número da proposta disponível');
                        feedback.css('color', 'green');
                        // Limpar tabela de despesas
                        const tbody = document.getElementById('despesasBody');
                        tbody.innerHTML = '<tr><td colspan="3">Nenhuma despesa cadastrada</td></tr>';
                    }
                }
            });
        }

        function buscarDespesas(numeroProposta) {
            if (!numeroProposta) {
                console.log('Número da proposta não fornecido');
                const tbody = document.getElementById('despesasBody');
                tbody.innerHTML = '<tr><td colspan="3">Nenhuma despesa cadastrada</td></tr>';
                return;
            }

            console.log('Buscando despesas para proposta:', numeroProposta);

            fetch(`buscar_despesas_fixas.php?numero_proposta=${encodeURIComponent(numeroProposta)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta do servidor:', data);
                    const tbody = document.getElementById('despesasBody');
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (Array.isArray(data) && data.length > 0) {
                        let html = '';
                        data.forEach(despesa => {
                            html += `
                                <tr id="row-${despesa.id}">
                                    <td>${despesa.nome_despesa}</td>
                                    <td>R$ ${despesa.valor}</td>
                                    <td>
                                        <button type="button" class="btn btn-excluir" onclick="excluirDespesa(${despesa.id}, event)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="3">Nenhuma despesa cadastrada</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar despesas:', error);
                    const tbody = document.getElementById('despesasBody');
                    tbody.innerHTML = '<tr><td colspan="3">Nenhuma despesa cadastrada</td></tr>';
                    
                    Swal.fire({
                        title: 'Erro!',
                        text: error.message || 'Erro ao carregar despesas',
                        icon: 'error'
                    });
                });
        }

        // Evento para carregar despesas quando o número da proposta mudar
        document.addEventListener('DOMContentLoaded', function() {
            const numeroPropostaInput = document.getElementById('numero_proposta');
            
            if (numeroPropostaInput) {
                // Carregar despesas quando o valor mudar
                numeroPropostaInput.addEventListener('change', function() {
                    const numeroProposta = this.value.trim();
                    if (numeroProposta) {
                        buscarDespesas(numeroProposta);
                    }
                });

                // Carregar despesas iniciais se já houver um número
                const numeroPropostaInicial = numeroPropostaInput.value.trim();
                if (numeroPropostaInicial) {
                    buscarDespesas(numeroPropostaInicial);
                }
            }
        });
    </script>
</body>
</html>