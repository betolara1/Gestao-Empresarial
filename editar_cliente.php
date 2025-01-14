<?php
include 'conexao.php';
include 'php/editar_cliente.php'
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
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>Editar Cliente</h1>
            <form action="atualizar_cliente.php" method="POST">
                <!-- Campo oculto com ID -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" id="tipo_pessoa" name="tipo_pessoa" readonly value="<?php echo htmlspecialchars($cliente['tipo_pessoa']); ?>">

                <div id="pessoaJuridica" class="hidden">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="razaoSocial" class="required">Razão Social:</label>
                            <input type="text" name="razao_social" id="razaoSocial" name="razaoSocial" placeholder="Digite a razão social"value="<?php echo htmlspecialchars($cliente['razao_social']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="cnpj" class="required">CNPJ:</label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" value="<?php echo htmlspecialchars($cliente['cnpj']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="atividade_principal" class="required">Atividade Principal (CNAE e atividade)</label>
                            <select name="atividade_principal" id="atividade_principal">
                                <option value="">Selecione uma atividade</option>
                                <?php
                                if ($cnae_data) {
                                    foreach ($cnae_data as $cnae) {
                                        $codigo = htmlspecialchars($cnae['id']);
                                        $descricao = htmlspecialchars($cnae['descricao']);
                                        
                                        // Verifica se o código atual é igual ao código do cliente
                                        $selected = ($codigo == $cliente['codigo_cnae']) ? 'selected' : '';

                                        echo '<option value="' . $codigo . '" ' . $selected . '>' . 
                                            $codigo . ' - ' . $descricao . 
                                            '</option>';
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
                            <input type="text" id="nomeCliente" name="nomeCliente" placeholder="Digite o nome completo" value="<?php echo htmlspecialchars($cliente['nome']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="cpf" class="required">CPF:</label>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($cliente['cpf']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cep" class="required">CEP:</label>
                        <input type="text" id="cep" name="cep"
                            value="<?php echo htmlspecialchars($cliente['cep'] ?? ''); ?>" required placeholder="00000-000">
                        <small id="cep-feedback" class="form-text"></small>
                    </div>

                    <div class="form-group">
                        <label for="rua">Rua:</label>
                        <input type="text" id="rua" name="rua" readonly placeholder="Endereço" value="<?php echo htmlspecialchars($cliente['rua']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="numero" class="required">Número:</label>
                        <input type="text" id="numero" name="numero" required placeholder="Número" value="<?php echo htmlspecialchars($cliente['numero']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc." value="<?php echo htmlspecialchars($cliente['complemento']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro" value="<?php echo htmlspecialchars($cliente['bairro']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade" value="<?php echo htmlspecialchars($cliente['cidade']); ?>">
                    </div>
                
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" readonly placeholder="Estado" value="<?php echo htmlspecialchars($cliente['estado']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">E-mail:</label>
                        <input type="email" id="email" name="email" required placeholder="seu@email.com" value="<?php echo htmlspecialchars($cliente['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="coordenada">Coordenada:</label>
                        <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude" 
                            value="<?php echo htmlspecialchars($cliente['coordenada'] ?? ''); ?>">
                            <small id="coordenadas-feedback" class="form-text"></small>
                    </div>

                    <div class="form-group">
                        <label for="celular" class="required">Celular:</label>
                        <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($cliente['celular']); ?>">
                    </div>
                </div>

                <div id="pessoaJuridica2" class='hidden'>

                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn" type="submit" style="margin-right: 10px;">Atualizar Cliente</button>
                    
                    <a href="excluir_cliente.php?id=<?php echo $id; ?>" 
                    onclick="return confirm('Tem certeza de que deseja excluir este cliente?');" 
                    class="btn-excluir">
                        Excluir
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
        
    </script>

    <script src="js/cep.js"></script>
</body>
