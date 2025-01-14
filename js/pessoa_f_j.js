$(document).ready(function() {
    // Controle de exibição de campos dependendo do tipo de pessoa
    $('#tipoPessoa').change(function() {
        if ($(this).val() === 'J') {
            $('#pessoaJuridica').removeClass('hidden');
            $('#pessoaFisica').addClass('hidden');
            $('#atividadePrincipalRow').removeClass('hidden'); // Exibe o campo de atividade principal
            $('#razaoSocial, #cnpj').prop('required', true);
            $('#nomeCliente, #cpf').prop('required', false);
        } else if ($(this).val() === 'F') {
            $('#pessoaFisica').removeClass('hidden');
            $('#pessoaJuridica').addClass('hidden');
            $('#atividadePrincipalRow').addClass('hidden'); // Oculta o campo de atividade principal
            $('#nomeCliente, #cpf').prop('required', true);
            $('#razaoSocial, #cnpj').prop('required', false);
        } else {
            $('#pessoaJuridica, #pessoaFisica').addClass('hidden');
            $('#atividadePrincipalRow').addClass('hidden'); // Oculta o campo de atividade principal
        }
    });

    // Inicialização do tipoPessoa com os campos correspondentes
    if ($('#tipoPessoa').val() === 'J') {
        $('#pessoaJuridica').removeClass('hidden');
        $('#pessoaFisica').addClass('hidden');
        $('#atividadePrincipalRow').removeClass('hidden'); // Exibe o campo de atividade principal
        $('#razaoSocial, #cnpj').prop('required', true);
        $('#nomeCliente, #cpf').prop('required', false);
    } else if ($('#tipoPessoa').val() === 'F') {
        $('#pessoaFisica').removeClass('hidden');
        $('#pessoaJuridica').addClass('hidden');
        $('#atividadePrincipalRow').addClass('hidden'); // Oculta o campo de atividade principal
        $('#nomeCliente, #cpf').prop('required', true);
        $('#razaoSocial, #cnpj').prop('required', false);
    }
});