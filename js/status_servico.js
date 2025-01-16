document.addEventListener('DOMContentLoaded', function() {
    // Atualizar o status do serviço quando as datas são alteradas
    const dataInicioInput = document.getElementById('data_inicio');
    const dataTerminoInput = document.getElementById('data_termino');
    const statusServicoInput = document.getElementById('status_servico');

    // Define a data máxima como hoje
    const hoje = new Date().toISOString().split('T')[0];
    if (dataTerminoInput) {
        dataTerminoInput.setAttribute('max', hoje);
    }

    function atualizarStatusServico() {
        const dataInicio = dataInicioInput.value;
        const dataTermino = dataTerminoInput.value;
        
        // Validações
        if (dataTermino && dataInicio) {
            if (new Date(dataTermino) < new Date(dataInicio)) {
                alert('Data de término não pode ser menor que a data de início');
                dataTerminoInput.value = '';
                statusServicoInput.value = 'EM ANDAMENTO';
                return;
            }
            
            if (new Date(dataTermino) > new Date()) {
                alert('Data de término não pode ser maior que hoje');
                dataTerminoInput.value = '';
                statusServicoInput.value = 'EM ANDAMENTO';
                return;
            }
        }
        
        // Definir status
        if (!dataInicio) {
            statusServicoInput.value = '';
        } else if (dataTermino) {
            statusServicoInput.value = 'CONCLUIDO';
        } else {
            statusServicoInput.value = 'EM ANDAMENTO';
        }
    }

    // Adiciona os event listeners
    if (dataInicioInput) {
        dataInicioInput.addEventListener('change', atualizarStatusServico);
    }
    if (dataTerminoInput) {
        dataTerminoInput.addEventListener('change', atualizarStatusServico);
    }

    // Inicializa o status ao carregar a página
    atualizarStatusServico();
});
