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
    <title>Editar Empresa</title>    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
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
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" 
                                value="<?php echo htmlspecialchars($empresa['complemento'] ?? ''); ?>">
                        </div>

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
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" 
                                value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="celular" class="required">Celular</label>
                            <input type="text" id="celular" name="celular" required 
                                value="<?php echo htmlspecialchars($empresa['celular'] ?? ''); ?>">
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
                                <button type="button" id="adicionar_cnae" class="btn btn-secondary"><label></label>
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

    <script src="js/coordenadas.js"></script>
    <script src="js/cep.js"></script>
    <script>
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