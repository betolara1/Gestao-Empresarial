<?php
include 'conexao.php';

// Verifica se o ID do serviço foi passado pela URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para buscar os dados do serviço pelo ID
    $sql = "SELECT * FROM servicos WHERE numero_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o serviço foi encontrado
    if ($result->num_rows > 0) {
        $servico = $result->fetch_assoc(); // Obtém os dados do serviço
    } else {
        die("Serviço não encontrado.");
    }
} else {
    die("ID do serviço não informado.");
}


// Consulta SQL para buscar todos os tipos de despesas
$sqlTipoDespesa = "SELECT * FROM despesas";
$resultTipoDespesa = $conn->query($sqlTipoDespesa);

// ID do serviço sendo editado
$servico_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($servico_id > 0) {
    // Consulta SQL para buscar todos os tipos de serviço associados ou não ao servico_id
    $queryTiposServicos = "
        SELECT 
            ts.*,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM servico_tipo_servico sts 
                    WHERE sts.tipo_servico_id = ts.id 
                    AND sts.servico_id = (
                        SELECT id 
                        FROM servicos 
                        WHERE numero_proposta = ?
                    )
                ) THEN 1
                ELSE 0
            END as is_selected
        FROM tipos_servicos ts
        ORDER BY ts.tipo_servico";

    $stmtTipos = $conn->prepare($queryTiposServicos);
    $stmtTipos->bind_param("i", $id); // $id é o número da proposta
    $stmtTipos->execute();
    $resultTipos = $stmtTipos->get_result();

    // Adicione este código para debug
    if ($stmtTipos->error) {
        echo "Erro na consulta: " . $stmtTipos->error;
    }
} else {
    echo "ID do serviço não informado.";
    exit;
}

// ID do serviço sendo editado
$servico_id = $id;

// Lógica de cálculo
$valor_total = isset($servico['valor_total']) ? (float) $servico['valor_total'] : 0;
$valor_entrada = isset($servico['valor_entrada']) ? (float) $servico['valor_entrada'] : 0;
$parcelamento = isset($servico['parcelamento']) ? (int) $servico['parcelamento'] : 1;
$data_pagamento_inicial = isset($servico['dia_pagamento']) ? $servico['dia_pagamento'] : date('Y-m-d');

// Busca todos os pagamentos já realizados para esta proposta
$sql_pagamentos = "SELECT SUM(valor_parcela) as total_pago 
                   FROM pagamentos 
                   WHERE numero_proposta = ? AND status_pagamento = 'Pago'";
$stmt_pagamentos = $conn->prepare($sql_pagamentos);
$stmt_pagamentos->bind_param("i", $servico['numero_proposta']);
$stmt_pagamentos->execute();
$result_pagamentos = $stmt_pagamentos->get_result();
$pagamentos_info = $result_pagamentos->fetch_assoc();
$total_ja_pago = $pagamentos_info['total_pago'] ?? 0;

// Calcula o valor restante a ser pago
$valor_pagar = $valor_total - $valor_entrada - $total_ja_pago;
$valor_parcela = $parcelamento > 0 ? $valor_pagar / $parcelamento : 0;

$parcelas = [];

// Consulta para buscar todas as parcelas relacionadas ao número da proposta
$sql_parcelas = "SELECT parcela_num, status_pagamento, valor_parcela, dia_pagamento 
                 FROM pagamentos 
                 WHERE numero_proposta = ? 
                 ORDER BY parcela_num ASC";
$stmt_parcelas = $conn->prepare($sql_parcelas);
$stmt_parcelas->bind_param("i", $servico['numero_proposta']);
$stmt_parcelas->execute();
$result_parcelas = $stmt_parcelas->get_result();

