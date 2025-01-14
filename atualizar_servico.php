<?php
include 'conexao.php';

function limparEntrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validação e limpeza dos dados do formulário
        $numero_proposta = limparEntrada($_POST['numero_proposta']); // ID do serviço a ser atualizado
        $tipo_servico = isset($_POST['tipo_servico']) ? $_POST['tipo_servico'] : [];
        $data_inicio = limparEntrada($_POST['data_inicio']);
        $data_termino = limparEntrada($_POST['data_termino']);
        $data_pagamento = limparEntrada($_POST['data_pagamento']);
        $status_servico = limparEntrada($_POST['status_servico']);
        $responsavel_execucao = limparEntrada($_POST['responsavel_execucao']);
        $forma_pagamento = limparEntrada($_POST['forma_pagamento']);
        $parcelamento = limparEntrada($_POST['parcelamento']);
        $valor_total = limparEntrada($_POST['valor_total']);
        $valor_entrada = limparEntrada($_POST['valor_entrada']);
        $cep = limparEntrada($_POST['cep']);
        $rua = limparEntrada($_POST['rua']);
        $numero = limparEntrada($_POST['numero']);
        $complemento = limparEntrada($_POST['complemento']);
        $bairro = limparEntrada($_POST['bairro']);
        $cidade = limparEntrada($_POST['cidade']);
        $estado = limparEntrada($_POST['estado']);
        $coordenada = limparEntrada($_POST['coordenada']);

        $conn->begin_transaction();

        // Update the servicos table
        $stmt = $conn->prepare("
            UPDATE servicos SET 
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
            WHERE numero_proposta = ?
        ");

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
            throw new Exception("Erro ao atualizar o serviço: " . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();

        echo "<script>
                alert('Serviço atualizado com sucesso!');
                window.location.href = 'editar_servico.php?id=$numero_proposta';
              </script>";
    } catch (Exception $e) {
        echo "<script>
                alert('" . $e->getMessage() . "');
                window.history.back();
              </script>";
    } finally {
        $stmt->close();
        $conn->close();
    }
}
?>

