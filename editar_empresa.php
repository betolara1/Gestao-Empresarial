<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';

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
    <title>Editar Empresa</title>    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <style>
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
            margin-bottom: 30px;
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
        
        input[type="text"],
        input[type="email"],
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

        .form-actions {
            display: flex;
            justify-content: left;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            margin-top: -30px;
        }
        
        .btn {
            padding: 8px 16px; /* Ajuste o padding para combinar com o estilo */
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff; /* Cor do botão primário */
            color: white;
        }

        .btn-danger {
            background: #dc3545; /* Cor do botão de perigo */
            color: white;
        }

        .btn:hover {
            opacity: 0.9; /* Efeito de hover */
        }

        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border-color);
            margin-bottom: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h1>Editar Empresa</h1>
            
            <form id="cadastroForm" action="atualizar_empresa.php" method="POST">
                <!-- Seção: Informações Principais -->
                <div class="form-section">
                    <h2>Informações Principais</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="razao_social" class="required">Razão Social</label>
                            <input type="text" id="razaoSocial" name="razaoSocial" 
                                value="<?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cnpj" class="required">CNPJ</label>
                            <input type="text" id="cnpj" name="cnpj" 
                                value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome Fantasia</label>
                            <input type="text" id="nome" name="nome" 
                                value="<?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" 
                                value="<?php echo htmlspecialchars($empresa['cpf'] ?? ''); ?>">
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
                                <input type="text" id="cep" name="cep" required 
                                    value="<?php echo htmlspecialchars($empresa['cep'] ?? ''); ?>">
                                <small id="cep-feedback" class="form-text"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rua">Rua</label>
                            <input type="text" id="rua" name="rua" readonly 
                                value="<?php echo htmlspecialchars($empresa['rua'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="numero" class="required">Número</label>
                            <input type="text" id="numero" name="numero" required 
                                value="<?php echo htmlspecialchars($empresa['numero'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" 
                                value="<?php echo htmlspecialchars($empresa['complemento'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro">Bairro</label>
                            <input type="text" id="bairro" name="bairro" readonly 
                                value="<?php echo htmlspecialchars($empresa['bairro'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" readonly 
                                value="<?php echo htmlspecialchars($empresa['cidade'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" readonly 
                                value="<?php echo htmlspecialchars($empresa['estado'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="coordenada">Coordenadas</label>
                            <div class="input-with-map">
                                <input type="text" id="coordenada" name="coordenada" readonly 
                                    value="<?php echo htmlspecialchars($empresa['coordenada'] ?? ''); ?>">
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
                            <input type="email" id="email" name="email" required 
                                value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="celular" class="required">Celular</label>
                            <input type="text" id="celular" name="celular" required 
                                value="<?php echo htmlspecialchars($empresa['celular'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="telefone" name="telefone" 
                                value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>">
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
                                    <option value="<?= $cnae['id'] ?>" 
                                            data-descricao="<?= htmlspecialchars($cnae['descricao']) ?>"
                                            <?= ($cnae['id'] == $empresa['codigo_cnae']) ? 'selected' : '' ?>>
                                        <?= $cnae['id'] ?> - <?= $cnae['descricao'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <input type="hidden" name="descricao_cnae" id="descricao_cnae" 
                                value="<?php echo htmlspecialchars($empresa['descricao_cnae'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Atividades Secundárias</h2>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="atividades_secundarias">Atividades Secundárias (CNAE)</label>
                            <div class="cnae-selector">
                                <select name="cnae_select" id="cnae_select">
                                    <option value="">Selecione um CNAE</option>
                                    <?php foreach ($cnae_data as $cnae) { ?>
                                        <option value="<?= $cnae['id'] ?>" 
                                                data-descricao="<?= htmlspecialchars($cnae['descricao']) ?>"
                                                <?= in_array($cnae['id'], $atividades_secundarias_selecionadas) ? 'disabled' : '' ?>>
                                            <?= $cnae['id'] ?> - <?= $cnae['descricao'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <button type="button" id="adicionar_cnae" class="btn btn-primary"><label></label>
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            </div>
                            <br><br>
                            <div id="cnaes_selecionados" class="mt-3">
                                <h6>CNAEs Selecionados:</h6>
                                <ul class="list-group" id="lista_cnaes">
                                    <?php
                                    if (!empty($cnaes_secundarios)) {
                                        foreach ($cnaes_secundarios as $index => $cnae) {
                                            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                                            echo "{$cnae['id']} - {$cnae['descricao']}";
                                            echo "<button type='button' class='btn btn-danger btn-sm remover-cnae'><i class='fas fa-trash'></i> Remover</button>";
                                            echo "<input type='hidden' name='atividades_secundarias[]' value='{$cnae['id']}'>";
                                            echo "<input type='hidden' name='descricoes_secundarias[]' value='" . htmlspecialchars($descricoes_secundarias[$index]) . "'>";
                                            echo "</li>";
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
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
    </script>
</body>
</html>