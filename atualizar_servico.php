<?php
include 'conexao.php';

function limparEntrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validação e limpeza dos dados do formulário
        $numero_proposta = limparEntrada($_POST['numero_proposta']);
        $data_inicio = limparEntrada($_POST['data_inicio']);
        $data_termino = !empty($_POST['data_termino']) ? limparEntrada($_POST['data_termino']) : null;
        $data_pagamento = limparEntrada($_POST['data_pagamento']);
        $status_servico = limparEntrada($_POST['status_servico']);
        $responsavel_execucao = limparEntrada($_POST['responsavel_execucao']);
        $forma_pagamento = limparEntrada($_POST['forma_pagamento']);
        $parcelamento = limparEntrada($_POST['parcelamento']);
        $valor_total = limparEntrada($_POST['valor_total']);
        $valor_entrada = !empty($_POST['valor_entrada']) ? limparEntrada($_POST['valor_entrada']) : null;
        $cep = limparEntrada($_POST['cep']);
        $rua = limparEntrada($_POST['rua']);
        $numero = limparEntrada($_POST['numero']);
        $complemento = !empty($_POST['complemento']) ? limparEntrada($_POST['complemento']) : null;
        $bairro = limparEntrada($_POST['bairro']);
        $cidade = limparEntrada($_POST['cidade']);
        $estado = limparEntrada($_POST['estado']);
        $coordenada = !empty($_POST['coordenada']) ? limparEntrada($_POST['coordenada']) : null;

        $conn->begin_transaction();

        // Primeiro, execute o UPDATE e verifique se foi bem-sucedido
        $sql_update = "UPDATE servicos SET 
            data_inicio = ?,
            data_termino = ?,
            data_pagamento = ?,
            valor_total = ?,
            valor_entrada = ?,
            forma_pagamento = ?,
            parcelamento = ?,
            status_servico = ?,
            responsavel_execucao = ?,
            cep = ?,
            rua = ?,
            numero = ?,
            complemento = ?,
            bairro = ?,
            cidade = ?,
            estado = ?,
            coordenada = ?
            WHERE numero_proposta = ?";

        $stmt = $conn->prepare($sql_update);
        
        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param(
            "sssddssssssssssssi",
            $data_inicio,
            $data_termino,
            $data_pagamento,
            $valor_total,
            $valor_entrada,
            $forma_pagamento,
            $parcelamento,
            $status_servico,
            $responsavel_execucao,
            $cep,
            $rua,
            $numero,
            $complemento,
            $bairro,
            $cidade,
            $estado,
            $coordenada,
            $numero_proposta
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar o UPDATE: " . $stmt->error);
        }

        // Atualiza os tipos de serviço
        // Primeiro, pega o ID do serviço
        $sql_get_id = "SELECT id FROM servicos WHERE numero_proposta = ?";
        $stmt_id = $conn->prepare($sql_get_id);
        $stmt_id->bind_param("i", $numero_proposta);
        $stmt_id->execute();
        $result = $stmt_id->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Serviço não encontrado");
        }
        
        $servico_id = $result->fetch_assoc()['id'];

        // Remove tipos de serviço existentes
        $sql_delete = "DELETE FROM servico_tipo_servico WHERE servico_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $servico_id);
        $stmt_delete->execute();

        // Insere novos tipos de serviço
        if (isset($_POST['tipo_servico']) && is_array($_POST['tipo_servico'])) {
            $sql_insert = "INSERT INTO servico_tipo_servico (servico_id, tipo_servico_id) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            
            foreach ($_POST['tipo_servico'] as $tipo_servico_id) {
                $stmt_insert->bind_param("ii", $servico_id, $tipo_servico_id);
                if (!$stmt_insert->execute()) {
                    throw new Exception("Erro ao inserir tipo de serviço: " . $stmt_insert->error);
                }
            }
        }

        $conn->commit();
        
        echo "<script>
                window.location.href = 'gerenciar_relatorio.php';
              </script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
                alert('Erro ao atualizar o serviço: " . $e->getMessage() . "');
                window.history.back();
              </script>";
    } finally {
        $conn->close();
    }
}
?>