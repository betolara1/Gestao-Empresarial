<?php
include 'conexao.php';

try {
    // Inicia a transação
    $conn->begin_transaction();

    // Pega o tipo de pessoa e ID
    $tipo_pessoa = $_POST['tipo_pessoa'];
    $id = $_POST['id'];

    // Prepara os dados comuns
    $dados = [
        'cep' => $_POST['cep'],
        'rua' => $_POST['rua'],
        'numero' => $_POST['numero'],
        'complemento' => $_POST['complemento'] ?? null,
        'bairro' => $_POST['bairro'],
        'cidade' => $_POST['cidade'],
        'estado' => $_POST['estado'],
        'email' => $_POST['email'],
        'celular' => $_POST['celular'],
        'coordenada' => $_POST['coordenada'] ?? null
    ];

    // Prepara a query base
    $sql = "UPDATE cliente SET ";
    $params = [];
    $types = "";

    // Adiciona campos comuns
    foreach ($dados as $campo => $valor) {
        $sql .= "$campo = ?, ";
        $params[] = $valor;
        $types .= "s"; // assume string para todos os campos comuns
    }

    // Adiciona campos específicos baseado no tipo de pessoa
    if ($tipo_pessoa === 'F') {
        $sql .= "nome = ?, cpf = ?, ";
        $sql .= "razao_social = NULL, cnpj = NULL, codigo_cnae = NULL";
        $params[] = $_POST['nomeCliente'];
        $params[] = $_POST['cpf'];
        $types .= "ss";
    } else if ($tipo_pessoa === 'J') {
        $sql .= "razao_social = ?, cnpj = ?, codigo_cnae = ?, ";
        $sql .= "nome = NULL, cpf = NULL";
        $params[] = $_POST['razao_social'];
        $params[] = $_POST['cnpj'];
        $params[] = $_POST['atividade_principal'];
        $types .= "sss";
    }

    // Adiciona a condição WHERE
    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    // Remove vírgula extra se houver
    $sql = preg_replace('/,\s+(WHERE)/', ' $1', $sql);

    // Prepara e executa a query
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Erro na preparação da query: " . $conn->error);
    }

    // Bind dos parâmetros dinamicamente
    $stmt->bind_param($types, ...$params);

    // Executa a query
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar cliente: " . $stmt->error);
    }

    // Commit da transação
    $conn->commit();

    // Redireciona com mensagem de sucesso
    header("Location: gerenciar_clientes.php?mensagem=" . urlencode("Cliente atualizado com sucesso!"));
    exit;

} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    
    // Redireciona com mensagem de erro
    $error = urlencode($e->getMessage());
    header("Location: editar_cliente.php?id=$id&error=$error");
    exit;
}

$conn->close();
?>