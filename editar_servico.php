<?php
include 'conexao.php';
include 'php/editar_servico.php'
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
    <div class="container">
        <h1>Editar Serviço</h1>
        <form action="atualizar_servico.php" method="POST">
            

            <div class="form-row">
                <div class="form-group">
                    <label for="cliente">Número da Proposta</label>
                    <input type="text" name="numero_proposta" value="<?php echo $servico['numero_proposta']; ?>">
                </div>
                <div class="form-group">
                    <label for="cliente">Cliente</label>
                    <input type="text" id="cliente" name="cliente" value="<?php echo htmlspecialchars($servico['cliente_id']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cnpj_cpf">CNPJ/CPF</label>
                    <input type="text" id="cnpj_cpf" name="cnpj_cpf" value="<?php echo htmlspecialchars($servico['cnpj_cpf']); ?>" readonly>
                </div>
            </div>

            <label>Tipos de Serviço:</label>
            <div class="checkbox-group">
                <?php
                    while($row = $resultTipos->fetch_assoc()) {
                        $checked = $row['is_selected'] ? 'checked' : '';
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='checkbox' 
                            name='tipo_servico[]' 
                            id='servico_" . $row['id'] . "' 
                            value='" . $row['id'] . "' 
                            " . $checked . ">";
                        echo "<label class='form-check-label' for='servico_" . $row['id'] . "'>";
                        echo htmlspecialchars($row['tipo_servico']);
                        echo "</label>";
                        echo "</div>";
                    }
                ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="data_inicio">Data de Início</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($servico['data_inicio']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="data_termino">Data de Término</label>
                    <input type="date" id="data_termino" name="data_termino" value="<?php echo htmlspecialchars($servico['data_termino']); ?>">
                </div>

                <div class="form-group">
                    <label for="status_servico">Status do Serviço</label>
                    <input type="text" id="status_servico" name="status_servico" value="<?php echo htmlspecialchars($servico['status_servico']); ?>" readonly>
                </div>


                
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="data_pagamento">Vencimento</label>
                    <input type="date" id="data_pagamento" name="data_pagamento" value="<?php echo htmlspecialchars($servico['data_pagamento']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="responsavel_execucao">Responsável pelo Serviço</label>
                    <input type="text" id="responsavel_execucao" name="responsavel_execucao" value="<?php echo htmlspecialchars($servico['responsavel_execucao']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="forma_pagamento">Forma de Pagamento</label>
                    <select id="forma_pagamento" name="forma_pagamento" required>
                        <option value="">Selecione a forma de pagamento</option>
                        <option value="CARTÃO DE CRÉDITO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'CARTÃO DE CRÉDITO' ? 'selected' : ''; ?>>Cartão de Crédito</option>
                        <option value="CARTÃO DE DÉBITO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'CARTÃO DE DÉBITO' ? 'selected' : ''; ?>>Cartão de Débito</option>
                        <option value="PIX" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'PIX' ? 'selected' : ''; ?>>PIX</option>
                        <option value="DINHEIRO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'DINHEIRO' ? 'selected' : ''; ?>>Dinheiro</option>
                        <option value="BOLETO" <?php echo isset($servico['forma_pagamento']) && $servico['forma_pagamento'] === 'BOLETO' ? 'selected' : ''; ?>>Boleto</option>
                    </select>
                </div>

                <div id="editarServicoForm" class="form-group">
                    <label for="parcelamento">Quatidade de Parcelas</label>
                    <input type="number" id="parcelamento" name="parcelamento" step="0.01" value="<?php echo htmlspecialchars($servico['parcelamento']); ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="valor_total">Valor Total</label>
                    <input type="number" id="valor_total" name="valor_total" step="0.01" value="<?php echo htmlspecialchars($servico['valor_total']); ?>">
                </div>

                <div>
                    <label for="valor_entrada">Valor Entrada</label>
                    <input type="number" id="valor_entrada" name="valor_entrada" step="0.01" 
                        value="<?php echo isset($servico['valor_entrada']) && $servico['valor_entrada'] !== '' ? htmlspecialchars($servico['valor_entrada']) : '0'; ?>">
                </div>

                <div>
                    <label for="valor_pago">Valor Pago</label>
                    <input type="number" id="valor_pago" name="valor_pago" step="0.01" 
                        value="<?php echo number_format($total_pago, 2, '.', ''); ?>" readonly>
                </div>

                <div>
                    <label for="valor_pagar">Valor A Ser Pago</label>
                    <input type="number" id="valor_pagar" name="valor_pagar" step="0.01" 
                        value="<?php echo number_format($total_pendente, 2, '.', ''); ?>" readonly>
                </div>
            </div>


            <label>Endereço do Serviço</label>
            <div class="form-row">
                <div class="form-group">
                    <label for="cep" class="required">CEP:</label>
                    <input type="text" id="cep" name="cep" required placeholder="00000-000" 
                        value="<?php echo htmlspecialchars($servico['cep']); ?>">
                    <small id="cep-feedback" class="form-text"></small>
                </div>
                <div class="form-group">
                    <label for="rua">Rua:</label>
                    <input type="text" id="rua" name="rua" placeholder="Endereço" 
                        value="<?php echo htmlspecialchars($servico['rua']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="numero" class="required">Número:</label>
                    <input type="text" id="numero" name="numero" required placeholder="Número" 
                        value="<?php echo htmlspecialchars($servico['numero']); ?>">
                </div>
                <div class="form-group">
                    <label for="complemento">Complemento:</label>
                    <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc." 
                        value="<?php echo htmlspecialchars($servico['complemento']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bairro">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" placeholder="Bairro" 
                        value="<?php echo htmlspecialchars($servico['bairro']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" placeholder="Cidade" 
                        value="<?php echo htmlspecialchars($servico['cidade']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" placeholder="Estado" 
                        value="<?php echo htmlspecialchars($servico['estado']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="coordenada">Coordenada:</label>
                    <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude" 
                        value="<?php echo htmlspecialchars($servico['coordenada']); ?>">
                    <small id="coordenadas-feedback" class="form-text"></small>
                </div>
            </div>


            <button class="btn" type="submit">Salvar Alterações</button>
        </form>
    </div>
    </div>

    <script>
        // Mascaras de CPF, CNPJ e outros campos
        $('#cep').mask('00000-000');
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
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

        // Adicionar evento de submit ao formulário
        document.getElementById('despesaForm').addEventListener('submit', cadastrarDespesa);

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
                statusServico.val('CONCLUIDO');
            } else {
                statusServico.val('EM ANDAMENTO');
            }
        }



    </script>
    <script src="js/status_servico.js"></script>
</body>
</html>
