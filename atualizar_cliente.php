<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $tipo_pessoa = strtoupper($_POST['tipo_pessoa'] ?? ''); // Convert to uppercase
    $razao_social = $_POST['razao_social'] ?? null;
    $cnpj = $_POST['cnpj'] ?? null;
    $nome = $_POST['nomeCliente'] ?? null;
    $cpf = $_POST['cpf'] ?? null;
    $cep = $_POST['cep'] ?? '';
    $rua = $_POST['rua'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $celular = $_POST['celular'] ?? '';
    $email = $_POST['email'] ?? '';
    $coordenada = $_POST['coordenada'] ?? '';
    $codigo_cnae = $_POST['atividade_principal'] ?? '';
    $descricao_cnae = $_POST['descricao_cnae'];

    // Validação de ID
    if (!$id) {
        die("ID do cliente não informado.");
    }

    // Validação do tipo_pessoa
    if (!in_array($tipo_pessoa, ['F', 'J'])) {
        die("Tipo de pessoa inválido. Use 'F' para Pessoa Física ou 'J' para Pessoa Jurídica.");
    }

    // Validações específicas baseadas no tipo de pessoa
    if ($tipo_pessoa === 'F') {
        if (empty($cpf) || empty($nome)) {
            die("Para pessoa física, CPF e nome são obrigatórios.");
        }
        // Limpar campos de PJ
        $razao_social = null;
        $cnpj = null;
    } else { // PJ
        if (empty($cnpj) || empty($razao_social)) {
            die("Para pessoa jurídica, CNPJ e razão social são obrigatórios.");
        }
        // Limpar campos de PF
        $cpf = null;
        $nome = null;
    }

    // Atualização do cliente
    $sql = "UPDATE cliente 
        SET tipo_pessoa=?, razao_social=?, cnpj=?, nome=?, cpf=?, cep=?, rua=?, numero=?, 
            complemento=?, bairro=?, cidade=?, estado=?, celular=?, email=?, coordenada=?,
            codigo_cnae=?, descricao_cnae=?
        WHERE id=?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssssssssi", 
            $tipo_pessoa, $razao_social, $cnpj, $nome, $cpf, $cep, $rua, $numero, 
            $complemento, $bairro, $cidade, $estado, $celular, $email, $coordenada,
            $codigo_cnae, $descricao_cnae, $id
        );

        if ($stmt->execute()) {
            echo "<script>
                    alert('Cliente atualizado com sucesso!');
                    window.location.href = 'gerenciar_clientes.php';
                  </script>";
        } else {
            throw new Exception("Erro ao atualizar cliente: " . $stmt->error);
        }
    } catch (mysqli_sql_exception $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    } catch (Exception $e) {
        die($e->getMessage());
    }

    $stmt->close();
}
$conn->close();
?>