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
