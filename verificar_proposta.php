<?php
include 'conexao.php';

header('Content-Type: application/json');

$numero_proposta = $_POST['numero_proposta'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM servicos WHERE numero_proposta = ?");
$stmt->bind_param("i", $numero_proposta);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['existe' => $row['total'] > 0]);

$conn->close();
?> 