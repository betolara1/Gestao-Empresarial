<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuração da conexão com o banco de dados
include 'conexao.php';
include 'php/cadastro_cliente.php';

// Buscar áreas de atuação
$query_areas = "SELECT id, nome FROM areas_atuacao ORDER BY nome";
$result_areas = $conn->query($query_areas);

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
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h1>Cadastro de Cliente</h1>
            <form id="cadastroForm" action="salvar_cliente.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipoPessoa" class="required">Tipo de Pessoa:</label>
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
                            <label for="razaoSocial" class="required">Razão Social:</label>
                            <input type="text" id="razaoSocial" name="razaoSocial" placeholder="Digite a razão social">
                        </div>
                        
                        <div class="form-group">
                            <label for="cnpj" class="required">CNPJ:</label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00">
                        </div>
                    </div>
                    
                    <!-- Campo Atividade Principal (CNAE) - Somente aparece para Pessoa Jurídica -->
                    <div class="form-row" id="atividadePrincipalRow" class="hidden">
                        <div class="form-group">
                            <label for="atividade_principal">Atividade Principal (CNAE e atividade)</label>
                            <select name="atividade_principal" id="atividade_principal" onchange="atualizarDescricaoCNAE(this)">
                                <option value="">Selecione uma atividade principal</option>
                                <?php
                                foreach ($cnae_data as $cnae) {
                                    $codigo = $cnae['id'];
                                    $descricao = $cnae['descricao'];
                                    echo "<option value=\"$codigo\" data-descricao=\"" . htmlspecialchars($descricao) . "\">$codigo - $descricao</option>";
                                }
                                ?>
                            </select>
                            <input type="hidden" name="descricao_cnae" id="descricao_cnae">
                        </div>
                    </div>
                </div>

                <div id="pessoaFisica" class="hidden">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nomeCliente" class="required">Nome do Cliente:</label>
                            <input type="text" id="nomeCliente" name="nomeCliente" required placeholder="Digite o nome completo">
                        </div>

                        <div class="form-group">
                            <label for="cpf" class="required">CPF:</label>
                            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cep" class="required">CEP:</label>
                        <input type="text" id="cep" name="cep" required placeholder="00000-000">
                        <small id="cep-feedback" class="form-text"></small>
                    </div>
                    <div class="form-group">
                        <label for="rua">Rua:</label>
                        <input type="text" id="rua" name="rua" readonly placeholder="Endereço">
                    </div>
                    <div class="form-group">
                        <label for="numero" class="required">Número:</label>
                        <input type="text" id="numero" name="numero" required placeholder="Número">
                    </div>
                    <div class="form-group">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro">
                    </div>
                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" readonly placeholder="Estado">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">E-mail:</label>
                        <input type="email" id="email" name="email" required placeholder="seu@email.com">
                    </div>
                    <div class="form-group">
                        <label for="celular" class="required">Celular:</label>
                        <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000">
                    </div>
                    <div class="form-group">
                        <label for="coordenada">Coordenada:</label>
                        <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude">
                        <small id="coordenadas-feedback" class="form-text"></small>
                    </div>
                </div>

                <br>
                <button class="btn" type="submit">Cadastrar</button>
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
            // Controle de exibição de campos dependendo do tipo de pessoa
            $('#tipoPessoa').change(function() {
                if ($(this).val() === 'J') {
                    $('#pessoaJuridica').removeClass('hidden');
                    $('#pessoaFisica').addClass('hidden');
                    $('#atividadePrincipalRow').removeClass('hidden'); // Exibe o campo de atividade principal
                    $('#razaoSocial, #cnpj').prop('required', true);
                    $('#nomeCliente, #cpf').prop('required', false);
                } else if ($(this).val() === 'F') {
                    $('#pessoaFisica').removeClass('hidden');
                    $('#pessoaJuridica').addClass('hidden');
                    $('#atividadePrincipalRow').addClass('hidden'); // Oculta o campo de atividade principal
                    $('#nomeCliente, #cpf').prop('required', true);
                    $('#razaoSocial, #cnpj').prop('required', false);
                } else {
                    $('#pessoaJuridica, #pessoaFisica').addClass('hidden');
                    $('#atividadePrincipalRow').addClass('hidden'); // Oculta o campo de atividade principal
                }
            });

            // Inicialização do tipoPessoa com os campos correspondentes
            if ($('#tipoPessoa').val() === 'J') {
                $('#pessoaJuridica').removeClass('hidden');
                $('#pessoaFisica').addClass('hidden');
                $('#atividadePrincipalRow').removeClass('hidden'); // Exibe o campo de atividade principal
                $('#razaoSocial, #cnpj').prop('required', true);
                $('#nomeCliente, #cpf').prop('required', false);
            } else if ($('#tipoPessoa').val() === 'F') {
                $('#pessoaFisica').removeClass('hidden');
                $('#pessoaJuridica').addClass('hidden');
                $('#atividadePrincipalRow').addClass('hidden'); // Oculta o campo de atividade principal
                $('#nomeCliente, #cpf').prop('required', true);
                $('#razaoSocial, #cnpj').prop('required', false);
            }
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

    </script>
    <script src="js/cep.js"></script>
</body>
</html>