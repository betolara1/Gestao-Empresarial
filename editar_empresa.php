<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';
include 'php/editar_empresa.php'
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Minha Empresa</title>
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
            <form id="cadastroForm" action="atualizar_empresa.php" method="POST">
                <h1>Editar Minha Empresa</h1>

                <div class="form-row">
                    <div class="form-group">
                        <label for="razao_social">Razão Social:</label>
                        <input type="text" id="razaoSocial" name="razaoSocial"
                            value="<?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cnpj">CNPJ:</label>
                        <input type="text" id="cnpj" name="cnpj"
                            value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>">  
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nome">Nome do Cliente:</label>
                        <input type="text" id="nome" name="nome" 
                            value="<?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF:</label>
                        <input type="text" id="cpf" name="cpf" 
                            value="<?php echo htmlspecialchars($empresa['cpf'] ?? ''); ?>"> 
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cep" class="required">CEP:</label>
                        <input type="text" id="cep" name="cep"
                            value="<?php echo htmlspecialchars($empresa['cep'] ?? ''); ?>" required placeholder="00000-000">
                        <small id="cep-feedback" class="form-text"></small>
                    </div>

                    <div class="form-group">
                        <label for="rua" >Rua:</label>
                        <input type="text" id="rua" name="rua" readonly
                            value="<?php echo htmlspecialchars($empresa['rua'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="numero">Número:</label>
                        <input type="text" id="numero" name="numero" 
                            value="<?php echo htmlspecialchars($empresa['numero'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" 
                            value="<?php echo htmlspecialchars($empresa['complemento'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" readonly
                            value="<?php echo htmlspecialchars($empresa['bairro'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" readonly
                            value="<?php echo htmlspecialchars($empresa['cidade'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" readonly 
                            value="<?php echo htmlspecialchars($empresa['estado'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" 
                            value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="coordenada">Coordenada:</label>
                        <input type="text" id="coordenada" name="coordenada" 
                            value="<?php echo htmlspecialchars($empresa['coordenada'] ?? ''); ?>" 
                            placeholder="Latitude, Longitude">
                            <small id="coordenadas-feedback" class="form-text"></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" 
                            value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="celular">Celular:</label>
                        <input type="text" id="celular" name="celular" 
                            value="<?php echo htmlspecialchars($empresa['celular'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="atividade_principal">Atividade Principal (CNAE e atividade)</label>
                        <select name="atividade_principal" id="atividade_principal" required onchange="atualizarDescricaoCNAE(this)">
                            <option value="">Selecione uma atividade principal</option>
                            <?php
                            foreach ($cnae_data as $cnae) {
                                $codigo = $cnae['id'];
                                $descricao = $cnae['descricao'];
                                $selected = ($codigo == $empresa['codigo_cnae']) ? 'selected' : '';
                                echo "<option value=\"$codigo\" data-descricao=\"" . htmlspecialchars($descricao) . "\" $selected>$codigo - $descricao</option>";
                            }
                            ?>
                        </select>
                        <input type="hidden" name="descricao_cnae" id="descricao_cnae" value="<?php echo htmlspecialchars($empresa['descricao_cnae'] ?? ''); ?>">
                    </div>
                </div>


                <div class="form-row">
                    <div class="form-group">
                        <label for="atividades_secundarias">Atividades Secundárias (CNAE e atividade)</label>
                        <div class="d-flex gap-2">
                        <select name="cnae_select" id="cnae_select" class="form-control">
                            <option value="">Selecione um CNAE</option>
                            <?php
                            foreach ($cnae_data as $cnae) {
                                $codigo = $cnae['id'];
                                $descricao = $cnae['descricao'];
                                // Verifica se o CNAE já está selecionado
                                $disabled = in_array($codigo, $atividades_secundarias_selecionadas) ? 'disabled' : '';
                                echo "<option value=\"$codigo\" data-descricao=\"$descricao\" $disabled>$codigo - $descricao</option>";
                            }
                            ?>
                        </select>
                            <button type="button" id="adicionar_cnae" class="btn btn-primary">Adicionar</button>
                        </div>
                        
                        <!-- Lista de CNAEs selecionados -->
                        <div id="cnaes_selecionados" class="mt-3">
                            <h6>CNAEs Selecionados:</h6>
                            <ul class="list-group" id="lista_cnaes">
                                <?php
                                if (!empty($cnaes_secundarios)) {
                                    foreach ($cnaes_secundarios as $index => $cnae) {
                                        echo "<li class='list-group-item d-flex justify-content-between align-items-center' data-id='{$cnae['id']}'>";
                                        echo "{$cnae['id']} - {$cnae['descricao']}";
                                        echo "<input type='hidden' name='descricoes_secundarias[]' value='" . htmlspecialchars($descricoes_secundarias[$index]) . "' class='form-control' placeholder='Descrição' style='width: 200px;'>";
                                        echo "<button type='button' class='btn btn-danger btn-sm remover-cnae'>Remover</button>";
                                        echo "<input type='hidden' name='atividades_secundarias[]' value='{$cnae['id']}'>";
                                        echo "</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <label></label><label></label>
                <button class='btn' type="submit">Atualizar</button>
            
            </form>
        </div>
    </div>

    <script src="js/cep.js"></script>
    <script>
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


        document.addEventListener('DOMContentLoaded', function() {
            // Função para criar e configurar campo de busca
            function createSearchableSelect(selectElement, placeholder) {
                // Cria campo de busca
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.placeholder = placeholder;
                searchInput.className = 'form-control mb-2';
                
                // Insere o campo de busca antes do select
                selectElement.parentNode.insertBefore(searchInput, selectElement);
                
                // Array com todas as opções originais
                const options = Array.from(selectElement.options);
                
                // Função de busca
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    // Remove todas as opções atuais
                    selectElement.innerHTML = '';
                    
                    // Adiciona opção padrão
                    const defaultOption = selectElement.id === 'atividade_principal' 
                        ? 'Selecione uma atividade'
                        : 'Selecione as atividades secundárias';
                    selectElement.add(new Option(defaultOption, ''));
                    
                    // Filtra e adiciona opções que correspondem à busca
                    options.forEach(option => {
                        if (option.value === '') return; // Pula a opção padrão
                        
                        if (option.text.toLowerCase().includes(searchTerm)) {
                            const newOption = new Option(option.text, option.value);
                            // Mantém o estado selecionado para atividades secundárias
                            if (selectElement.id === 'cnae_select' && option.selected) {
                                newOption.selected = true;
                            }
                            selectElement.add(newOption);
                        }
                    });
                });
            }

            // Configura busca para atividade principal
            const atividadePrincipal = document.getElementById('atividade_principal');
            createSearchableSelect(atividadePrincipal, 'Buscar CNAE Principal...');

            // Configura busca para atividades secundárias
            const atividadesSecundarias = document.getElementById('cnae_select');
            createSearchableSelect(atividadesSecundarias, 'Buscar CNAE Secundário...');
        });

        document.addEventListener('DOMContentLoaded', function() {
            const selectCnae = document.getElementById('cnae_select');
            const btnAdicionar = document.getElementById('adicionar_cnae');
            const listaCnaes = document.getElementById('lista_cnaes');

            btnAdicionar.addEventListener('click', function() {
                const selectedOption = selectCnae.options[selectCnae.selectedIndex];
                
                if (!selectedOption.value) {
                    alert('Por favor, selecione um CNAE');
                    return;
                }

                // Verifica se o CNAE já foi adicionado
                const jaExiste = document.querySelector(`#lista_cnaes li[data-id="${selectedOption.value}"]`);
                if (jaExiste) {
                    alert('Este CNAE já foi adicionado');
                    return;
                }

                // Cria novo item na lista
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.setAttribute('data-id', selectedOption.value);
                
                // Adiciona o texto do CNAE e os campos hidden
                li.innerHTML = `
                    ${selectedOption.text}
                    <button type="button" class="btn btn-danger btn-sm remover-cnae">Remover</button>
                    <input type="hidden" name="atividades_secundarias[]" value="${selectedOption.value}">
                    <input type="hidden" name="descricoes_secundarias[]" value="${selectedOption.dataset.descricao}">
                `;

                // Adiciona à lista
                listaCnaes.appendChild(li);
                
                // Desabilita a opção no select
                selectedOption.disabled = true;
                
                // Limpa a seleção
                selectCnae.value = '';
            });

            // Delegação de eventos para o botão remover
            listaCnaes.addEventListener('click', function(e) {
                if (e.target.classList.contains('remover-cnae')) {
                    const li = e.target.closest('li');
                    const cnaeId = li.getAttribute('data-id');
                    
                    // Habilita novamente a opção no select
                    const option = selectCnae.querySelector(`option[value="${cnaeId}"]`);
                    if (option) {
                        option.disabled = false;
                    }
                    
                    // Remove o item da lista
                    li.remove();
                }
            });
        });

        function atualizarDescricaoCNAE(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const descricaoInput = document.getElementById('descricao_cnae');
            if (selectedOption.value) {
                descricaoInput.value = selectedOption.dataset.descricao;
            } else {
                descricaoInput.value = '';
            }
        }

        // Executa ao carregar a página para garantir que a descrição está correta
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('atividade_principal');
            atualizarDescricaoCNAE(select);
        });
    </script>
    <script src="js/cep.js"></script>

</body>
</html>