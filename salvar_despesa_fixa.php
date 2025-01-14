<?php
include 'conexao.php';

// Função para limpar dados de entrada
function limparEntrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = limparEntrada($_POST['descricao']);
    $valor = limparEntrada($_POST['valor']);
    $mes = limparEntrada($_POST['mes']);
    $ano = limparEntrada($_POST['ano']);
    
    // Cria a data completa com o primeiro dia do mês
    $data = "$ano-$mes-01";
    
    $query = "INSERT INTO despesas_fixas (descricao, valor, data) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sds", $descricao, $valor, $data);
    
    if ($stmt->execute()) {
        header('Location: cadastro_despesas_fixas.php');
    } else {
        header('Location: cadastro_despesa_fixa.php');
    }
} else {
    header('Location: cadastro_despesa_fixa.php');
}
?>

    