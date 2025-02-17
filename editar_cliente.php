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


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
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

        .form-section h2 i {
            margin-right: 10px;
            color: #007bff;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1; /* Cada grupo ocupa o mesmo espaço */
            margin-right: 15px; /* Espaçamento entre os grupos */
        }

        .form-group:last-child {
            margin-right: 0; /* Remove margem do último grupo */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label i {
            margin-right: 5px;
            color: #666;
            width: 16px;
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

        .form-actions {
            display: flex;
            justify-content: left;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            margin-top: -30px;
        }

        /* Feedback visual */
        small.form-text {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }

        .form-text i {
            margin-right: 5px;
            color: #666;
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

        .hidden {
            display: none !important;
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

        .form-group input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

                /* Estilos para o mapa */
                .leaflet-container {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: var(--shadow-md);
        }

        .leaflet-popup-content {
            margin: 13px 19px;
            line-height: 1.4;
        }

        .leaflet-control-zoom {
            border: none !important;
            box-shadow: var(--shadow-md) !important;
        }

        .leaflet-control-zoom a {
            background-color: white !important;
            color: var(--primary-color) !important;
        }

        .leaflet-control-zoom a:hover {
            background-color: #f8f9fa !important;
        }

        /* Adicione estes estilos CSS */
        .form-group label.required:after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

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
            <h1>Editar Cliente</h1>
            <form action="atualizar_cliente.php" method="POST" id="formEditarCliente">
                <!-- Campo oculto com ID -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" id="tipo_pessoa" name="tipo_pessoa" readonly value="<?php echo htmlspecialchars($cliente['tipo_pessoa']); ?>">

                <!-- Seção: Informações do Cliente -->
                <div class="form-section">
                    <h2><i class="fas fa-user-circle"></i> Informações do Cliente</h2>
                    
                    <div id="pessoaJuridica" class="hidden">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="razaoSocial" class="required">
                                    <i class="fas fa-building"></i> Razão Social:
                                </label>
                                <input type="text" name="razao_social" id="razaoSocial" placeholder="Digite a razão social" 
                                       value="<?php echo htmlspecialchars($cliente['razao_social']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="cnpj" class="required">
                                    <i class="fas fa-briefcase"></i> CNPJ:
                                </label>
                                <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" 
                                       value="<?php echo htmlspecialchars($cliente['cnpj']); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="atividade_principal" class="required">
                                    <i class="fas fa-industry"></i> Atividade Principal (CNAE)
                                </label>
                                <select name="atividade_principal" id="atividade_principal">
                                    <option value="">Selecione uma atividade</option>
                                    <?php
                                    if ($cnae_data) {
                                        foreach ($cnae_data as $cnae) {
                                            $codigo = htmlspecialchars($cnae['id']);
                                            $descricao = htmlspecialchars($cnae['descricao']);
                                            $selected = ($codigo == $cliente['codigo_cnae']) ? 'selected' : '';
                                            echo "<option value='$codigo' $selected>$codigo - $descricao</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="pessoaFisica" class="hidden">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nomeCliente" class="required">
                                    <i class="fas fa-user"></i> Nome do Cliente:
                                </label>
                                <input type="text" id="nomeCliente" name="nomeCliente" placeholder="Digite o nome completo" 
                                       value="<?php echo htmlspecialchars($cliente['nome']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="cpf" class="required">
                                    <i class="fas fa-id-card"></i> CPF:
                                </label>
                                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" 
                                       value="<?php echo htmlspecialchars($cliente['cpf']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Endereço -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marked-alt"></i> Endereço</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep" class="required">
                                <i class="fas fa-map-pin"></i> CEP:
                            </label>
                            <div class="input-with-feedback">
                                <input type="text" id="cep" name="cep" required placeholder="00000-000" 
                                       value="<?php echo htmlspecialchars($cliente['cep']); ?>">
                                <small id="cep-feedback" class="form-text"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rua">
                                <i class="fas fa-road"></i> Rua:
                            </label>
                            <input type="text" id="rua" name="rua" readonly placeholder="Endereço" 
                                   value="<?php echo htmlspecialchars($cliente['rua']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="numero" class="required">
                                <i class="fas fa-home"></i> Número:
                            </label>
                            <input type="text" id="numero" name="numero" required placeholder="Número" 
                                   value="<?php echo htmlspecialchars($cliente['numero']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="complemento">
                                <i class="fas fa-info-circle"></i> Complemento:
                            </label>
                            <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc." 
                                   value="<?php echo htmlspecialchars($cliente['complemento']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro">
                                <i class="fas fa-map"></i> Bairro:
                            </label>
                            <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro" 
                                   value="<?php echo htmlspecialchars($cliente['bairro']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="cidade">
                                <i class="fas fa-city"></i> Cidade:
                            </label>
                            <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade" 
                                   value="<?php echo htmlspecialchars($cliente['cidade']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="estado">
                                <i class="fas fa-map-marker-alt"></i> Estado:
                            </label>
                            <input type="text" id="estado" name="estado" readonly placeholder="Estado" 
                                   value="<?php echo htmlspecialchars($cliente['estado']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="coordenada">
                                <i class="fas fa-location-arrow"></i> Coordenada:
                            </label>
                            <div class="input-with-feedback">
                                <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude" 
                                       value="<?php echo htmlspecialchars($cliente['coordenada']); ?>">
                                <small id="coordenadas-feedback" class="form-text"></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Contato -->
                <div class="form-section">
                    <h2><i class="fas fa-address-book"></i> Contato</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="required">
                                <i class="fas fa-envelope"></i> E-mail:
                            </label>
                            <input type="email" id="email" name="email" required placeholder="seu@email.com" 
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="celular" class="required">
                                <i class="fas fa-mobile-alt"></i> Celular:
                            </label>
                            <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000" 
                                   value="<?php echo htmlspecialchars($cliente['celular']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Seção: Mapa -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marked"></i> Localização no Mapa</h2>
                    <div id="map" style="height: 400px; width: 100%; border-radius: 8px; margin-bottom: 15px;"></div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Clique no mapa para atualizar as coordenadas ou arraste o marcador
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Alterar
                    </button>
                    <a href="gerenciar_clientes.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Adicionar Leaflet CSS e JS antes do </body> -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

    <script>
        // Mascaras de CPF, CNPJ e outros campos
        $('#cep').mask('00000-000');
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
        $('#telefone').mask('(00) 0000-0000');
        $('#celular').mask('(00) 00000-0000');

        $(document).ready(function() {
            // Função para mostrar/ocultar campos baseado no tipo de pessoa
            function togglePessoaFields(tipoPessoa) {
                if (tipoPessoa === 'J') {
                    $('#pessoaJuridica').removeClass('hidden');
                    $('#pessoaFisica').addClass('hidden');
                    // Tornar campos de PJ obrigatórios
                    $('#razaoSocial, #cnpj').prop('required', true);
                    $('#nomeCliente, #cpf').prop('required', false);
                    // Limpar campos de PF
                    $('#nomeCliente, #cpf').val('');
                } else if (tipoPessoa === 'F') {
                    $('#pessoaFisica').removeClass('hidden');
                    $('#pessoaJuridica').addClass('hidden');
                    // Tornar campos de PF obrigatórios
                    $('#nomeCliente, #cpf').prop('required', true);
                    $('#razaoSocial, #cnpj').prop('required', false);
                    // Limpar campos de PJ
                    $('#razaoSocial, #cnpj').val('');
                    // Resetar select de atividade principal
                    $('#atividade_principal').val('');
                }
            }

            // Inicialização: verificar o tipo de pessoa atual e mostrar os campos apropriados
            var tipoPessoa = $('#tipo_pessoa').val();
            togglePessoaFields(tipoPessoa);

            // Remover classe 'hidden' do JavaScript e adicionar ao CSS
            $('<style>')
                .text('.hidden { display: none !important; }')
                .appendTo('head');
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
                
                const enderecoCompleto = `${endereco.logradouro}, ${endereco.localidade}, ${endereco.uf}, Brasil`;
                
                // Atualizar o mapa com o novo endereço
                updateMapFromAddress(endereco);
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
        
        // Adicionar código do mapa
        let map;
        let marker;

        function initMap() {
            // Coordenadas iniciais (usar as do cliente se existirem, ou centro do Brasil como padrão)
            let initialLat = -15.77972;
            let initialLng = -47.92972;
            
            // Pegar coordenadas salvas do cliente
            const coordField = document.getElementById('coordenada');
            const savedCoords = coordField.value;
            
            if (savedCoords) {
                const [lat, lng] = savedCoords.split(',').map(coord => parseFloat(coord.trim()));
                if (!isNaN(lat) && !isNaN(lng)) {
                    initialLat = lat;
                    initialLng = lng;
                }
            }

            // Inicializar o mapa
            map = L.map('map').setView([initialLat, initialLng], 15);

            // Adicionar camada do OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Adicionar marcador
            marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            // Atualizar coordenadas quando o marcador for arrastado
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // Atualizar coordenadas ao clicar no mapa
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });
        }

        function updateCoordinates(lat, lng) {
            const coordField = document.getElementById('coordenada');
            coordField.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }

        // Atualizar mapa quando o endereço mudar
        function updateMapFromAddress(endereco) {
            const enderecoCompleto = `${endereco.logradouro}, ${endereco.localidade}, ${endereco.uf}, Brasil`;
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(enderecoCompleto)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        
                        if (map && marker) {
                            marker.setLatLng([lat, lng]);
                            map.setView([lat, lng], 16);
                            updateCoordinates(lat, lng);
                        }
                    }
                })
                .catch(error => console.error('Erro ao buscar coordenadas:', error));
        }

        // Inicializar o mapa quando a página carregar
        initMap();

        // Atualizar o mapa quando a janela for redimensionada
        window.addEventListener('resize', function() {
            if (map) {
                map.invalidateSize();
            }
        });
    </script>
</body>