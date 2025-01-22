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


if ($empresa != 0) {
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
                gap: 15px; /* Espaçamento entre os campos */
            }

            .form-group {
                flex: 1;
                min-width: 200px; /* Largura mínima para os campos */
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
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" placeholder="(00) 0000-0000">
                            </div>

                            <div class="form-group">
                                <label for="celular" class="required">Celular</label>
                                <input type="text" id="celular" name="celular" required placeholder="(00) 00000-0000">
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
                                <input type="hidden" name="descricao_cnae" id="descricao_cnae">
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
                                    
                                    // If you want to save the coordinates immediately
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

                // Função para obter a descrição do CNAE selecionado
                function getCNAEDescription(codigo) {
                    const option = selectCnae.querySelector(`option[value="${codigo}"]`);
                    return option ? option.text : '';
                }

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
                    
                    // Adiciona o texto do CNAE e campos hidden para código e descrição
                    li.innerHTML = `
                        ${selectedOption.text}
                        <button type="button" class="btn btn-danger btn-sm remover-cnae">Remover</button>
                        <input type="hidden" name="atividades_secundarias[]" value="${selectedOption.value}">
                        <input type="hidden" name="descricoes_secundarias[]" value="${selectedOption.text}">
                    `;

                    // Adiciona à lista
                    listaCnaes.appendChild(li);
                    
                    // Limpa a seleção
                    selectCnae.value = '';
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

            // Evento para remover CNAE
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remover-cnae')) {
                    e.target.closest('li').remove();
                }
            });
            
        </script>
        <script src="js/cep.js"></script>
        <script src="js/coordenadas.js"></script>
    </body>
    </html>
<?php
}else {
    // Se houver registros, redireciona para a página de edição
    header("Location: gerenciar_empresa.php");
    exit();
}
?>
