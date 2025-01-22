<?php
include 'conexao.php';

$sql = "SELECT 
    servicos.numero_proposta,
    servicos.cnpj_cpf,
    CASE 
        WHEN cliente.tipo_pessoa = 'F' THEN cliente.nome
        WHEN cliente.tipo_pessoa = 'J' THEN cliente.razao_social
        ELSE 'Não especificado'
    END AS cliente_nome_ou_razao,
    GROUP_CONCAT(tipos_servicos.tipo_servico SEPARATOR ', ') AS tipos_servico,
    servicos.data_inicio,
    servicos.data_termino,
    servicos.valor_total,
    servicos.valor_entrada,
    servicos.forma_pagamento,
    servicos.parcelamento,
    servicos.status_servico,
    servicos.responsavel_execucao,
    servicos.data_cadastro,
    (SELECT COALESCE(SUM(valor), 0) FROM despesas WHERE proposta = servicos.numero_proposta) AS total_despesas,
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM pagamentos 
            WHERE numero_proposta = servicos.numero_proposta 
            AND status_pagamento = 'Aberto'
        ) THEN 'EM ABERTO'
        ELSE 'FINALIZADO'
    END AS status_pagamento,
    (SELECT COALESCE(SUM(valor_parcela), 0) 
     FROM pagamentos 
     WHERE numero_proposta = servicos.numero_proposta 
       AND status_pagamento = 'Aberto') AS valor_a_pagar,
    (SELECT COALESCE(SUM(valor_parcela), 0) 
     FROM pagamentos 
     WHERE numero_proposta = servicos.numero_proposta 
       AND status_pagamento = 'Pago') AS total_pago,
    (servicos.valor_total - servicos.valor_entrada - (SELECT COALESCE(SUM(valor_parcela), 0) 
     FROM pagamentos 
     WHERE numero_proposta = servicos.numero_proposta 
       AND status_pagamento = 'Pago')) AS total_pendente,
    (SELECT MIN(p.dia_pagamento)
     FROM pagamentos p
     WHERE p.numero_proposta = servicos.numero_proposta
     AND p.status_pagamento = 'Aberto'
     AND p.dia_pagamento > (
         SELECT COALESCE(MAX(dia_pagamento), '1900-01-01')
         FROM pagamentos
         WHERE numero_proposta = servicos.numero_proposta
         AND status_pagamento = 'Pago'
     )
    ) AS proximo_pagamento
FROM servicos
INNER JOIN cliente ON servicos.cliente_id = cliente.id
LEFT JOIN servico_tipo_servico ON servicos.id = servico_tipo_servico.servico_id
LEFT JOIN tipos_servicos ON servico_tipo_servico.tipo_servico_id = tipos_servicos.id
GROUP BY servicos.numero_proposta, servicos.cnpj_cpf, cliente_nome_ou_razao, servicos.data_inicio, 
         servicos.data_termino, servicos.valor_total, servicos.valor_entrada, servicos.forma_pagamento, 
         servicos.parcelamento, servicos.status_servico, servicos.responsavel_execucao, servicos.data_cadastro
ORDER BY servicos.numero_proposta ASC";

$result = $conn->query($sql);
// Fetch all rows
$servicos = $result->fetch_all(MYSQLI_ASSOC);

