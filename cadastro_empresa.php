<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';

// Busca os dados cadastrados na tabela `minhaempresa`
$sql_empresa = "SELECT * FROM empresa LIMIT 1"; // Supondo que há apenas uma linha ou você deseja exibir apenas a primeira
$result_empresa = $conn->query($sql_empresa);

// Verifica se encontrou registros
if ($result_empresa->num_rows > 0) {
    $empresa = $result_empresa->fetch_assoc(); // Pega o primeiro registro
} else {
    $empresa = 0; // Caso não encontre dados, inicializa como vazio
}

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


if ($empresa === 0) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastrar Empresa</title>    
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
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

            /* Estilos para o formulário */
            .form-section {
                margin-bottom: 2rem;
            }

            .form-row {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 15px;
            }

            .form-group {
                flex: 1;
                min-width: 250px; /* Largura mínima para as colunas */
                margin-right: 15px;
            }
            
            .form-group:last-child {
                margin-right: 0; /* Remove margem do último item */
            }

            .required {
                color: var(--error-color);
            }

            .input-with-feedback {
                position: relative;
            }

            .input-with-feedback .form-text {
                position: absolute;
                bottom: -20px;
                left: 0;
                color: var(--error-color);
                font-size: 0.8rem;
            }

            .input-with-map {
                display: flex;
                align-items: center;
            }

            .input-with-map input {
                flex: 1;
            }

            .btn {
                background-color: var(--primary-color);
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .btn:hover {
                background-color: var(--primary-dark);
            }

            .btn-secondary {
                background-color: var(--accent-color);
            }

            .btn-secondary:hover {
                background-color: darken(var(--accent-color), 10%);
            }

            .list-group {
                list-style: none;
                padding: 0;
            }

            .list-group-item {
                padding: 10px;
                border: 1px solid var(--border-color);
                border-radius: 5px;
                margin-bottom: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
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

            .checkbox-group {
                display: flex;
                flex-wrap: wrap; /* Permite que os checkboxes quebrem para a próxima linha */
                gap: 15px; /* Espaçamento entre os checkboxes */
                justify-content: center; /* Centraliza os checkboxes horizontalmente */
            }

            .form-check {
                display: flex;
                align-items: center;
                padding: 10px;
                border: 1px solid var(--border-color);
                border-radius: 5px;
                background-color: white; /* Cor de fundo */
                transition: background-color 0.3s, border-color 0.3s; /* Transições suaves */
            }

            .form-check:hover {
                background-color: #f0f0f0; /* Cor de fundo ao passar o mouse */
                border-color: var(--primary-color); /* Cor da borda ao passar o mouse */
            }

            .form-check-input {
                margin-right: 10px; /* Espaçamento entre o checkbox e o texto */
                cursor: pointer; /* Cursor de ponteiro */
            }

            .form-check-label {
                cursor: pointer; /* Cursor de ponteiro */
            }
        </style>
    </head>

    <body>
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <h1>Cadastrar Empresa</h1>
                
                <form id="cadastroForm" action="salvar_empresa.php" method="POST" enctype="multipart/form-data">
                    <!-- Seção: Informações Principais -->
                    <div class="form-section">
                        <h2>Informações Principais</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="razao_social" class="required">Razão Social</label>
                                <input type="text" id="razaoSocial" name="razaoSocial" placeholder="Digite a razão social" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cnpj" class="required">CNPJ</label>
                                <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome Fantasia</label>
                                <input type="text" id="nome" name="nome" placeholder="Digite o nome fantasia">
                            </div>

                            <div class="form-group">
                                <label for="cpf">CPF</label>
                                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00">
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Endereço -->
                    <div class="form-section">
                        <h2>Endereço</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cep" class="required">CEP</label>
                                <div class="input-with-feedback">
                                    <input type="text" id="cep" name="cep" required placeholder="00000-000">
                                    <small id="cep-feedback" class="form-text"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="rua">Rua</label>
                                <input type="text" id="rua" name="rua" readonly placeholder="Endereço">
                            </div>

                            <div class="form-group">
                                <label for="numero" class="required">Número</label>
                                <input type="text" id="numero" name="numero" required placeholder="Número">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="complemento">Complemento</label>
                                <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc.">
                            </div>

                            <div class="form-group">
                                <label for="bairro">Bairro</label>
                                <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro">
                            </div>

                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade">
                            </div>

                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <input type="text" id="estado" name="estado" readonly>
                            </div>

                            <div class="form-group">
                                <label for="coordenada">Coordenadas</label>
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
                                <label for="email" class="required">E-mail</label>
                                <input type="email" id="email" name="email" required placeholder="seu@email.com">
                            </div>

                            <div class="form-group">
                                <label for="celular" class="required">Celular</label>
                                <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000">
                            </div>
                            <div class="form-group">
                                <input type="hidden" id="telefone" name="telefone" placeholder="(00) 0000-0000">
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Atividades -->
                    <div class="form-section">
                        <h2>Atividades</h2>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="atividade_principal" class="required">Atividade Principal (CNAE)</label>
                                <select name="atividade_principal" id="atividade_principal" required onchange="atualizarDescricaoCNAE(this)">
                                    <option value="">Selecione uma atividade principal</option>
                                    <?php foreach ($cnae_data as $cnae) { ?>
                                        <option value="<?= $cnae['id'] ?>" data-descricao="<?= htmlspecialchars($cnae['descricao']) ?>">
                                            <?= $cnae['id'] ?> - <?= $cnae['descricao'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <input type="hidden" name="descricao_cnae" id="descricao_cnae" value="">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2>Atividades Secundárias</h2>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="atividades_secundarias">Atividades Secundárias (CNAE)</label>
                                <select name="cnae_select" id="cnae_select">
                                    <option value="">Selecione um CNAE</option>
                                    <?php
                                    foreach ($cnae_data as $cnae) {
                                        $codigo = $cnae['id'];
                                        $descricao = $cnae['descricao'];
                                        echo "<option value=\"$codigo\" data-descricao=\"$descricao\">$codigo - $descricao</option>";
                                    }
                                    ?>
                                </select>
                                <button type="button" id="adicionar_cnae" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                               
                                <label></label> <label></label> <label></label> <label></label>
                                <div id="cnaes_selecionados" class="mt-3">
                                    <h6>CNAEs Selecionados:</h6>
                                    <ul class="list-group" id="lista_cnaes">
                                        <?php
                                        // Exibe CNAEs já selecionados anteriormente
                                        if (!empty($atividades_secundarias_selecionadas)) {
                                            foreach ($atividades_secundarias_selecionadas as $cnae_id) {
                                                $cnae = array_filter($cnae_data, function($item) use ($cnae_id) {
                                                    return $item['id'] == $cnae_id;
                                                });
                                                $cnae = reset($cnae);
                                                if ($cnae) {
                                                    echo "<li class='list-group-item d-flex justify-content-between align-items-center' data-id='{$cnae['id']}'>";
                                                    echo "{$cnae['id']} - {$cnae['descricao']} ";
                                                    echo '<button type="button" class="btn btn-danger btn-sm remover-cnae"><i class="fas fa-trash"></i> Remover</button>';
                                                    echo "<input type='hidden' name='atividades_secundarias[]' value='{$cnae['id']}'>";
                                                    echo "</li>";
                                                }
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-save"></i> Salvar Empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <script>
            $('#cep').mask('00000-000');
            $('#cpf').mask('000.000.000-00');
            $('#cnpj').mask('00.000.000/0000-00');
            $('#celular').mask('(00) 00000-0000');

            let map;
            let marker;

            // Funções do Mapa
            function initMap() {
                const defaultLocation = { lat: -14.235004, lng: -51.92528 };
                map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 4,
                    center: defaultLocation,
                });
            }

            function atualizarMapa(latitude, longitude) {
                const position = { lat: parseFloat(latitude), lng: parseFloat(longitude) };
                map.setCenter(position);
                map.setZoom(15);

                if (marker) {
                    marker.setMap(null);
                }

                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: 'Localização'
                });
            }

            // Eventos jQuery para CEP e coordenadas
            $(document).ready(function() {
                function buscarCoordenadas(cep) {
                    cep = cep.replace(/[^0-9]/g, '');
                    
                    if (cep.length === 8) {
                        $('#coordenada').val('Buscando coordenadas...');
                        
                        $.ajax({
                            url: `https://brasilapi.com.br/api/cep/v2/${cep}`,
                            method: 'GET',
                            success: function(response) {
                                if (response.location && response.location.coordinates) {
                                    const latitude = response.location.coordinates[1];
                                    const longitude = response.location.coordinates[0];
                                    $('#coordenada').val(`${latitude}, ${longitude}`);
                                    
                                    atualizarMapa(latitude, longitude);
                                    
                                    $.ajax({
                                        url: 'atualizar_coordenadas.php',
                                        method: 'POST',
                                        data: {
                                            cep: cep,
                                            coordenada: `${latitude}, ${longitude}`
                                        },
                                        success: function(response) {
                                            console.log('Coordenadas salvas com sucesso');
                                        },
                                        error: function() {
                                            console.log('Erro ao salvar coordenadas');
                                        }
                                    });
                                } else {
                                    $('#coordenada').val('Coordenadas não encontradas');
                                }
                            },
                            error: function() {
                                $('#coordenada').val('Erro ao buscar coordenadas');
                            }
                        });
                    }
                }

                $('#cep').on('blur change', function() {
                    buscarCoordenadas($(this).val());
                });
            });

            // Inicialização do mapa
            window.initMap = initMap; 

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

            document.addEventListener('DOMContentLoaded', function() {
                // Código do campo de busca
                function createSearchableSelect(selectElement, placeholder) {
                    const searchContainer = document.createElement('div');
                    searchContainer.className = 'search-select-container';
                    
                    // Cria campo de busca
                    const searchInput = document.createElement('input');
                    searchInput.type = 'text';
                    searchInput.placeholder = placeholder;
                    searchInput.className = 'form-control search-input';
                    
                    // Insere o container antes do select
                    selectElement.parentNode.insertBefore(searchContainer, selectElement);
                    searchContainer.appendChild(searchInput);
                    searchContainer.appendChild(selectElement);
                    
                    // Array com todas as opções originais
                    const options = Array.from(selectElement.options);
                    
                    // Função de busca
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        
                        // Remove todas as opções atuais
                        selectElement.innerHTML = '';
                        
                        // Adiciona opção padrão
                        const defaultOption = selectElement.id === 'atividade_principal' 
                            ? 'Selecione uma atividade principal'
                            : 'Selecione um CNAE';
                        selectElement.add(new Option(defaultOption, ''));
                        
                        // Filtra e adiciona opções que correspondem à busca
                        options.forEach(option => {
                            if (option.value === '') return; // Pula a opção padrão
                            
                            if (option.text.toLowerCase().includes(searchTerm)) {
                                const newOption = new Option(option.text, option.value);
                                // Copia os atributos data-* da opção original
                                if (option.dataset.descricao) {
                                    newOption.dataset.descricao = option.dataset.descricao;
                                }
                                // Mantém o estado selecionado/desabilitado
                                newOption.selected = option.selected;
                                newOption.disabled = option.disabled;
                                selectElement.add(newOption);
                            }
                        });
                    });
                }

                // Configura busca para atividade principal e secundária
                const atividadePrincipal = document.getElementById('atividade_principal');
                const atividadesSecundarias = document.getElementById('cnae_select');
                
                if (atividadePrincipal) {
                    createSearchableSelect(atividadePrincipal, 'Buscar CNAE Principal...');
                }
                if (atividadesSecundarias) {
                    createSearchableSelect(atividadesSecundarias, 'Buscar CNAE Secundário...');
                }

                // Código para adicionar e remover CNAEs secundários
                const btnAdicionar = document.getElementById('adicionar_cnae');
                const listaCnaes = document.getElementById('lista_cnaes');
                let selectedValue = '';
                let selectedText = '';

                // Captura o valor quando o select muda
                if (atividadesSecundarias) {
                    atividadesSecundarias.addEventListener('change', function() {
                        selectedValue = this.value;
                        selectedText = this.options[this.selectedIndex].text;
                        console.log('CNAE selecionado:', selectedValue, selectedText); // Debug
                    });
                }

                // Função para adicionar CNAE secundário
                if (btnAdicionar && atividadesSecundarias) {
                    btnAdicionar.addEventListener('click', function() {
                        console.log('Clique no botão adicionar'); // Debug
                        
                        if (!selectedValue) {
                            alert('Por favor, selecione um CNAE');
                            return;
                        }

                        // Verifica se o CNAE já foi adicionado
                        const jaExiste = document.querySelector(`input[name="atividades_secundarias[]"][value="${selectedValue}"]`);
                        if (jaExiste) {
                            alert('Este CNAE já foi adicionado');
                            return;
                        }

                        // Pega a descrição do CNAE selecionado
                        const descricaoCnae = selectedText.split(' - ')[1];

                        // Cria novo item na lista
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        
                        // Adiciona o texto do CNAE e campos hidden para código e descrição
                        li.innerHTML = `
                            ${selectedText}
                            <button type="button" class="btn btn-danger btn-sm remover-cnae">
                                <i class="fas fa-trash"></i> Remover
                            </button>
                            <input type="hidden" name="atividades_secundarias[]" value="${selectedValue}">
                            <input type="hidden" name="descricoes_secundarias[]" value="${descricaoCnae}">
                        `;

                        // Adiciona à lista
                        listaCnaes.appendChild(li);
                        
                        // Desabilita a opção no select
                        const option = atividadesSecundarias.querySelector(`option[value="${selectedValue}"]`);
                        if (option) {
                            option.disabled = true;
                        }
                        
                        // Limpa as seleções
                        atividadesSecundarias.value = '';
                        selectedValue = '';
                        selectedText = '';
                    });
                }

                // Função para remover CNAE secundário
                if (listaCnaes) {
                    listaCnaes.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remover-cnae') || e.target.closest('.remover-cnae')) {
                            const li = e.target.closest('li');
                            const cnaeValue = li.querySelector('input[name="atividades_secundarias[]"]').value;
                            
                            // Habilita novamente a opção no select
                            const option = atividadesSecundarias.querySelector(`option[value="${cnaeValue}"]`);
                            if (option) {
                                option.disabled = false;
                            }
                            
                            // Remove o item da lista
                            li.remove();
                        }
                    });
                }
            });

            $(document).ready(function() {
                $('#atividade_principal').change(function() {
                    const selectedOption = $(this).find('option:selected');
                    const descricao = selectedOption.data('descricao');
                    $('#descricao_cnae').val(descricao); // Define a descrição no campo oculto
                });
            });
        </script>
    </body>
    </html>
<?php
}else {
    // Se houver registros, redireciona para a página de edição
    header("Location: gerenciar_empresa.php");
    exit();
}
?>
