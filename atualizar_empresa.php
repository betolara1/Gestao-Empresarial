<?php
include 'conexao.php';

function limparEntrada($data) {
    if (is_array($data)) {
        return array_map('limparEntrada', $data);
    } else {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $razao_social = limparEntrada($_POST['razaoSocial']);
    $cnpj = limparEntrada($_POST['cnpj']);
    $nome = limparEntrada($_POST['nome']);
    $cpf = limparEntrada($_POST['cpf']);
    $cep = limparEntrada($_POST['cep']);
    $rua = limparEntrada($_POST['rua']);
    $numero = limparEntrada($_POST['numero']);
    $complemento = limparEntrada($_POST['complemento']);
    $bairro = limparEntrada($_POST['bairro']);
    $cidade = limparEntrada($_POST['cidade']);
    $estado = limparEntrada($_POST['estado']);
    $coordenada = limparEntrada($_POST['coordenada']);
    $telefone = limparEntrada($_POST['telefone']);
    $celular = limparEntrada($_POST['celular']);
    $email = limparEntrada($_POST['email']);
    $codigo_cnae = limparEntrada($_POST['atividade_principal']);
    $descricao_cnae = limparEntrada($_POST['descricao_cnae']);
    $atividades_secundarias = isset($_POST['atividades_secundarias']) ? limparEntrada($_POST['atividades_secundarias']) : [];
    $descricoes_secundarias = isset($_POST['descricoes_secundarias']) ? limparEntrada($_POST['descricoes_secundarias']) : [];

    $atividades_secundarias_string = implode(',', $atividades_secundarias);
    $descricoes_secundarias_string = implode('|||', $descricoes_secundarias);

    $sql = "UPDATE empresa SET 
        razao_social = ?, 
        cnpj = ?, 
        nome = ?, 
        cpf = ?, 
        cep = ?, 
        rua = ?, 
        numero = ?, 
        complemento = ?, 
        bairro = ?, 
        cidade = ?, 
        estado = ?, 
        coordenada = ?, 
        telefone = ?, 
        celular = ?, 
        email = ?, 
        atividades_secundarias = ?,
        descricoes_secundarias = ?,
        codigo_cnae = ?,
        descricao_cnae = ?
    WHERE id = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssssssss", 
        $razao_social, $cnpj, $nome, $cpf, $cep, $rua, 
        $numero, $complemento, $bairro, $cidade, $estado, $coordenada,
        $telefone, $celular, $email, $atividades_secundarias_string,
        $descricoes_secundarias_string, $codigo_cnae, $descricao_cnae
    );

    if ($stmt->execute()) {
        echo "<script>
                window.location.href = 'gerenciar_empresa.php';
              </script>";
    } else {
        echo "<script>
                alert('Erro ao atualizar empresa: " . $stmt->error . "');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
?>