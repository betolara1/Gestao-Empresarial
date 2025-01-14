<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $mes = $_POST['mes'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    // Prepara e executa a query de atualização
    $sql = "UPDATE despesas_fixas SET 
                mes = ?,
                descricao = ?,
                valor = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $mes, $descricao, $valor, $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Despesa atualizada com sucesso!');
                window.location.href = 'gerenciar_despesa_fixa.php';
              </script>";
    } else {
        echo "<script>
                alert('Erro ao atualizar despesa: " . $conn->error . "');
                window.location.href = 'editar_despesa_fixa.php?id=" . $id . "';
              </script>";
    }

    $stmt->close();
} else {
    echo "<script>
            alert('Método inválido de requisição');
            window.location.href = 'gerenciar_despesa_fixa.php';
          </script>";
}

$conn->close();
?>