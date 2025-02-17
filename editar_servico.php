<?php
include 'conexao.php';

// Verifica se o ID do serviço foi passado pela URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para buscar os dados do serviço pelo ID
    $sql = "SELECT s.*, c.nome as nome_cliente, c.cpf, c.cnpj, c.tipo_pessoa, c.razao_social 
            FROM servicos s 
            LEFT JOIN cliente c ON s.cliente_id = c.id 
            WHERE s.numero_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o serviço foi encontrado
    if ($result->num_rows > 0) {
        $servico = $result->fetch_assoc(); // Obtém os dados do serviço
        $nome_cliente = $servico['nome_cliente']; // Adiciona esta linha
        $tipo_pessoa = $servico['tipo_pessoa']; // Adiciona esta linha
        $razao_social = $servico['razao_social']; // Adiciona esta linha
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
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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

        .form-group input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
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

        /* Estilos para o mapa */
        .leaflet-container {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: var(--shadow-md);
        }

        .leaflet-popup-content {
            margin: 13px 19px;
            line-height: 1.4;
        }

        .leaflet-control-zoom {
            border: none !important;
            box-shadow: var(--shadow-md) !important;
        }

        .leaflet-control-zoom a {
            background-color: white !important;
            color: var(--primary-color) !important;
        }

        .leaflet-control-zoom a:hover {
            background-color: #f8f9fa !important;
        }

        .form-section h2 i {
            margin-right: 10px;
            color: #007bff;
        }

        .form-group label i {
            margin-right: 5px;
            color: #666;
            width: 16px;
        }

        .form-text i {
            margin-right: 5px;
            color: #666;
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
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h1>Editar Serviço</h1>

            <div class="card">
                <form action="atualizar_servico.php" method="POST">
                    <div class="form-section">
                        <h2><i class="fas fa-info-circle"></i> Informações do Serviço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="numero_proposta">
                                    <i class="fas fa-hashtag"></i> Número da Proposta
                                </label>
                                <input type="text" id="numero_proposta" name="numero_proposta" value="<?php echo htmlspecialchars($servico['numero_proposta']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="cliente">
                                    <i class="fas fa-user"></i> Cliente
                                </label>
                                <input type="text" id="cliente" name="cliente" value="<?php echo htmlspecialchars($tipo_pessoa === 'J' ? $razao_social : $nome_cliente); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="cnpj_cpf">
                                    <i class="fas fa-id-card"></i> CNPJ/CPF
                                </label>
                                <input type="text" id="cnpj_cpf" name="cnpj_cpf" value="<?php echo htmlspecialchars($servico['cnpj_cpf']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2><i class="fas fa-tools"></i> Tipos de Serviço</h2>
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
                        <h2><i class="fas fa-tasks"></i> Status do Serviço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_inicio" class="required">
                                    <i class="fas fa-calendar-plus"></i> Data de Início
                                </label>
                                <input type="date" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($servico['data_inicio']); ?>" required class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="data_termino">
                                    <i class="fas fa-calendar-check"></i> Data de Término
                                </label>
                                <input type="date" id="data_termino" name="data_termino" value="<?php echo htmlspecialchars($servico['data_termino']); ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="status_servico">
                                    <i class="fas fa-chart-line"></i> Status do Serviço
                                </label>
                                <input type="text" id="status_servico" name="status_servico" value="<?php echo htmlspecialchars($servico['status_servico']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2><i class="fas fa-money-bill-wave"></i> Informações do Pagamento</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_pagamento" class="required">
                                    <i class="fas fa-calendar-day"></i> Vencimento
                                </label>
                                <input type="date" id="data_pagamento" name="data_pagamento" value="<?php echo htmlspecialchars($servico['data_pagamento']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="forma_pagamento" class="required">
                                    <i class="fas fa-credit-card"></i> Forma de Pagamento
                                </label>
                                <select id="forma_pagamento" name="forma_pagamento" required class="form-control">
                                    <option value="">Selecione a forma de pagamento</option>
                                    <option value="CARTÃO DE CRÉDITO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'CARTÃO DE CRÉDITO' ? 'selected' : ''; ?>>Cartão de Crédito</option>
                                    <option value="CARTÃO DE DÉBITO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'CARTÃO DE DÉBITO' ? 'selected' : ''; ?>>Cartão de Débito</option>
                                    <option value="PIX" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'PIX' ? 'selected' : ''; ?>>PIX</option>
                                    <option value="DINHEIRO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'DINHEIRO' ? 'selected' : ''; ?>>Dinheiro</option>
                                    <option value="BOLETO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'BOLETO' ? 'selected' : ''; ?>>Boleto</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="parcelamento">
                                    <i class="fas fa-clock"></i> Quantidade de Parcelas
                                </label>
                                <input type="number" id="parcelamento" name="parcelamento" step="0.01" value="<?php echo htmlspecialchars($servico['parcelamento']); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="valor_total" class="required">
                                    <i class="fas fa-dollar-sign"></i> Valor Total
                                </label>
                                <input type="number" id="valor_total" name="valor_total" step="0.01" value="<?php echo htmlspecialchars($servico['valor_total']); ?>" required class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="valor_entrada">
                                    <i class="fas fa-hand-holding-usd"></i> Valor Entrada
                                </label>
                                <input type="number" id="valor_entrada" name="valor_entrada" step="0.01" value="<?php echo isset($servico['valor_entrada']) && $servico['valor_entrada'] !== '' ? htmlspecialchars($servico['valor_entrada']) : '0'; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="valor_pago">
                                    <i class="fas fa-check-circle"></i> Valor Pago
                                </label>
                                <input type="number" id="valor_pago" name="valor_pago" step="0.01" value="<?php echo number_format($total_pago, 2, '.', ''); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="valor_pagar">
                                    <i class="fas fa-hourglass-half"></i> Valor A Ser Pago
                                </label>
                                <input type="number" id="valor_pagar" name="valor_pagar" step="0.01" value="<?php echo number_format($total_pendente, 2, '.', ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2><i class="fas fa-map-marked-alt"></i> Endereço do Serviço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cep" class="required">
                                    <i class="fas fa-map-pin"></i> CEP:
                                </label>
                                <input type="text" id="cep" name="cep" required placeholder="00000-000" value="<?php echo htmlspecialchars($servico['cep']); ?>" class="form-control">
                                <small id="cep-feedback" class="form-text"></small>
                            </div>

                            <div class="form-group">
                                <label for="rua">
                                    <i class="fas fa-road"></i> Rua:
                                </label>
                                <input type="text" id="rua" name="rua" placeholder="Endereço" value="<?php echo htmlspecialchars($servico['rua']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="numero" class="required">
                                    <i class="fas fa-home"></i> Número:
                                </label>
                                <input type="text" id="numero" name="numero" required placeholder="Número" value="<?php echo htmlspecialchars($servico['numero']); ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="complemento">
                                    <i class="fas fa-building"></i> Complemento:
                                </label>
                                <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc." value="<?php echo htmlspecialchars($servico['complemento']); ?>" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="bairro">
                                    <i class="fas fa-map-marker-alt"></i> Bairro:
                                </label>
                                <input type="text" id="bairro" name="bairro" placeholder="Bairro" value="<?php echo htmlspecialchars($servico['bairro']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="cidade">
                                    <i class="fas fa-city"></i> Cidade:
                                </label>
                                <input type="text" id="cidade" name="cidade" placeholder="Cidade" value="<?php echo htmlspecialchars($servico['cidade']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="estado">
                                    <i class="fas fa-map-marker-alt"></i> Estado:
                                </label>
                                <input type="text" id="estado" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($servico['estado']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="coordenada">
                                    <i class="fas fa-map-marker-alt"></i> Coordenada:
                                </label>
                                <div class="input-with-feedback">
                                    <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude" value="<?php echo htmlspecialchars($servico['coordenada']); ?>" class="form-control">
                                    <small id="coordenadas-feedback" class="form-text"></small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="responsavel_execucao">
                                    <i class="fas fa-user-tie"></i> Responsável pelo Serviço
                                </label>
                                <input type="text" id="responsavel_execucao" name="responsavel_execucao" value="<?php echo htmlspecialchars($servico['responsavel_execucao']); ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Mapa -->
                    <div class="form-section">
                        <h2><i class="fas fa-map"></i> Localização no Mapa</h2>
                        <div id="map" style="height: 400px; width: 100%; border-radius: 8px; margin-bottom: 15px;"></div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Clique no mapa para atualizar as coordenadas ou arraste o marcador
                        </small>
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
        $('#cep').mask('00000-000');
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
        $('#telefone').mask('(00) 0000-0000');
        $('#celular').mask('(00) 00000-0000');
        
        $(document).ready(function() {
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

            function buscarCoordenadas(endereco) {
                $('#coordenada').val("Buscando coordenadas...");

                const enderecoCompleto = `${endereco.logradouro}, ${endereco.localidade}, ${endereco.uf}, Brasil`;

                // Atualizar o mapa com o novo endereço
                updateMapFromAddress(endereco);

                // Adicione uma chamada para a API de geocodificação aqui
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(enderecoCompleto)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            $('#coordenada').val(`${lat}, ${lng}`);
                        } else {
                            alert("Coordenadas não encontradas.");
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar coordenadas:', error);
                        alert("Erro ao buscar coordenadas. Tente novamente mais tarde.");
                    });
            }

            //Quando o campo cep perde o foco.
            $("#cep").on('blur change', function() {
                // Nova variável "cep" somente com dígitos.
                var cep = $(this).val().replace(/\D/g, '');

                //Verifica se campo cep possui valor informado.
                if (cep.length === 8) {
                    //Expressão regular para validar o CEP.
                    var validacep = /^[0-9]{8}$/;

                    //Valida o formato do CEP.
                    if(validacep.test(cep)) {
                        //Preenche os campos com "..." enquanto consulta webservice.
                        $("#rua").val("...");
                        $("#bairro").val("...");
                        $("#cidade").val("...");
                        $("#estado").val("...");
                        $("#coordenada").val("Buscando coordenadas...");

                        //Consulta o webservice viacep.com.br/
                        $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(dados) {
                            if (!("erro" in dados)) {
                                preencheCamposEndereco(dados);
                                // Busca coordenadas após preencher o endereço
                                buscarCoordenadas(dados);
                            } else {
                                //CEP pesquisado não foi encontrado.
                                limpaFormularioCep();
                                alert("CEP não encontrado.");
                            }
                        }).fail(function() {
                            limpaFormularioCep();
                            alert("Erro ao buscar CEP. Tente novamente mais tarde.");
                        });
                    } else {
                        //cep é inválido.
                        limpaFormularioCep();
                        alert("Formato de CEP inválido.");
                    }
                } else {
                    //cep sem valor, limpa formulário.
                    limpaFormularioCep();
                }
            });
        });
        
        // Adicionar código do mapa
        let map;
        let marker;

        function initMap() {
            // Coordenadas iniciais (usar as do cliente se existirem, ou centro do Brasil como padrão)
            let initialLat = -15.77972;
            let initialLng = -47.92972;
            
            // Pegar coordenadas salvas do cliente
            const coordField = document.getElementById('coordenada');
            const savedCoords = coordField.value;
            
            if (savedCoords) {
                const [lat, lng] = savedCoords.split(',').map(coord => parseFloat(coord.trim()));
                if (!isNaN(lat) && !isNaN(lng)) {
                    initialLat = lat;
                    initialLng = lng;
                }
            }

            // Inicializar o mapa
            map = L.map('map').setView([initialLat, initialLng], 15);

            // Adicionar camada do OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Adicionar marcador
            marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            // Atualizar coordenadas quando o marcador for arrastado
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // Atualizar coordenadas ao clicar no mapa
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });
        }

        function updateCoordinates(lat, lng) {
            const coordField = document.getElementById('coordenada');
            coordField.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }

        // Atualizar mapa quando o endereço mudar
        function updateMapFromAddress(endereco) {
            const enderecoCompleto = `${endereco.logradouro}, ${endereco.localidade}, ${endereco.uf}, Brasil`;
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(enderecoCompleto)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        
                        if (map && marker) {
                            marker.setLatLng([lat, lng]);
                            map.setView([lat, lng], 16);
                            updateCoordinates(lat, lng);
                        }
                    }
                })
                .catch(error => console.error('Erro ao buscar coordenadas:', error));
        }

        // Inicializar o mapa quando a página carregar
        initMap();

        // Atualizar o mapa quando a janela for redimensionada
        window.addEventListener('resize', function() {
            if (map) {
                map.invalidateSize();
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
</body>
</html>