$(document).ready(function() {
    let isRequesting = false;

    $('#cep').on('blur', function() {
        if (isRequesting) return;

        let cep = $(this).val().replace(/\D/g, '');
        if (cep !== '') {
            let validacep = /^[0-9]{8}$/;
            if (validacep.test(cep)) {
                isRequesting = true;
                $('#cep-feedback').text('Buscando CEP...').removeClass('text-danger').addClass('text-info');

                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`)
                    .done(function(dados) {
                        if (!('erro' in dados)) {
                            $('#rua').val(dados.logradouro);
                            $('#bairro').val(dados.bairro);
                            $('#cidade').val(dados.localidade);
                            $('#estado').val(dados.uf);
                            $('#cep-feedback').text('CEP encontrado!').removeClass('text-info text-danger').addClass('text-success');

                            // Buscar coordenadas
                            buscarCoordenadas(dados.logradouro + ', ' + dados.localidade + ' - ' + dados.uf);
                        } else {
                            limpaCamposEndereco();
                            $('#cep-feedback').text('CEP não encontrado.').removeClass('text-info text-success').addClass('text-danger');
                        }
                    })
                    .fail(function() {
                        limpaCamposEndereco();
                        $('#cep-feedback').text('Erro na busca do CEP.').removeClass('text-info text-success').addClass('text-danger');
                    })
                    .always(function() {
                        isRequesting = false;
                    });
            } else {
                limpaCamposEndereco();
                $('#cep-feedback').text('Formato de CEP inválido.').removeClass('text-info text-success').addClass('text-danger');
            }
        } else {
            limpaCamposEndereco();
            $('#cep-feedback').text('');
        }
    });

    function limpaCamposEndereco() {
        $('#cep').val('');
        $('#rua').val('');
        $('#bairro').val('');
        $('#cidade').val('');
        $('#estado').val('');
        $('#coordenada').val('');
    }
});
    
    