<?php
include 'conexao.php';

// Consulta SQL para buscar os clientes
$sql = "SELECT 
            id,
            tipo_pessoa,
            CASE 
                WHEN tipo_pessoa = 'F' THEN nome
                WHEN tipo_pessoa = 'J' THEN razao_social
                ELSE 'Não especificado'
            END AS cliente_nome_ou_razao,
            cnpj,
            cpf,
            cep,
            rua,
            numero,
            complemento,
            bairro,
            cidade,
            estado,
            telefone,
            celular,
            email,
            codigo_cnae,
            data_cadastro
        FROM cliente";

$result = $conn->query($sql);

// Armazena os dados dos clientes em um array
if ($result->num_rows > 0) {
    $clientes = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $clientes = []; // Array vazio caso não haja clientes
}

if (isset($_GET['mensagem'])) {
    echo "<p style='color: green; font-weight: bold; text-align: center;'>" . htmlspecialchars($_GET['mensagem']) . "</p>";
}
?>