<?php
include 'conexao.php';

// Verifica se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$id = $_GET['id'];

// Verifica se existem retiradas associadas a este sócio
$sql = "SELECT COUNT(*) as total FROM retiradas_socios WHERE socio_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
  // Se existirem retiradas, não permite a exclusão
  header('Location: index.php?erro=1');
  exit;
}

// Procede com a exclusão
$sql = "DELETE FROM socios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  echo "<script>
        alert('Sócio cadastrado com sucesso!');
        window.location.href = 'gerenciar_empresa.php';
      </script>";
} else {
  echo "<script>
  alert('Sócio cadastrado com sucesso!');
  window.location.href = 'gerenciar_empresa.php';
</script>";
}
exit;
?>