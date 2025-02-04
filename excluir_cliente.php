<?php
include 'conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Inicia uma transação
    $conn->begin_transaction();
    
    try {
        // Encontra todos os serviços vinculados ao cliente
        $sql_servicos_ids = "SELECT id FROM servicos WHERE cliente_id = ?";
        $stmt_servicos_ids = $conn->prepare($sql_servicos_ids);
        $stmt_servicos_ids->bind_param("i", $id);
        $stmt_servicos_ids->execute();
        $result_servicos_ids = $stmt_servicos_ids->get_result();

        while ($row = $result_servicos_ids->fetch_assoc()) {
            $servico_id = $row['id'];

            // Exclui os registros relacionados na tabela servico_tipo_servico
            $sql_servico_tipo = "DELETE FROM servico_tipo_servico WHERE servico_id = ?";
            $stmt_servico_tipo = $conn->prepare($sql_servico_tipo);
            $stmt_servico_tipo->bind_param("i", $servico_id);
            $stmt_servico_tipo->execute();
            $stmt_servico_tipo->close();
        }
        
        $stmt_servicos_ids->close();

        // Exclui os serviços vinculados ao cliente
        $sql_servicos = "DELETE FROM servicos WHERE cliente_id = ?";
        $stmt_servicos = $conn->prepare($sql_servicos);
        $stmt_servicos->bind_param("i", $id);
        $stmt_servicos->execute();
        $stmt_servicos->close();
        
        // Exclui o cliente
        $sql_cliente = "DELETE FROM cliente WHERE id = ?";
        $stmt_cliente = $conn->prepare($sql_cliente);
        $stmt_cliente->bind_param("i", $id);
        
        if ($stmt_cliente->execute()) {
            // Confirma todas as operações
            $conn->commit();
            echo "<script>
                    window.location.href = 'gerenciar_clientes.php';
                  </script>";
        } else {
            throw new Exception("Erro ao excluir o cliente");
        }
        
        $stmt_cliente->close();
        
    } catch (Exception $e) {
        // Em caso de erro, desfaz todas as operações
        $conn->rollback();
        echo "<script>
                alert('Erro ao excluir cliente: " . $e->getMessage() . "');
                window.location.href = 'gerenciar_clientes.php';
              </script>";
    }
    
} else {
    echo "<script>
            alert('ID do cliente não fornecido');
            window.location.href = 'gerenciar_clientes.php';
          </script>";
}

$conn->close();
?>
