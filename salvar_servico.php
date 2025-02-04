<?php
include 'conexao.php';

// Função para limpar dados de entrada
function limparEntrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Limpa e valida os dados recebidos
        $numero_proposta = (int) limparEntrada($_POST['numero_proposta']);

        // Verifica se o número da proposta já existe
        $stmt_verificacao = $conn->prepare("SELECT COUNT(*) as total FROM servicos WHERE numero_proposta = ?");
        $stmt_verificacao->bind_param("i", $numero_proposta);
        $stmt_verificacao->execute();
        $result = $stmt_verificacao->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] > 0) {
            throw new Exception("Número da proposta já existe. Por favor, escolha outro número.");
        }

        $cliente_id = (int) limparEntrada($_POST['cliente']);
        $cnpj_cpf = limparEntrada($_POST['cnpj_cpf']);
        $data_inicio = limparEntrada($_POST['data_inicio']);
        $data_termino = !empty($_POST['data_termino']) ? limparEntrada($_POST['data_termino']) : null;
        $data_pagamento_inicial = limparEntrada($_POST['data_pagamento']);
        $valor_total = (float) limparEntrada($_POST['valor_total']);
        $valor_entrada = (float) limparEntrada($_POST['valor_entrada']);
        $forma_pagamento = limparEntrada($_POST['forma_pagamento']);
        $parcelamento = (int) limparEntrada($_POST['parcelamento']);
        $status_servico = limparEntrada($_POST['status_servico']);
        $responsavel_execucao = limparEntrada($_POST['responsavel_execucao']);
        $origem_demanda = limparEntrada($_POST['origem_demanda']);
        $cep = limparEntrada($_POST['cep']);
        $rua = limparEntrada($_POST['rua']);
        $numero = limparEntrada($_POST['numero']);
        $complemento = limparEntrada($_POST['complemento']);
        $bairro = limparEntrada($_POST['bairro']);
        $cidade = limparEntrada($_POST['cidade']);
        $estado = limparEntrada($_POST['estado']);
        $coordenada = limparEntrada($_POST['coordenada']);
        $observacao = limparEntrada($_POST['observacao']);

        // Calcula o valor restante a ser pago
        $valor_pagar = $valor_total - $valor_entrada;
        $valor_parcela = $parcelamento > 0 ? $valor_pagar / $parcelamento : $valor_pagar;

        // Iniciar transação
        $conn->begin_transaction();

        // Insere os dados do serviço na tabela `servicos`
        $sql_servico = "INSERT INTO servicos 
                        (numero_proposta, cliente_id, cnpj_cpf, cep, rua, 
                        numero, complemento, bairro, cidade, estado, coordenada, 
                        data_inicio, data_termino, data_pagamento, valor_total, valor_entrada,
                         forma_pagamento, parcelamento, status_servico, observacao, responsavel_execucao, origem_demanda)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_servico = $conn->prepare($sql_servico);
        $stmt_servico->bind_param(
            "iissssssssssssddssssss",
            $numero_proposta, 
            $cliente_id, 
            $cnpj_cpf, 
            $cep, 
            $rua, 
            $numero, 
            $complemento, 
            $bairro, 
            $cidade, 
            $estado, 
            $coordenada,
            $data_inicio, 
            $data_termino, 
            $data_pagamento_inicial, 
            $valor_total, 
            $valor_entrada, 
            $forma_pagamento, 
            $parcelamento, 
            $status_servico,
            $observacao,
            $responsavel_execucao, 
            $origem_demanda
        );
        $stmt_servico->execute();

        // Insere os tipos de serviço associados na tabela `servico_tipo_servico`
        if (!empty($_POST['tipo_servico'])) {
            $tipo_servico = $_POST['tipo_servico'];
            $servico_id = $conn->insert_id;
            $sql_tipo_servico = "INSERT INTO servico_tipo_servico (servico_id, tipo_servico_id) VALUES (?, ?)";
            $stmt_tipo_servico = $conn->prepare($sql_tipo_servico);

            foreach ($tipo_servico as $tipo_id) {
                $stmt_tipo_servico->bind_param("ii", $servico_id, $tipo_id);
                $stmt_tipo_servico->execute();
            }
        }

        // Insere ou atualiza as parcelas na tabela `pagamentos`
        for ($i = 0; $i < $parcelamento; $i++) {
            $novo_id = $i + 1;
            $data_pagamento_parcela = date('Y-m-d', strtotime("+$i month", strtotime($data_pagamento_inicial)));
            $dia_pagamento = date('d', strtotime($data_pagamento_parcela));
            
            // Define o status do pagamento
            $status_pagamento = ($valor_total == $valor_entrada) ? 'Pago' : 'Aberto';
            
            $sql_parcela = "INSERT INTO pagamentos 
                            (numero_proposta, parcela_num, status_pagamento, valor_parcela, data_pagamento, dia_pagamento) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_parcela = $conn->prepare($sql_parcela);
            $stmt_parcela->bind_param(
                "iisdss", 
                $numero_proposta, 
                $novo_id, 
                $status_pagamento,
                $valor_parcela, 
                $data_pagamento_parcela,
                $dia_pagamento
            );
            $stmt_parcela->execute();
        }

        // Confirma a transação
        $conn->commit();

        echo "<script>
                alert('Serviço cadastrado com sucesso!');
                window.location.href = 'cadastro_servicos.php';
              </script>";

    } catch (Exception $e) {
        // Reverte a transação em caso de erro
        $conn->rollback();
        echo "<script>
                alert('Erro ao cadastrar serviço: " . $e->getMessage() . "');
                window.history.back();
              </script>";
    } finally {
        // Fecha conexões
        $stmt_servico->close();
        if (isset($stmt_tipo_servico)) $stmt_tipo_servico->close();
        if (isset($stmt_parcela)) $stmt_parcela->close();
        $conn->close();
    }
}
?>
