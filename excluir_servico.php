<?php
include 'conexao.php';

// Verifica se o número da proposta foi enviado
if (isset($_POST['numero_proposta'])) {
    $numero_proposta = $_POST['numero_proposta'];

    // Inicia a transação
    $conn->begin_transaction();

    try {
        // Primeiro, exclui os registros relacionados na tabela servico_tipo_servico
        $sql_related = "DELETE FROM servico_tipo_servico WHERE servico_id IN (SELECT id FROM servicos WHERE numero_proposta = ?)";
        $stmt_related = $conn->prepare($sql_related);
        $stmt_related->bind_param("i", $numero_proposta);
        $stmt_related->execute();
        $stmt_related->close();

        // Agora, exclui o serviço principal
        $sql_main = "DELETE FROM servicos WHERE numero_proposta = ?";
        $stmt_main = $conn->prepare($sql_main);
        $stmt_main->bind_param("i", $numero_proposta);
        $stmt_main->execute();
        $stmt_main->close();

        $sql_delete_parcelas = "DELETE FROM pagamentos WHERE numero_proposta = ?";
        $stmt_delete = $conn->prepare($sql_delete_parcelas);
        $stmt_delete->bind_param("i", $numero_proposta);
        $stmt_delete->execute();
        $stmt_delete->close();

        $sql_delete_despesas = "DELETE FROM despesas WHERE proposta = ?";
        $stmt_del = $conn->prepare($sql_delete_despesas);
        $stmt_del->bind_param("i", $numero_proposta);
        $stmt_del->execute();
        $stmt_del->close();
        
        // Se tudo ocorreu bem, confirma a transação
        $conn->commit();

        echo "<script>
                alert('Registro excluído com sucesso!');
                window.location.href = 'gerenciar_relatorio.php';
              </script>";
    } catch (Exception $e) {
        // Se ocorreu algum erro, desfaz as alterações
        $conn->rollback();

        echo "<script>
                alert('Erro ao excluir o registro: " . $e->getMessage() . "');
                window.location.href = 'gerenciar_relatorio.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Número da proposta não informado!');
            window.location.href = 'gerenciar_relatorio.php';
          </script>";
}

$conn->close();
?>