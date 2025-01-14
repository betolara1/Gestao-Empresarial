<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';

// Busca os dados cadastrados na tabela `minhaempresa`
$sql_empresa = "SELECT * FROM empresa LIMIT 1";
$result_empresa = $conn->query($sql_empresa);

// Verifica se encontrou registros
if ($result_empresa->num_rows > 0) {
    $empresa = $result_empresa->fetch_assoc();
} else {
    $empresa = [];
}


if (isset($_POST['addArea'])) {
    $nome = $conn->real_escape_string($_POST['nome']);
        $sql_atuacao = "INSERT INTO areas_atuacao (nome) VALUES (?)";
        $stmt = $conn->prepare($sql_atuacao);
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->close();
} elseif (isset($_POST['delete_area'])) {
    $id = $conn->real_escape_string($_POST['id']);
    
    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, delete related rows in servico_tipo_servico
        $id = $conn->real_escape_string($_POST['id']);
        $sql_atuacao = "DELETE FROM areas_atuacao WHERE id = ?";
        $stmt = $conn->prepare($sql_atuacao);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // If we get here, it means both queries were successful
        $conn->commit();
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} 

$sql_atuacao = "SELECT * FROM areas_atuacao";
$result_atuacao = $conn->query($sql_atuacao);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['addTipo'])) {
        $tipo_servico = $conn->real_escape_string($_POST['tipo_servico']);
        $sql_servico = "INSERT INTO tipos_servicos (tipo_servico) VALUES (?)";
        $stmt = $conn->prepare($sql_servico);
        $stmt->bind_param("s", $tipo_servico);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $id = $conn->real_escape_string($_POST['id']);

        // First delete dependent records in servico_tipo_servico
        $sql_delete_dependent = "DELETE FROM servico_tipo_servico WHERE tipo_servico_id = ?";
        $stmt = $conn->prepare($sql_delete_dependent);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Now delete the record from tipos_servicos
        $sql_servico = "DELETE FROM tipos_servicos WHERE id = ?";
        $stmt = $conn->prepare($sql_servico);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

$sql_servico = "SELECT * FROM tipos_servicos";
$result = $conn->query($sql_servico);



function processarCNAEs($empresa) {
    // Processa CNAE principal
    $cnae_principal = [
        'codigo' => $empresa['codigo_cnae'] ?? '',
        'descricao' => $empresa['descricao_cnae'] ?? ''
    ];

    // Processa CNAEs secundários
    $cnaes_secundarios = [];
    if (!empty($empresa['atividades_secundarias']) && !empty($empresa['descricoes_secundarias'])) {
        $codigos = explode(',', $empresa['atividades_secundarias']);
        $descricoes = explode('|||', $empresa['descricoes_secundarias']);
        
        foreach ($codigos as $index => $codigo) {
            if (isset($descricoes[$index])) {
                $cnaes_secundarios[] = [
                    'codigo' => trim($codigo),
                    'descricao' => trim($descricoes[$index])
                ];
            }
        }
    }

    return [
        'principal' => $cnae_principal,
        'secundarios' => $cnaes_secundarios
    ];
}

// Uso da função
$cnaes = processarCNAEs($empresa);

$sql = "SELECT * FROM socios ORDER BY nome";
$resultSocios = $conn->query($sql);
?>