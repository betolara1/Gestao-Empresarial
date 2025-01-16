<?php
include 'conexao.php';

$dados = [
    "numero_proposta" => "105",
    "tipos_servico" => [
        ["id" => "5", "tipo_servico" => "Informatica"],
        ["id" => "6", "tipo_servico" => "Programador"]
    ],
    "despesas" => []
];

// Armazene os dados em variáveis PHP em vez de imprimir diretamente
$numero_proposta = $dados['numero_proposta'];
$tipos_servico = $dados['tipos_servico'];
$despesas = $dados['despesas'];

// Verifica se é uma requisição AJAX para buscar dados do cliente
if (isset($_POST['buscar_cliente']) && isset($_POST['cliente_id'])) {
    $cliente_id = $_POST['cliente_id'];
    $sql_cliente = "SELECT cnpj, cpf FROM cliente WHERE id = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente = $result_cliente->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($cliente);
    $stmt_cliente->close();
    exit;
}

try {
    // Busca o último número da proposta e incrementa para o próximo
    $sql_proposta = "SELECT COALESCE(MAX(numero_proposta), 0) + 1 AS proximo_numero FROM servicos FOR UPDATE";
    $result_proposta = $conn->query($sql_proposta);
    $row_proposta = $result_proposta->fetch_assoc();
    $numero_proposta = $row_proposta['proximo_numero'];

    // Consulta SQL para buscar todos os tipos de serviços
    $sqlTipoServico = "SELECT id, tipo_servico FROM tipos_servicos";
    $resultTipoServico = $conn->query($sqlTipoServico);
    $tipos_servico = $resultTipoServico->fetch_all(MYSQLI_ASSOC);

    // Buscar despesas existentes
    $sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();
    $despesas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Commit a transação
    $conn->commit();

    // Se for uma requisição AJAX, retorna JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'numero_proposta' => $numero_proposta,
            'tipos_servico' => $tipos_servico,
            'despesas' => $despesas
        ]);
        exit;
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>