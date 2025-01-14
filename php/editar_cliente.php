<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';

// Verifica se o ID do cliente foi passado pela URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta SQL para buscar os detalhes do cliente pelo ID
    $sql = "SELECT 
                tipo_pessoa,
                razao_social,
                cnpj,
                nome,
                cpf,
                cep,
                rua,
                numero,
                complemento,
                bairro,
                cidade,
                estado,
                celular,
                email,
                coordenada,
                codigo_cnae,
                data_cadastro
            FROM cliente
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o cliente foi encontrado
    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
    } else {
        die("Cliente não encontrado.");
    }
} else {
    die("ID do cliente não informado.");
}

// Buscar áreas de atuação
$query_areas = "SELECT id, nome FROM areas_atuacao ORDER BY nome";
$result_areas = $conn->query($query_areas);

function getCNAE() {
    $url = "https://servicodados.ibge.gov.br/api/v2/cnae/classes";
    
    // Inicializa o CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Executa a requisição
    $response = curl_exec($ch);
    
    // Verifica se houve erro
    if(curl_errno($ch)) {
        echo 'Erro ao buscar CNAE: ' . curl_error($ch);
        return false;
    }
    
    curl_close($ch);
    
    // Converte o JSON para array
    return json_decode($response, true);
}

// Busca os dados
$cnae_data = getCNAE();
?>