if ($result_parcelas->num_rows > 0) {
    // Adiciona as parcelas existentes ao array de parcelas
    while ($parcela = $result_parcelas->fetch_assoc()) {
        $parcelas[] = [
            'id' => $parcela['parcela_num'],
            'status_pagamento' => $parcela['status_pagamento'],
            'valor_parcela' => number_format($parcela['valor_parcela'], 2, '.', ''),
            'dia_pagamento' => date('Y-m-d', strtotime($parcela['dia_pagamento']))
        ];
    }
} else {
    // Se não existirem parcelas no banco, gera as parcelas iniciais
    $data_pagamento_inicial = isset($servico['dia_pagamento']) ? $servico['dia_pagamento'] : date('Y-m-d'); // Usa a dia_pagamento de `servicos`

    for ($i = 0; $i < $parcelamento; $i++) {
        // Incrementa os meses com base na data inicial
        $data_pagamento_parcela = date('Y-m-d', strtotime("+$i month", strtotime($data_pagamento_inicial)));

        $parcelas[] = [
            'id' => $i + 1,
            'status_pagamento' => 'Aberto',
            'valor_parcela' => number_format($valor_parcela, 2, '.', ''),
            'dia_pagamento' => $data_pagamento_parcela
        ];

        // Insere a parcela no banco de dados se ainda não existir
        $sql_inserir_parcela = "INSERT IGNORE INTO pagamentos 
                                (numero_proposta, parcela_num, status_pagamento, valor_parcela, dia_pagamento, dia_pagamento) 
                                VALUES (?, ?, 'Aberto', ?, ?, ?)";
        $stmt_inserir = $conn->prepare($sql_inserir_parcela);
        $stmt_inserir->bind_param(
            "iidds", 
            $servico['numero_proposta'], 
            $parcelas[$i]['id'], 
            $valor_parcela, 
            $parcelas[$i]['dia_pagamento'], 
            $parcelas[$i]['dia_pagamento']
        );
        $stmt_inserir->execute();
    }
}

// Calcula o total já pago (entrada + parcelas pagas)
$total_ja_pago = isset($servico['valor_entrada']) ? (float)$servico['valor_entrada'] : 0;
$total_ja_pago += (float)($pagamentos_info['total_pago'] ?? 0);

// Calcula o valor que falta pagar
$valor_pagar = (float)$servico['valor_total'] - $total_ja_pago;


// Atualiza os totais de valor pago e a pagar no banco de dados
$sql_totais = "SELECT 
                    SUM(CASE WHEN status_pagamento = 'Pago' THEN valor_parcela ELSE 0 END) AS total_pago,
                    SUM(CASE WHEN status_pagamento = 'Aberto' THEN valor_parcela ELSE 0 END) AS total_pendente
               FROM pagamentos 
               WHERE numero_proposta = ?";
$stmt_totais = $conn->prepare($sql_totais);
$stmt_totais->bind_param("i", $servico['numero_proposta']);
$stmt_totais->execute();
$result_totais = $stmt_totais->get_result();
$totais = $result_totais->fetch_assoc();

$total_pago = $totais['total_pago'];
$total_pendente = $totais['total_pendente'];


