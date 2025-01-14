<?php
include 'conexao.php';

// Função para limpar dados de entrada
function limparEntrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_pessoa = limparEntrada($_POST['tipoPessoa']);
    $razao_social = ($tipo_pessoa == 'J') ? limparEntrada($_POST['razaoSocial']) : null;
    $cnpj = ($tipo_pessoa == 'J') ? limparEntrada($_POST['cnpj']) : null;
    $nome = limparEntrada($_POST['nomeCliente']);
    $cpf = ($tipo_pessoa == 'F') ? limparEntrada($_POST['cpf']) : null;
    $cep = limparEntrada($_POST['cep']);
    $rua = limparEntrada($_POST['rua']);
    $numero = limparEntrada($_POST['numero']);
    $complemento = limparEntrada($_POST['complemento']);
    $bairro = limparEntrada($_POST['bairro']);
    $cidade = limparEntrada($_POST['cidade']);
    $estado = limparEntrada($_POST['estado']);
    $coordenada = limparEntrada($_POST['coordenada']);
    $celular = limparEntrada($_POST['celular']);
    $email = limparEntrada($_POST['email']);

    // Processo específico para pessoa jurídica
    if ($tipo_pessoa == 'J') {
        $codigo_cnae = limparEntrada($_POST['atividade_principal']);
        $descricao_cnae = limparEntrada($_POST['descricao_cnae']);
    } else {
        $codigo_cnae = null;
        $descricao_cnae = null;
    }

    // Modifique a query de inserção para incluir os campos de CNAE
    $sql = "INSERT INTO cliente (
        tipo_pessoa, razao_social, cnpj, nome, cpf, cep, rua, 
        numero, complemento, bairro, cidade, estado, coordenada, 
        celular, email, codigo_cnae, descricao_cnae
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssssssss",
            $tipo_pessoa, $razao_social, $cnpj, $nome, $cpf, $cep, $rua, 
            $numero, $complemento, $bairro, $cidade, $estado, $coordenada,
            $celular, $email, $codigo_cnae, $descricao_cnae
        );

        if ($stmt->execute()) {
            echo "<script>
                    alert('Cliente cadastrado com sucesso!');
                    window.location.href = 'cadastro_cliente.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Erro ao cadastrar cliente: " . $stmt->error . "');
                    window.history.back();
                  </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
                alert('Erro na preparação da query: " . $conn->error . "');
                window.history.back();
              </script>";
    }

    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
