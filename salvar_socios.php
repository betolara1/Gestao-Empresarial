<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Verifica se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido');
    }

    // Recebe e valida os dados
    $nome = trim($_POST['nome'] ?? '');
    $porcentagem_sociedade = floatval($_POST['porcentagem_sociedade'] ?? 0);
    $porcentagem_comissao = floatval($_POST['porcentagem_comissao'] ?? 0);

    // Validações
    if (empty($nome)) {
        throw new Exception('Nome é obrigatório');
    }

    if ($porcentagem_sociedade < 0 || $porcentagem_sociedade > 100) {
        throw new Exception('Porcentagem de sociedade inválida');
    }

    if ($porcentagem_comissao < 0 || $porcentagem_comissao > 100) {
        throw new Exception('Porcentagem de comissão inválida');
    }

    // Verifica se a soma das porcentagens de sociedade não ultrapassa 100%
    $sql = "SELECT SUM(porcentagem_sociedade) as total FROM socios";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_atual = floatval($row['total']);

    if (($total_atual + $porcentagem_sociedade) > 100) {
        throw new Exception('A soma das porcentagens de sociedade não pode ultrapassar 100%');
    }

    // Insere o novo sócio
    $sql = "INSERT INTO socios (nome, porcentagem_sociedade, porcentagem_comissao) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdd", $nome, $porcentagem_sociedade, $porcentagem_comissao);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao cadastrar sócio');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sócio cadastrado com sucesso'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>