// Modifique a consulta SQL para incluir um JOIN com a tabela cliente
$sql = "SELECT s.*, c.nome as nome_cliente, c.cpf, c.cnpj 
        FROM servicos s 
        LEFT JOIN cliente c ON s.cliente_id = c.id 
        WHERE s.numero_proposta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h1>Editar Serviço</h1>

            <div class="card">
                <form action="atualizar_servico.php" method="POST">
                    <div class="form-section">
                        <h2>Informações do Serviço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="numero_proposta">Número da Proposta</label>
                                <input type="text" id="numero_proposta" name="numero_proposta" value="<?php echo htmlspecialchars($servico['numero_proposta']); ?>" class="form-control disabled-field" readonly>
                            </div>
                            <div class="form-group">
                                <label for="cliente">Cliente</label>
                                <input type="text" id="cliente" name="cliente" value="<?php echo htmlspecialchars($servico['cliente_id']); ?>" readonly class="form-control disabled-field">
                            </div>
                            <div class="form-group">
                                <label for="cnpj_cpf">CNPJ/CPF</label>
                                <input type="text" id="cnpj_cpf" name="cnpj_cpf" value="<?php echo htmlspecialchars($servico['cnpj_cpf']); ?>" readonly class="form-control disabled-field">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Tipos de Serviço</h2>
                        <div class="checkbox-group">
                            <?php
                            while($row = $resultTipos->fetch_assoc()) {
                                $checked = $row['is_selected'] ? 'checked' : '';
                                echo "<label class='form-check' for='servico_" . $row['id'] . "'>";
                                echo "<input class='form-check-input' type='checkbox' 
                                    name='tipo_servico[]' 
                                    id='servico_" . $row['id'] . "' 
                                    value='" . $row['id'] . "' 
                                    " . $checked . ">";
                                echo "<span class='form-check-label'>";
                                echo htmlspecialchars($row['tipo_servico']);
                                echo "</span>";
                                echo "</label>";
                            }
                            ?>
                        </div>
                    </div>
                
                    <div class="form-section">
                        <h2>Status do Serviço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_inicio">Data de Início</label>
                                <input type="date" id="data_inicio" name="data_inicio" 
                                       value="<?php echo htmlspecialchars($servico['data_inicio']); ?>" 
                                       required class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="data_termino">Data de Término</label>
                                <input type="date" id="data_termino" name="data_termino" 
                                       value="<?php echo htmlspecialchars($servico['data_termino']); ?>" 
                                       class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="status_servico">Status do Serviço</label>
                                <input type="text" id="status_servico" name="status_servico" 
                                       value="<?php echo htmlspecialchars($servico['status_servico']); ?>" 
                                       readonly class="form-control disabled-field">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Informações do Pagamento</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_pagamento">Vencimento</label>
                                <input type="date" id="data_pagamento" name="data_pagamento" value="<?php echo htmlspecialchars($servico['data_pagamento']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="responsavel_execucao">Responsável pelo Serviço</label>
                                <input type="text" id="responsavel_execucao" name="responsavel_execucao" value="<?php echo htmlspecialchars($servico['responsavel_execucao']); ?>" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="forma_pagamento">Forma de Pagamento</label>
                                <select id="forma_pagamento" name="forma_pagamento" required class="form-control">
                                    <option value="">Selecione a forma de pagamento</option>
                                    <option value="CARTÃO DE CRÉDITO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'CARTÃO DE CRÉDITO' ? 'selected' : ''; ?>>Cartão de Crédito</option>
                                    <option value="CARTÃO DE DÉBITO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'CARTÃO DE DÉBITO' ? 'selected' : ''; ?>>Cartão de Débito</option>
                                    <option value="PIX" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'PIX' ? 'selected' : ''; ?>>PIX</option>
                                    <option value="DINHEIRO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'DINHEIRO' ? 'selected' : ''; ?>>Dinheiro</option>
                                    <option value="BOLETO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'BOLETO' ? 'selected' : ''; ?>>Boleto</option>
                                </select>
                            </div>

                            <div id="editarServicoForm" class="form-group">
                                <label for="parcelamento">Quatidade de Parcelas</label>
                                <input type="number" id="parcelamento" name="parcelamento" step="0.01" value="<?php echo htmlspecialchars($servico['parcelamento']); ?>" readonly class="form-control disabled-field">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="valor_total">Valor Total</label>
                                <input type="number" id="valor_total" name="valor_total" step="0.01" value="<?php echo htmlspecialchars($servico['valor_total']); ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="valor_entrada">Valor Entrada</label>
                                <input type="number" id="valor_entrada" name="valor_entrada" step="0.01" 
                                    value="<?php echo isset($servico['valor_entrada']) && $servico['valor_entrada'] !== '' ? htmlspecialchars($servico['valor_entrada']) : '0'; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="valor_pago">Valor Pago</label>
                                <input type="number" id="valor_pago" name="valor_pago" step="0.01" 
                                    value="<?php echo number_format($total_pago, 2, '.', ''); ?>" readonly class="form-control disabled-field">
                            </div>

                            <div class="form-group">
                                <label for="valor_pagar">Valor A Ser Pago</label>
                                <input type="number" id="valor_pagar" name="valor_pagar" step="0.01" 
                                    value="<?php echo number_format($total_pendente, 2, '.', ''); ?>" readonly class="form-control disabled-field">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Endereço do Serviço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cep" class="required">CEP:</label>
                                <input type="text" id="cep" name="cep" required placeholder="00000-000" 
                                    value="<?php echo htmlspecialchars($servico['cep']); ?>" class="form-control">
                                <small id="cep-feedback" class="form-text"></small>
                            </div>

                            <div class="form-group">
                                <label for="rua">Rua:</label>
                                <input type="text" id="rua" name="rua" placeholder="Endereço" 
                                    value="<?php echo htmlspecialchars($servico['rua']); ?>" readonly class="form-control disabled-field">
                            </div>

                            <div class="form-group">
                                <label for="numero" class="required">Número:</label>
                                <input type="text" id="numero" name="numero" required placeholder="Número" 
                                    value="<?php echo htmlspecialchars($servico['numero']); ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="complemento">Complemento:</label>
                                <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc." 
                                    value="<?php echo htmlspecialchars($servico['complemento']); ?>" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="bairro">Bairro:</label>
                                <input type="text" id="bairro" name="bairro" placeholder="Bairro" 
                                    value="<?php echo htmlspecialchars($servico['bairro']); ?>" readonly class="form-control disabled-field">
                            </div>
                            <div class="form-group">
                                <label for="cidade">Cidade:</label>
                                <input type="text" id="cidade" name="cidade" placeholder="Cidade" 
                                    value="<?php echo htmlspecialchars($servico['cidade']); ?>" readonly class="form-control disabled-field">
                            </div>
                            <div class="form-group">
                                <label for="estado">Estado:</label>
                                <input type="text" id="estado" name="estado" placeholder="Estado" 
                                    value="<?php echo htmlspecialchars($servico['estado']); ?>" readonly class="form-control disabled-field">
                            </div>
                            <div class="form-group">
                                <label for="coordenada">Coordenada:</label>
                                <div class="input-with-map">
                                    <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude" 
                                        value="<?php echo htmlspecialchars($servico['coordenada']); ?>" class="form-control">
                                    <small id="coordenadas-feedback" class="form-text"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="gerenciar_relatorio.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
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
                // Remove any non-numeric characters from CEP
                cep = cep.replace(/[^0-9]/g, '');
                
                if (cep.length === 8) {
                    // Show loading indicator in the coordinates field
                    $('#coordenada').val('Buscando coordenadas...');
                    
                    $.ajax({
                        url: `https://brasilapi.com.br/api/cep/v2/${cep}`,
                        method: 'GET',
                        success: function(response) {
                            if (response.location && response.location.coordinates) {
                                const latitude = response.location.coordinates[1];
                                const longitude = response.location.coordinates[0];
                                $('#coordenada').val(`${latitude}, ${longitude}`);
                            } else {
                                $('#coordenada').val('');
                            }
                        },
                        error: function() {
                            $('#coordenada').val('');
                            console.log('Erro ao buscar coordenadas');
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


        $(document).ready(function() {
            let isRequesting = false;
            $('#cep').on('blur', function() {
                if (isRequesting) return;

                let cep = $(this).val().replace(/\D/g, '');
                if (cep !== '') {
                    let validacep = /^[0-9]{8}$/;
                    if (validacep.test(cep)) {
                        isRequesting = true;
                        $('#cep-feedback').text('Buscando CEP...').removeClass('text-danger').addClass('text-info');

                        $.getJSON(`https://viacep.com.br/ws/${cep}/json/`)
                            .done(function(dados) {
                                if (!('erro' in dados)) {
                                    $('#rua').val(dados.logradouro);
                                    $('#bairro').val(dados.bairro);
                                    $('#cidade').val(dados.localidade);
                                    $('#estado').val(dados.uf);
                                    $('#cep-feedback').text('CEP encontrado!').removeClass('text-info text-danger').addClass('text-success');

                                    // Buscar coordenadas
                                    buscarCoordenadas(dados.logradouro + ', ' + dados.localidade + ' - ' + dados.uf);
                                } else {
                                    limpaCamposEndereco();
                                    $('#cep-feedback').text('CEP não encontrado.').removeClass('text-info text-success').addClass('text-danger');
                                }
                            })
                            .fail(function() {
                                limpaCamposEndereco();
                                $('#cep-feedback').text('Erro na busca do CEP.').removeClass('text-info text-success').addClass('text-danger');
                            })
                            .always(function() {
                                isRequesting = false;
                            });
                    } else {
                        limpaCamposEndereco();
                        $('#cep-feedback').text('Formato de CEP inválido.').removeClass('text-info text-success').addClass('text-danger');
                    }
                } else {
                    limpaCamposEndereco();
                    $('#cep-feedback').text('');
                }
            });

            function limpaCamposEndereco() {
                $('#cep').val('');
                $('#rua').val('');
                $('#bairro').val('');
                $('#cidade').val('');
                $('#estado').val('');
                $('#coordenada').val('');
            }
        });
    
        // Atualizar o status do serviço quando as datas são alteradas
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('data_termino').setAttribute('max', hoje);
        $(document).ready(function() {
            // Inicializar o status baseado nos valores existentes
            atualizarStatusServico();
            
            // Atualizar quando as datas mudarem
            $('#data_inicio, #data_termino').on('change', atualizarStatusServico);
        });

        function atualizarStatusServico() {
            const dataInicio = $('#data_inicio').val();
            const dataTermino = $('#data_termino').val();
            const statusServico = $('#status_servico');
            const hoje = new Date().toISOString().split('T')[0];
            
            // Validações
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
            
            // Definir status
            if (!dataInicio) {
                statusServico.val('');
            } else if (dataTermino) {
                statusServico.val('CONCLUIDO');
            } else {
                statusServico.val('EM ANDAMENTO');
            }
        }



    </script>
    <script src="js/coordenadas.js"></script>
    <script src="js/busca_cpfcnpj.js"></script>
    <script src="js/cep.js"></script>
    <script src="js/despesas.js"></script>
    <script src="js/status_servico.js"></script>
</body>
</html>
