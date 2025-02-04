<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #838282;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --sidebar-width: 250px;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --primary-dark: #1e40af;
            --background-color: #ffffff;
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 2rem auto;
        }
        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        h2 {
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background-color: var(--accent-color);
            border-radius: 2px;
        }

        .form-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eef2f7;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-shadow: var(--shadow-md);
            white-space: nowrap; /* Impede quebra de linha */
            overflow: hidden; /* Oculta texto que excede a largura */
            text-overflow: ellipsis; /* Adiciona reticências para texto que não cabe */
        }
        .form-group {
            flex: 1; /* Cada grupo ocupa o mesmo espaço */
            margin-right: 15px; /* Espaçamento entre os grupos */
        }

        .form-group:last-child {
            margin-right: 0; /* Remove margem do último grupo */
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label.required:after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dce0e4;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
            outline: none;
        }

        .form-group input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        /* Botões */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            margin-top: -30px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        /* Feedback visual */
        .input-with-feedback {
            position: relative;
        }

        .form-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 4px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Estilo para campos inválidos */
        .form-group input:invalid,
        .form-group select:invalid {
            border-color: #e74c3c;
        }

        /* Tooltip para campos obrigatórios */
        .required-tooltip {
            position: relative;
        }

        .required-tooltip:hover:after {
            content: "Campo obrigatório";
            position: absolute;
            background: #34495e;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            bottom: 100%;
            left: 0;
            white-space: nowrap;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h1>Cadastro de Cliente</h1>
            <form id="cadastroForm" action="salvar_cliente.php" method="POST">
                
                <!-- Seção: Informações Principais -->
                <div class="form-section">
                    <h2>Informações Principais</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipoPessoa" class="required"><i class="fas fa-user-tag"></i> Tipo de Pessoa</label>
                            <select id="tipoPessoa" name="tipoPessoa" required>
                                <option value="">Selecione o tipo de pessoa</option>
                                <option value="F">Física</option>
                                <option value="J">Jurídica</option>
                            </select>
                        </div>
                    </div>

                    <div id="pessoaJuridica" class="hidden">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="razaoSocial" class="required"><i class="fas fa-building"></i> Razão Social</label>
                                <input type="text" id="razaoSocial" name="razaoSocial" placeholder="Digite a razão social">
                            </div>
                            
                            <div class="form-group">
                                <label for="cnpj" class="required"><i class="fas fa-id-card"></i> CNPJ</label>
                                <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00">
                            </div>
                        </div>
                    </div>

                    <div id="pessoaFisica" class="hidden">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nomeCliente" class="required"><i class="fas fa-user"></i> Nome do Cliente</label>
                                <input type="text" id="nomeCliente" name="nomeCliente" placeholder="Digite o nome completo">
                            </div>

                            <div class="form-group">
                                <label for="cpf" class="required"><i class="fas fa-id-card"></i> CPF</label>
                                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Endereço -->
                <div class="form-section">
                    <h2>Endereço</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep" class="required"><i class="fas fa-map-marker-alt"></i> CEP</label>
                            <div class="input-with-feedback">
                                <input type="text" id="cep" name="cep" required placeholder="00000-000">
                                <small id="cep-feedback" class="form-text"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rua"><i class="fas fa-road"></i> Rua</label>
                            <input type="text" id="rua" name="rua" readonly placeholder="Endereço">
                        </div>

                        <div class="form-group">
                            <label for="numero" class="required"><i class="fas fa-home"></i> Número</label>
                            <input type="text" id="numero" name="numero" required placeholder="Número">
                        </div>

                        <div class="form-group">
                            <label for="complemento"><i class="fas fa-info"></i> Complemento</label>
                            <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc.">
                        </div>
                    </div>

                    <div class="form-row">
                        

                        <div class="form-group">
                            <label for="bairro"><i class="fas fa-map"></i> Bairro</label>
                            <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro">
                        </div>

                        <div class="form-group">
                            <label for="cidade"><i class="fas fa-city"></i> Cidade</label>
                            <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade">
                        </div>

                        <div class="form-group">
                            <label for="estado"><i class="fas fa-flag"></i> Estado</label>
                            <input type="text" id="estado" name="estado" readonly placeholder="Estado">
                        </div>

                        <div class="form-group">
                            <label for="coordenada"><i class="fas fa-map-pin"></i> Coordenadas</label>
                            <div class="input-with-map">
                                <input type="text" id="coordenada" name="coordenada" readonly placeholder="Latitude, Longitude">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Contato -->
                <div class="form-section">
                    <h2>Contato</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="required"><i class="fas fa-envelope"></i> E-mail</label>
                            <input type="email" id="email" name="email" required placeholder="seu@email.com">
                        </div>

                        <div class="form-group">
                            <label for="celular" class="required"><i class="fas fa-mobile-alt"></i> Celular</label>
                            <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000">
                        </div>
                    </div>
                </div>

                <!-- Seção: Atividades (apenas para Pessoa Jurídica) -->
                <div class="form-section" id="atividadePrincipalRow">
                    <h2>Atividades</h2>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="atividade_principal"><i class="fas fa-briefcase"></i> Atividade Principal (CNAE)</label>
                            <select name="atividade_principal" id="atividade_principal" onchange="atualizarDescricaoCNAE(this)">
                                <option value="">Selecione uma atividade principal</option>
                                <?php foreach ($cnae_data as $cnae) { ?>
                                    <option value="<?= $cnae['id'] ?>" data-descricao="<?= htmlspecialchars($cnae['descricao']) ?>">
                                        <?= $cnae['id'] ?> - <?= $cnae['descricao'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <input type="hidden" name="descricao_cnae" id="descricao_cnae">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save"></i> Cadastrar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Mascaras de CPF, CNPJ e outros campos
        $('#cep').mask('00000-000');
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
        $('#celular').mask('(00) 00000-0000');

        $(document).ready(function() {
            // Adicionar classe CSS para elementos ocultos
            $('<style>')
                .text('.hidden { display: none !important; }')
                .appendTo('head');

            // Função para controlar a visibilidade dos campos
            function toggleCamposPessoa(tipo) {
                if (tipo === 'J') {
                    // Pessoa Jurídica
                    $('#pessoaJuridica').removeClass('hidden');
                    $('#pessoaFisica').addClass('hidden');
                    $('#atividadePrincipalRow').removeClass('hidden');
                    
                    // Ajusta campos obrigatórios
                    $('#razaoSocial, #cnpj').prop('required', true);
                    $('#nomeCliente, #cpf').prop('required', false).val('');
                    
                } else if (tipo === 'F') {
                    // Pessoa Física
                    $('#pessoaFisica').removeClass('hidden');
                    $('#pessoaJuridica').addClass('hidden');
                    $('#atividadePrincipalRow').addClass('hidden');
                    
                    // Ajusta campos obrigatórios
                    $('#nomeCliente, #cpf').prop('required', true);
                    $('#razaoSocial, #cnpj').prop('required', false).val('');
                    
                } else {
                    // Nenhum tipo selecionado
                    $('#pessoaJuridica, #pessoaFisica, #atividadePrincipalRow').addClass('hidden');
                    $('#razaoSocial, #cnpj, #nomeCliente, #cpf').prop('required', false).val('');
                }
            }

            // Controle no carregamento da página
            toggleCamposPessoa($('#tipoPessoa').val());

            // Controle na mudança do select
            $('#tipoPessoa').on('change', function() {
                toggleCamposPessoa($(this).val());
            });
        });

        // Adicionar função de pesquisa ao campo de seleção de CNAE
        createSearchableSelect(document.getElementById('atividade_principal'), "Buscar Atividade");

        // Função para criar o campo de busca no select
        function createSearchableSelect(selectElement, placeholder) {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = placeholder;
            searchInput.className = 'form-control mb-2';
            
            selectElement.parentNode.insertBefore(searchInput, selectElement);
            
            const options = Array.from(selectElement.options);
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                selectElement.innerHTML = '';
                selectElement.add(new Option('', ''));

                options.forEach(option => {
                    if (option.value === '') return;
                    if (option.text.toLowerCase().includes(searchTerm)) {
                        const newOption = new Option(option.text, option.value);
                        selectElement.add(newOption);
                    }
                });
            });
        }

        function atualizarDescricaoCNAE(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const descricaoInput = document.getElementById('descricao_cnae');
            if (selectedOption.value) {
                descricaoInput.value = selectedOption.getAttribute('data-descricao');
            } else {
                descricaoInput.value = '';
            }
        }

        $(document).ready(function() {
            function limpaFormularioCep() {
                // Limpa valores do formulário de cep.
                $("#rua").val("");
                $("#bairro").val("");
                $("#cidade").val("");
                $("#estado").val("");
                $("#coordenada").val("");
            }

            function preencheCamposEndereco(dados) {
                $("#rua").val(dados.street || dados.logradouro);
                $("#bairro").val(dados.neighborhood || dados.bairro);
                $("#cidade").val(dados.city || dados.cidade || dados.localidade);
                $("#estado").val(dados.state || dados.uf);
            }

            function buscarCoordenadas(endereco) {
                $('#coordenada').val("Buscando coordenadas...");
                
                // Monta o endereço completo para busca
                const enderecoCompleto = `${endereco.logradouro}, ${endereco.localidade}, ${endereco.uf}, Brasil`;
                
                // Usa a API do OpenStreetMap (Nominatim)
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    type: 'GET',
                    data: {
                        q: enderecoCompleto,
                        format: 'json',
                        limit: 1
                    },
                    headers: {
                        'Accept-Language': 'pt-BR'
                    },
                    success: function(response) {
                        if (response && response.length > 0) {
                            const lat = response[0].lat;
                            const lon = response[0].lon;
                            $('#coordenada').val(`${lat}, ${lon}`);
                        } else {
                            $('#coordenada').val('');
                            console.log('Coordenadas não encontradas');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#coordenada').val('');
                        console.log('Erro ao buscar coordenadas:', error);
                    }
                });
            }

            //Quando o campo cep perde o foco.
            $("#cep").on('blur change', function() {
                // Nova variável "cep" somente com dígitos.
                var cep = $(this).val().replace(/\D/g, '');

                //Verifica se campo cep possui valor informado.
                if (cep.length === 8) {
                    //Expressão regular para validar o CEP.
                    var validacep = /^[0-9]{8}$/;

                    //Valida o formato do CEP.
                    if(validacep.test(cep)) {
                        //Preenche os campos com "..." enquanto consulta webservice.
                        $("#rua").val("...");
                        $("#bairro").val("...");
                        $("#cidade").val("...");
                        $("#estado").val("...");
                        $("#coordenada").val("Buscando coordenadas...");

                        //Consulta o webservice viacep.com.br/
                        $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(dados) {
                            if (!("erro" in dados)) {
                                preencheCamposEndereco(dados);
                                // Busca coordenadas após preencher o endereço
                                buscarCoordenadas(dados);
                            } else {
                                //CEP pesquisado não foi encontrado.
                                limpaFormularioCep();
                                alert("CEP não encontrado.");
                            }
                        }).fail(function() {
                            limpaFormularioCep();
                            alert("Erro ao buscar CEP. Tente novamente mais tarde.");
                        });
                    } else {
                        //cep é inválido.
                        limpaFormularioCep();
                        alert("Formato de CEP inválido.");
                    }
                } else {
                    //cep sem valor, limpa formulário.
                    limpaFormularioCep();
                }
            });
        });
    </script>
</body>
</html>