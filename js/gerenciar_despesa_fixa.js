function excluirDespesa(id) {
    if (confirm('Tem certeza que deseja excluir este registro?')) {
        $.ajax({
            url: 'excluir_despesa_fixa.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Remove a linha da tabela
                    $('#row-' + id).fadeOut(400, function() {
                        $(this).remove();
                        // Recalcula o total
                        atualizarTotal();
                    });
                    
                    // Mostra mensagem de sucesso
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erro ao processar a requisição');
            }
        });
    }
}

function atualizarTotal() {
    let total = 0;
    // Soma todos os valores da tabela
    $('table tbody tr:not(.total-row)').each(function() {
        const valorText = $(this).find('td:eq(1)').text().replace('R$ ', '').replace('.', '').replace(',', '.');
        total += parseFloat(valorText || 0);
    });
    
    // Atualiza a linha do total
    $('.total-row td:eq(1)').text('R$ ' + total.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
}