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
                    <h2>Informações do Cliente</h2>
                    
                    <div id="pessoaJuridica" class="hidden">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="razaoSocial" class="required">Razão Social:</label>
                                <input type="text" name="razao_social" id="razaoSocial" placeholder="Digite a razão social" 
                                       value="<?php echo htmlspecialchars($cliente['razao_social']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="cnpj" class="required">CNPJ:</label>
                                <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" 
                                       value="<?php echo htmlspecialchars($cliente['cnpj']); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="atividade_principal" class="required">Atividade Principal (CNAE)</label>
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
                                <label for="nomeCliente" class="required">Nome do Cliente:</label>
                                <input type="text" id="nomeCliente" name="nomeCliente" placeholder="Digite o nome completo" 
                                       value="<?php echo htmlspecialchars($cliente['nome']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="cpf" class="required">CPF:</label>
                                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" 
                                       value="<?php echo htmlspecialchars($cliente['cpf']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Endereço -->
                <div class="form-section">
                    <h2>Endereço</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep" class="required">CEP:</label>
                            <div class="input-with-feedback">
                                <input type="text" id="cep" name="cep" required placeholder="00000-000" 
                                       value="<?php echo htmlspecialchars($cliente['cep']); ?>">
                                <small id="cep-feedback" class="form-text"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rua">Rua:</label>
                            <input type="text" id="rua" name="rua" readonly placeholder="Endereço" 
                                   value="<?php echo htmlspecialchars($cliente['rua']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="numero" class="required">Número:</label>
                            <input type="text" id="numero" name="numero" required placeholder="Número" 
                                   value="<?php echo htmlspecialchars($cliente['numero']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="complemento">Complemento:</label>
                            <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc." 
                                   value="<?php echo htmlspecialchars($cliente['complemento']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro">Bairro:</label>
                            <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro" 
                                   value="<?php echo htmlspecialchars($cliente['bairro']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="cidade">Cidade:</label>
                            <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade" 
                                   value="<?php echo htmlspecialchars($cliente['cidade']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <input type="text" id="estado" name="estado" readonly placeholder="Estado" 
                                   value="<?php echo htmlspecialchars($cliente['estado']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="coordenada">Coordenada:</label>
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
                    <h2>Contato</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="required">E-mail:</label>
                            <input type="email" id="email" name="email" required placeholder="seu@email.com" 
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>">
                        </div>

                        

                        <div class="form-group">
                            <label for="celular" class="required">Celular:</label>
                            <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000" 
                                   value="<?php echo htmlspecialchars($cliente['celular']); ?>">
                        </div>
                    </div>
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

    <script>
        // Mascaras de CPF, CNPJ e outros campos
        $('#cep').mask('00000-000');
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
        $('#telefone').mask('(00) 0000-0000');
        $('#celular').mask('(00) 00000-0000');

        $(document).ready(function() {
            function buscarCoordenadas(cep) {
                // Remove any non-numeric characters from CEP
                cep = cep.replace(/[^0-9]/g, '');
                
                if (cep.length === 8) {
                    // Show loading indicator in the coordinates field
                    $('#coordenada').val('Buscando coordenadas...');
                    
                    $.ajax({
                        url: `https://brasilapi.com.br/api/cep/v2/${cep}`,
                        method: 'GET',
                        success: function(response) {
                            if (response.location && response.location.coordinates) {
                                const latitude = response.location.coordinates[1];
                                const longitude = response.location.coordinates[0];
                                $('#coordenada').val(`${latitude}, ${longitude}`);
                            } else {
                                $('#coordenada').val('');
                            }
                        },
                        error: function() {
                            $('#coordenada').val('');
                            console.log('Erro ao buscar coordenadas');
                        }
                    });
                }
            }

            // Trigger coordinate search when CEP changes
            $('#cep').on('blur', function() {
                buscarCoordenadas($(this).val());
            });

            // Also trigger when CEP field loses focus
            $('#cep').on('change', function() {
                buscarCoordenadas($(this).val());
            });
        });

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