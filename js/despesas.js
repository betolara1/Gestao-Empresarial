function cadastrarDespesa() {
    var valor = $('#valor_despesa').val();
    
    $.ajax({
        url: 'salvar_despesa.php',
        type: 'POST',
        data: {
            nome_despesa: $('#nome_despesa').val(),
            valor_despesa: valor,
            proposta: $('#numero_proposta').val() 
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // ... rest of the success handler
                alert('Despesa cadastrada com sucesso!');
                $('#nome_despesa').val('');
                $('#valor_despesa').val('');
                $('#numero_proposta').val('');

            } else {
                // ... rest of the success handler
                alert('Erro ao cadastrar despesa: ' + data.message);
            }
        },
        error: function(xhr, status, error) {
            // ... rest of the error handler
            alert('Erro ao cadastrar despesa: ' + error);
        }
    });
}

