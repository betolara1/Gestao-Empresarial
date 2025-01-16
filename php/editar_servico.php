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
