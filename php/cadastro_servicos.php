<?php 
include 'conexao.php';

// Busca o último número da proposta e incrementa para o próximo
$sql_proposta = "SELECT COALESCE(MAX(numero_proposta), 0) + 1 AS proximo_numero FROM servicos";
$result_proposta = $conn->query($sql_proposta);
$row_proposta = $result_proposta->fetch_assoc();
$numero_proposta = $row_proposta['proximo_numero'];

// Função PHP para buscar o CNPJ/CPF do cliente selecionado
if (isset($_POST['buscar_cliente']) && isset($_POST['cliente_id'])) {
    $cliente_id = $_POST['cliente_id'];
    $sql_cliente = "SELECT cnpj, cpf FROM cliente WHERE id = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente = $result_cliente->fetch_assoc();
    echo json_encode($cliente);
    exit;
}

// Consulta SQL para buscar todos os tipos de serviços
$sqlTipoServico = "SELECT tipo_servico FROM tipos_servicos";
$resultTipoServico = $conn->query($sqlTipoServico);


// Prepara a consulta SQL usando prepared statement para evitar SQL injection
$sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $numero_proposta);
$stmt->execute();
$result = $stmt->get_result();

// Fecha a conexão e o statement
$stmt->close();
$conn->close();

?>