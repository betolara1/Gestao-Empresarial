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
                    if (selectElement.id === 'atividades_secundarias' && option.selected) {
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
    const atividadesSecundarias = document.getElementById('atividades_secundarias');
    createSearchableSelect(atividadesSecundarias, 'Buscar CNAE Secundário...');
});