foreach ($servicos as $servico) {
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
            $sql_inserir_parcela = "INSERT IGNORE INTO pagamentos (numero_proposta, parcela_num, valor_parcela, dia_pagamento, status_pagamento) 
                                    VALUES (?, ?, ?, ?, 'Aberto')";
            $stmt_inserir = $conn->prepare($sql_inserir_parcela);
            $stmt_inserir->bind_param(
                "iids", 
                $servico['numero_proposta'], 
                $parcelas[$i]['id'], 
                $valor_parcela, 
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
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Serviços</title>
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
            --sidebar-width: 250px;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --primary-dark: #1e40af;
            --background-color: #ffffff;
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

        .container {
            max-width: 1200px;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 2rem auto;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            cursor: pointer;
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

        .btn-editar, .btn-excluir {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 0.9rem;
        }

        .btn-editar {
            background-color: #3498db;
        }

        .btn-editar:hover {
            background-color: #2980b9;
        }

        .btn-excluir {
            background-color: #e74c3c;
        }

        .btn-excluir:hover {
            background-color: #c0392b;
        }

        .no-data {
            text-align: center;
            color: #999;
        }

        .no-results {
            text-align: center;
            color: #999;
        }

        /* Estilos do Card */
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        /* Estilos do Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #007bff; /* Cor do cabeçalho do modal */
            color: white; /* Cor do texto do cabeçalho do modal */
            border-radius: 8px 8px 0 0;
        }

        .modal-body {
            padding: 20px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Estilo do botão de detalhes */
        .btn-detalhes {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-detalhes:hover {
            background-color: #138496;
        }

        /* Estilos dos Botões */
        .btn {
            padding: 10px 15px; /* Aumenta o padding para um botão mais espaçoso */
            border-radius: 5px; /* Bordas arredondadas */
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s; /* Transições suaves */
            font-weight: bold; /* Negrito para os botões */
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

        /* Estilos das Células de Status */
        .status-cell {
            padding: 5px;
            border-radius: 4px;
            color: white; /* Cor do texto */
        }

        .status-em-aberto {
            background-color: #ffc107; /* Amarelo */
        }

        .status-finalizado {
            background-color: #28a745; /* Verde */
        }

        .status-pendente {
            background-color: #dc3545; /* Vermelho */
        }

        .status-concluido {
            background-color: #28a745; /* Verde para Concluído */
            color: white; /* Cor do texto */
        }

        .status-em-andamento {
            background-color: #ffc107; /* Amarelo para Em Andamento */
            color: black; /* Cor do texto */
        }

        /* Estilos das Células de Status de Pagamento */
        .status-pago {
            background-color: #28a745; /* Verde para Pago */
            color: white; /* Cor do texto */
        }

        .status-aberto {
            background-color: #ffc107; /* Amarelo para Aberto */
            color: black; /* Cor do texto */
        }

        .btn-disabled {
            background-color: #d3d3d3; /* Cor cinza */
            color: #a9a9a9; /* Cor do texto cinza */
            cursor: not-allowed; /* Cursor de não permitido */
            pointer-events: none; /* Desabilita eventos de clique */
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <div class="header-content">
                <h2>Relatório de Serviços</h2>
                <div class="search-container">
                    <input type="text" name="search" id="search" class="search-input" placeholder="Buscar serviços...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table id="tabelaServicos" class="table">
                        <thead>
                            <tr>
                                <th>Nº Proposta</th>
                                <th>Cliente</th>
                                <th>CNPJ/CPF</th>
                                <th>Serviços</th>
                                <th>Data Início</th>
                                <th>Data Término</th>
                                <th>Status Serviço</th>
                                <th>Orçamento</th>
                                <th>Entrada</th>
                                <th>Valor Líquido</th>
                                <th>Total Despesas</th>
                                <th>Pagamento</th>
                                <th>Parcelamento</th>
                                <th>Status Pagamento</th>
                                <th>Valor Pago</th>
                                <th>Valor A Ser Pago</th>
                                <th>Próximo Pagamento</th>
                                <th>Detalhes</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($servicos)): ?>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr data-proposta="<?php echo $servico['numero_proposta']; ?>">
                                        <td><?php echo htmlspecialchars($servico['numero_proposta']); ?></td>
                                        <td><?php echo htmlspecialchars($servico['cliente_nome_ou_razao']); ?></td>
                                        <td><?php echo htmlspecialchars($servico['cnpj_cpf']); ?></td>
                                        <td><?php echo empty($servico['tipos_servico']) ? 'Nenhum' : htmlspecialchars($servico['tipos_servico']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($servico['data_inicio'])); ?></td>
                                        <td><?php echo ($servico['data_termino'] && $servico['data_termino'] != '0000-00-00') ? date('d/m/Y', strtotime($servico['data_termino'])) : ''; ?></td>
                                        <td class="status-cell status-<?php echo strtolower(str_replace(' ', '-', $servico['status_servico'])); ?>">
                                            <?php echo htmlspecialchars($servico['status_servico']); ?>
                                        </td>
                                        <td class="valor">R$ <?php echo number_format($servico['valor_total'], 2, ',', '.'); ?></td>
                                        <td class="valor">R$ <?php echo number_format($servico['valor_entrada'], 2, ',', '.'); ?></td>
                                        <td class="valor">R$ <?php echo number_format($servico['valor_a_pagar'], 2, ',', '.'); ?></td>
                                        <td class="valor">R$ <?php echo number_format($servico['total_despesas'], 2, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($servico['forma_pagamento']); ?></td>
                                        <td><?php echo htmlspecialchars($servico['parcelamento']); ?></td>
                                        <td class="status-cell status-<?php echo strtolower(str_replace(' ', '-', $servico['status_pagamento'])); ?>">
                                            <?php echo htmlspecialchars($servico['status_pagamento']); ?>
                                        </td>
                                        <td class="valor valor-pago">R$ <?php echo number_format($servico['total_pago'], 2, ',', '.'); ?></td>
                                        <td class="valor valor-pendente">R$ <?php echo number_format($servico['total_pendente'], 2, ',', '.'); ?></td>
                                        <td class="proximo-pagamento">
                                            <?php 
                                            if ($servico['status_pagamento'] == 'FINALIZADO') {
                                                echo '-';
                                            } else {
                                                echo $servico['proximo_pagamento'] ? date('d/m/Y', strtotime($servico['proximo_pagamento'])) : '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn-detalhes" onclick="verDetalhes(<?php echo $servico['numero_proposta']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        </td>
                                        <td class="actions">
                                            <button type="button" class="btn-editar" onclick="window.location.href='editar_servico.php?id=<?php echo $servico['numero_proposta']; ?>'">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn-excluir" onclick="confirmarExclusao(<?php echo $servico['numero_proposta']; ?>)">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="20" class="text-center">Nenhum serviço encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detalhes do Pagamento</h3>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="detalhesContent"></div>
            </div>
        </div>
    </div>

    <script>
    function confirmarExclusao(numeroProposta) {
        if (confirm('Tem certeza que deseja excluir este serviço?')) {
            // Criar um formulário dinâmico para enviar via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'excluir_servico.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'numero_proposta';
            input.value = numeroProposta;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function verDetalhes(numeroProposta) {
        const modal = document.getElementById('modalDetalhes');
        modal.style.display = 'block';
        document.getElementById('detalhesContent').innerHTML = '<p>Carregando...</p>';
        
        $.ajax({
            url: 'get_payment_details.php',
            method: 'GET',
            data: { numero_proposta: numeroProposta },
            dataType: 'json',
            success: function(response) {
                try {
                    if (response.success && response.parcelas && response.parcelas.length > 0) {
                        let html = `
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Parcela</th>
                                        <th>Status</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        response.parcelas.forEach((parcela) => {
                            const statusClass = parcela.status_pagamento.toLowerCase();
                            html += `
                                <tr>
                                    <td>${parcela.parcela_num}</td>
                                    <td class="status-cell status-${statusClass}">${parcela.status_pagamento}</td>
                                    <td>${parcela.dia_pagamento}</td>
                                    <td class="valor">R$ ${parcela.valor_parcela}</td>
                                    <td>
                                        <button class="btn btn-primary ${parcela.status_pagamento.toLowerCase() === 'pago' ? 'btn-disabled' : ''}" 
                                                onclick="confirmarPagamento(${numeroProposta}, ${parcela.parcela_num}, '${parcela.valor_parcela}', '${parcela.dia_pagamento}')" 
                                                ${parcela.status_pagamento.toLowerCase() === 'pago' ? 'disabled' : ''}>
                                            ${parcela.status_pagamento.toLowerCase() === 'pago' ? 'Pago' : 'Pagar'}
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        html += `</tbody></table>`;
                        document.getElementById('detalhesContent').innerHTML = html;
                    } else {
                        document.getElementById('detalhesContent').innerHTML = '<p>Nenhuma parcela encontrada para este serviço.</p>';
                    }
                } catch (e) {
                    console.error('Erro ao processar resposta:', e);
                    console.error('Resposta recebida:', response);
                    document.getElementById('detalhesContent').innerHTML = '<p>Erro ao processar dados do pagamento.</p>';
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição:', error);
                console.error('Status:', status);
                console.error('Resposta:', xhr.responseText);
                document.getElementById('detalhesContent').innerHTML = '<p>Erro ao carregar detalhes do pagamento.</p>';
            }
        });
    }

    function fecharModal() {
        document.getElementById('modalDetalhes').style.display = 'none';
    }

    // Fechar modal quando clicar fora
    window.onclick = function(event) {
        const modal = document.getElementById('modalDetalhes');
        if (event.target == modal) {
            fecharModal();
        }
    }

    function confirmarPagamento(numeroProposta, parcela, valor, data) {
        if (confirm('Confirmar o pagamento desta parcela?')) {
            $.ajax({
                url: 'atualizar_pagamento.php',
                method: 'POST',
                data: {
                    numero_proposta: numeroProposta,
                    parcela_num: parcela,
                    valor_parcela: valor,
                    data_pagamento: data
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Atualiza o status e o botão da parcela
                        const linha = $(`button[onclick*="confirmarPagamento(${numeroProposta}, ${parcela})"]`).closest('tr');
                        linha.find('.status-cell').removeClass('status-aberto').addClass('status-pago').text('Pago');
                        linha.find('button').prop('disabled', true).text('Pago');

                        // Atualiza os valores na tabela principal
                        const linhaServico = $(`tr[data-proposta="${numeroProposta}"]`);
                        linhaServico.find('.valor-pago').text(`R$ ${formatarMoeda(response.total_pago)}`);
                        linhaServico.find('.valor-pendente').text(`R$ ${formatarMoeda(response.total_pendente)}`);
                        
                        // Atualiza o próximo pagamento
                        if (response.proximo_pagamento) {
                            linhaServico.find('.proximo-pagamento').text(response.proximo_pagamento);
                        } else {
                            linhaServico.find('.proximo-pagamento').text('-');
                        }

                        // Verifica se todas as parcelas foram pagas
                        if (parseFloat(response.total_pendente) <= 0) {
                            // Atualiza o status de pagamento para FINALIZADO
                            const statusCell = linhaServico.find('.status-pagamento');
                            statusCell
                                .removeClass('status-pendente status-aberto')
                                .addClass('status-finalizado')
                                .text('FINALIZADO');

                            // Atualiza a célula de próximo pagamento
                            linhaServico.find('.proximo-pagamento').text('-');
                        }

                        // Recarrega os detalhes do pagamento
                        verDetalhes(numeroProposta);
                    } else {
                        alert('Erro ao confirmar pagamento: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', error);
                    alert('Erro ao processar pagamento');
                }
            });
        }
    }

    // Função auxiliar para formatar valores monetários
    function formatarMoeda(valor) {
        return parseFloat(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    document.getElementById('search').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tabelaServicos tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let rowContainsSearchValue = false;

            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchValue)) {
                    rowContainsSearchValue = true;
                }
            });

            if (rowContainsSearchValue) {
                row.style.display = ''; // Exibe a linha
            } else {
                row.style.display = 'none'; // Esconde a linha
            }
        });
    });
    </script>
</body>
</html>