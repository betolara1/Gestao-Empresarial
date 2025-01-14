<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';

// Buscar áreas de atuação
$query_areas = "SELECT id, nome FROM areas_atuacao ORDER BY nome";
$result_areas = $conn->query($query_areas);

// Busca os dados cadastrados na tabela `empresa`
$sql_empresa = "SELECT * FROM empresa LIMIT 1";
$result_empresa = $conn->query($sql_empresa);

// Verifica se encontrou registros
if ($result_empresa->num_rows > 0) {
    $empresa = $result_empresa->fetch_assoc();
    
    // Converte as strings em arrays
    $atividades_secundarias = !empty($empresa['atividades_secundarias']) 
        ? explode(',', $empresa['atividades_secundarias']) 
        : [];
    
    $descricoes_secundarias = !empty($empresa['descricoes_secundarias']) 
        ? explode('|||', $empresa['descricoes_secundarias']) 
        : [];
    
    // Combina os códigos com suas descrições
    $cnaes_secundarios = [];
    foreach ($atividades_secundarias as $index => $codigo) {
        $descricao = isset($descricoes_secundarias[$index]) ? $descricoes_secundarias[$index] : '';
        if (!empty($codigo)) {
            $cnaes_secundarios[] = [
                'id' => trim($codigo),
                'descricao' => trim($descricao)
            ];
        }
    }
} else {
    $empresa = [
        'atividades_secundarias' => '',
        'descricoes_secundarias' => ''
    ];
    $cnaes_secundarios = [];
}

// Prepara o array de códigos selecionados para o disabled no select
$atividades_secundarias_selecionadas = array_column($cnaes_secundarios, 'id');



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

function getCNAEDescricao($codigo) {
    $url = "https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/" . $codigo;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        return false;
    }
    
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (!empty($data)) {
        return $data[0]['id'] . ' - ' . $data[0]['descricao'];
    }
    
    return false;
}

// Para atividades secundárias que podem ter múltiplos códigos
function getMultiplosCNAE($codigos) {
    if (empty($codigos)) return '';
    
    // Se os códigos estiverem em formato de string, converte para array
    if (is_string($codigos)) {
        $codigos = explode(',', $codigos);
    }

    $descricoes = [];
    foreach ($codigos as $codigo) {
        // Assegure-se de que $codigo é uma string antes de usar trim
        $codigo = is_array($codigo) ? implode(',', $codigo) : (string)$codigo; // Converte para string, se necessário
        $codigo = trim($codigo);
        $descricao = getCNAEDescricao($codigo);
        if ($descricao) {
            $descricoes[] = $descricao;
        }
    }
    
    return implode('; ', $descricoes);
}

// Processa os dados antes de exibir
$cnae_principal = '';
if (!empty($empresa['codigo_cnae'])) {
    $cnae_principal = getCNAEDescricao($empresa['codigo_cnae']);
}

$cnae_secundarios = '';
if (!empty($empresa['atividades_secundarias'])) {
    $cnae_secundarios = getMultiplosCNAE($empresa['atividades_secundarias']);
}


?>