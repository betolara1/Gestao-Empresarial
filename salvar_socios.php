<?php
require_once 'conexao.php';

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Recebe os dados do formulário
$nome = isset($_POST['nome']) ? $_POST['nome'] : '';
$porcentagem_sociedade = isset($_POST['porcentagem_sociedade']) ? $_POST['porcentagem_sociedade'] : 0;
$porcentagem_comissao = isset($_POST['porcentagem_comissao']) ? $_POST['porcentagem_comissao'] : 0;

// Validações básicas
if (empty($nome) || $porcentagem_sociedade < 0 || $porcentagem_comissao < 0) {
    header('Location: cadastro_socio.php?erro=1');
    exit;
}

// Prepara e executa a query
$sql = "INSERT INTO socios (nome, porcentagem_sociedade, porcentagem_comissao) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sdd", $nome, $porcentagem_sociedade, $porcentagem_comissao);

if ($stmt->execute()) {
    header('Location: gerenciar_empresa.php?success=3'); // 3 = cadastro realizado com sucesso
} else {
    header('Location: cadastro_socio.php?erro=2'); // 2 = erro ao cadastrar
}
exit;
?>