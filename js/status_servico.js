// Atualizar o status do serviço quando as datas são alteradas
const hoje = new Date().toISOString().split('T')[0];
document.getElementById('data_termino').setAttribute('max', hoje);

$(document).ready(function() {
    // Inicializar o status baseado nos valores existentes
    atualizarStatusServico();
    
    // Atualizar quando as datas mudarem
    $('#data_inicio, #data_termino').on('change', atualizarStatusServico);
});

function atualizarStatusServico() {
    const dataInicio = $('#data_inicio').val();
    const dataTermino = $('#data_termino').val();
    const statusServico = $('#status_servico');
    const hoje = new Date().toISOString().split('T')[0];
    
    // Validações
    if (dataTermino && dataInicio) {
        if (new Date(dataTermino) < new Date(dataInicio)) {
            alert('Data de término não pode ser menor que a data de início');
            $('#data_termino').val('');
            statusServico.val('EM ANDAMENTO');
            return;
        }
        
        if (new Date(dataTermino) > new Date()) {
            alert('Data de término não pode ser maior que hoje');
            $('#data_termino').val('');
            statusServico.val('EM ANDAMENTO');
            return;
        }
    }
    
    // Definir status
    if (!dataInicio) {
        statusServico.val('');
    } else if (dataTermino) {
        statusServico.val('CONCLUÍDO');
    } else {
        statusServico.val('EM ANDAMENTO');
    }
}


$(document).ready(function() {
    $('#editarServicoForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = response.redirect;
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function() {
                alert('Erro ao processar a requisição.');
            }
        });
    });

    $('#parcelamento').on('change', function() {
        updateInstallmentTablePreview();
    });

    function updateInstallmentTablePreview() {
        var parcelamento = $('#parcelamento').val();
        var valorTotal = parseFloat($('#valor_total').val());
        var valorEntrada = parseFloat($('#valor_entrada').val()) || 0;
        var valorRestante = valorTotal - valorEntrada;
        var valorParcela = valorRestante / parcelamento;

        var tableBody = $('#tabelaPagamentos tbody');
        tableBody.empty();

        for (var i = 1; i <= parcelamento; i++) {
            var row = `<tr>
                <td id="status-${i-1}" class="status-aberto">Aberto</td>
                <td>${getFormattedDate(i)}</td>
                <td>R$ ${valorParcela.toFixed(2)}</td>
                <td><button class="pay-btn" data-index="${i-1}" data-proposta="<?php echo $servico['numero_proposta']; ?>" data-valor="${valorParcela.toFixed(2)}" data-data="${getFormattedDate(i)}">Pagar</button></td>
            </tr>`;
            tableBody.append(row);
        }
    }

    function getFormattedDate(monthsToAdd) {
        var date = new Date();
        date.setMonth(date.getMonth() + monthsToAdd);
        return date.toLocaleDateString('pt-BR');
    }

    $('.pay-btn').on('click', function() {
        const button = $(this);
        const index = button.data('index');
        const numeroProposta = button.data('proposta');
        const valorParcela = button.data('valor');
        const dataPagamento = button.data('data');
        
        if (confirm('Confirmar o pagamento desta parcela?')) {
            const formData = new FormData();
            formData.append('numero_proposta', numeroProposta);
            formData.append('parcela_num', index + 1);
            formData.append('valor_parcela', valorParcela);
            formData.append('data_pagamento', dataPagamento);
            
            $.ajax({
                url: 'atualizar_pagamento.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const statusCell = $(`#status-${index}`);
                        statusCell.text('Pago');
                        button.prop('disabled', true).text('Pago');
                        statusCell.removeClass('status-aberto').addClass('status-pago');
                        
                        // Atualizar os totais
                        $('#valor_pago').val(response.total_pago);
                        $('#valor_pagar').val(response.total_pendente);
                        
                        alert('Pagamento confirmado com sucesso!');
                    } else {
                        let errorMessage = 'Erro ao confirmar pagamento: ';
                        if (response.error_type === 'invalid_input') {
                            errorMessage += 'Dados inválidos fornecidos.';
                        } else if (response.error_type === 'database_error') {
                            errorMessage += 'Erro no banco de dados. Por favor, tente novamente.';
                        } else {
                            errorMessage += response.message || 'Erro desconhecido';
                        }
                        alert(errorMessage);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    alert('Erro ao processar pagamento. Por favor, tente novamente.');
                }
            });
        }
    });
});
