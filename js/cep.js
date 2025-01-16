$(document).ready(function() {
    function limpaFormularioCep() {
        // Limpa valores do formulário de cep.
        $("#rua").val("");
        $("#bairro").val("");
        $("#cidade").val("");
        $("#estado").val("");
        $("#coordenada").val("");
    }

    function preencheCamposEndereco(dados) {
        $("#rua").val(dados.street || dados.logradouro);
        $("#bairro").val(dados.neighborhood || dados.bairro);
        $("#cidade").val(dados.city || dados.cidade || dados.localidade);
        $("#estado").val(dados.state || dados.uf);
    }

    function buscarCoordenadas(endereco) {
        $('#coordenada').val("Buscando coordenadas...");
        
        // Monta o endereço completo para busca
        const enderecoCompleto = `${endereco.logradouro}, ${endereco.localidade}, ${endereco.uf}, Brasil`;
        
        // Usa a API do OpenStreetMap (Nominatim)
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            type: 'GET',
            data: {
                q: enderecoCompleto,
                format: 'json',
                limit: 1
            },
            headers: {
                'Accept-Language': 'pt-BR'
            },
            success: function(response) {
                if (response && response.length > 0) {
                    const lat = response[0].lat;
                    const lon = response[0].lon;
                    $('#coordenada').val(`${lat}, ${lon}`);
                } else {
                    $('#coordenada').val('');
                    console.log('Coordenadas não encontradas');
                }
            },
            error: function(xhr, status, error) {
                $('#coordenada').val('');
                console.log('Erro ao buscar coordenadas:', error);
            }
        });
    }

    //Quando o campo cep perde o foco.
    $("#cep").on('blur change', function() {
        // Nova variável "cep" somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');

        //Verifica se campo cep possui valor informado.
        if (cep.length === 8) {
            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;

            //Valida o formato do CEP.
            if(validacep.test(cep)) {
                //Preenche os campos com "..." enquanto consulta webservice.
                $("#rua").val("...");
                $("#bairro").val("...");
                $("#cidade").val("...");
                $("#estado").val("...");
                $("#coordenada").val("Buscando coordenadas...");

                //Consulta o webservice viacep.com.br/
                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(dados) {
                    if (!("erro" in dados)) {
                        preencheCamposEndereco(dados);
                        // Busca coordenadas após preencher o endereço
                        buscarCoordenadas(dados);
                    } else {
                        //CEP pesquisado não foi encontrado.
                        limpaFormularioCep();
                        alert("CEP não encontrado.");
                    }
                }).fail(function() {
                    limpaFormularioCep();
                    alert("Erro ao buscar CEP. Tente novamente mais tarde.");
                });
            } else {
                //cep é inválido.
                limpaFormularioCep();
                alert("Formato de CEP inválido.");
            }
        } else {
            //cep sem valor, limpa formulário.
            limpaFormularioCep();
        }
    });
});
    
    