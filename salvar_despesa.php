<?php
include 'conexao.php';

header('Content-Type: application/json');

try {
    // Validar dados recebidos
    if (empty($_POST['nome_despesa']) || !isset($_POST['valor_despesa'])) {
        throw new Exception('Dados incompletos');
    }

    $nome_despesa = $_POST['nome_despesa'];
    $valor_despesa = floatval(str_replace(',', '.', $_POST['valor_despesa']));
    $numero_proposta = $_POST['numero_proposta'] ?? null;

    // Inserir no banco de dados
    $stmt = $conn->prepare("INSERT INTO despesas (nome_despesa, valor, numero_proposta) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $nome_despesa, $valor_despesa, $numero_proposta);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'id' => $conn->insert_id,
            'nome_despesa' => $nome_despesa,
            'valor_despesa' => $valor_despesa
        ]);
    } else {
        throw new Exception('Erro ao salvar no banco de dados');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>