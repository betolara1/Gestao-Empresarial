<?php
include 'conexao.php';

// Função para limpar dados de entrada
function limparEntrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Configurar cabeçalhos para resposta JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparEntrada($_POST['nome_despesa']);
    $valor = limparEntrada($_POST['valor_despesa']);
    $proposta = limparEntrada($_POST['numero_proposta']);

    // Validação simples
    if (empty($nome) || $valor <= 0 || empty($proposta)) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    $sql = "INSERT INTO despesas (proposta, nome_despesa, valor) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $proposta, $nome, $valor);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'id' => $stmt->insert_id,
            'proposta' => $proposta,
            'nome_despesa' => $nome,
            'valor_despesa' => $valor
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar despesa.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>