<?php
include 'conexao.php';
include 'php/gerenciar_relatorio.php'
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Serviços</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/status_colors.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h1>RELATÓRIO DE SERVIÇOS</h1>

            <div class="search-container">
                <form action="" method="GET">
                    <input type="text" name="search" id="search" class="search-input" placeholder="Buscar serviços...">
                    <label></label>
                </form>
            </div>

            <br><br>
            <table border="1" id="servicos-table">
                <thead>
                    <tr>
                        <th>Nº Proposta</th>
                        <th>Cliente</th>
                        <th>CNPJ/CPF</th>
                        <th>Serviços</th>
                        <th>Data Início</th>
                        <th>Data Término</th>
                        <th>Status Serviço</th>
                        <th>Orçamento</th>
                        <th>Entrada</th>
                        <th>Valor Líquido</th>
                        <th>Total Despesas</th>
                        <th>Pagamento</th>
                        <th>Parcelamento</th>
                        <th>Status Pagamento</th>
                        <th>Valor Pago</th>
                        <th>Valor A Ser Pago</th>
                        <th>Próximo Pagamento</th>
                        <th>Detalhes Pagamento</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($servicos)): ?>
                        <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($servico['numero_proposta']); ?></td>
                                <td><?php echo htmlspecialchars($servico['cliente_nome_ou_razao']); ?></td>
                                <td><?php echo htmlspecialchars($servico['cnpj_cpf']); ?></td>
                                <td><?php echo empty($servico['tipos_servico']) ? 'Nenhum' : htmlspecialchars($servico['tipos_servico']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($servico['data_inicio'])); ?></td>
                                <td><?php echo ($servico['data_termino'] && $servico['data_termino'] != '0000-00-00') ? date('d/m/Y', strtotime($servico['data_termino'])) : ''; ?></td>
                                <td class="status-servico status-<?php echo strtolower(str_replace(' ', '-', $servico['status_servico'])); ?>">
                                    <?php echo htmlspecialchars($servico['status_servico']); ?>
                                </td>
                                <td class="valor">R$ <?php echo number_format($servico['valor_total'], 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format($servico['valor_entrada'], 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format($servico['valor_a_pagar'], 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format($servico['total_despesas'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($servico['forma_pagamento']); ?></td>
                                <td><?php echo htmlspecialchars($servico['parcelamento']); ?></td>
                                <td class="status-pagamento status-<?php echo strtolower(str_replace(' ', '-', $servico['status_pagamento'])); ?>">
                                    <?php echo htmlspecialchars($servico['status_pagamento']); ?>
                                </td>
                                <td class="valor">R$ <?php echo number_format($servico['total_pago'], 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format($servico['total_pendente'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                    if ($servico['status_pagamento'] == 'FINALIZADO') {
                                        echo '-';
                                    } else {
                                        echo $servico['proximo_pagamento'] ? date('d/m/Y', strtotime($servico['proximo_pagamento'])) : '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn-detalhes-pagamento" data-proposta="<?php echo $servico['numero_proposta']; ?>">
                                        Ver Detalhes
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($servico['responsavel_execucao']); ?></td>
                                <td>
                                    <a href="editar_servico.php?id=<?php echo $servico['numero_proposta']; ?>" class="btn">Editar</a>
                                    <form action="excluir_servico.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="numero_proposta" value="<?php echo $servico['numero_proposta']; ?>">
                                        <button type="submit" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este registro?');">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="17">Nenhum serviço encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Popup for payment details -->
    <div id="paymentPopup" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <h2>Detalhes do Pagamento</h2>
            <div id="paymentDetails"></div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const popup = $("#paymentPopup");
        const closeBtn = $(".close");
        const paymentDetails = $("#paymentDetails");

        $(".btn-detalhes-pagamento").on("click", function() {
            const numeroProposta = $(this).data("proposta");
            
            // AJAX request to get payment details
            $.ajax({
                url: 'get_payment_details.php',
                method: 'GET',
                data: { numero_proposta: numeroProposta },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Populate the popup with payment details
                        let tableHtml = `
                            <table>
                                <thead>
                                    <tr>
                                        <th>Status do Pagamento</th>
                                        <th>Vencimento</th>
                                        <th>Valor da Parcela</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        response.parcelas.forEach((parcela, index) => {
                            tableHtml += `
                                <tr>
                                    <td id="status-${index}">${parcela.status_pagamento}</td>
                                    <td>${parcela.dia_pagamento}</td>
                                    <td>R$ ${parcela.valor_parcela}</td>
                                    <td>
                                        <button class="pay-btn" 
                                            data-index="${index}" 
                                            data-proposta="${numeroProposta}"
                                            data-valor="${parcela.valor_parcela}"
                                            data-data="${parcela.dia_pagamento}"
                                            ${parcela.status_pagamento === 'Pago' ? 'disabled' : ''}>
                                            ${parcela.status_pagamento === 'Pago' ? 'Pago' : 'Pagar'}
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        tableHtml += `
                                </tbody>
                            </table>
                        `;

                        paymentDetails.html(tableHtml);
                        popup.show();
                    } else {
                        alert('Erro ao carregar detalhes do pagamento: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro ao processar a requisição.');
                }
            });
        });

        closeBtn.on("click", function() {
            popup.hide();
        });

        $(window).on("click", function(event) {
            if (event.target == popup[0]) {
                popup.hide();
            }
        });

        // Delegate event for dynamically created pay buttons
        $(document).on('click', '.pay-btn', function() {
            const button = $(this);
            const index = button.data('index');
            const numeroProposta = button.data('proposta');
            const valorParcela = button.data('valor');
            const dataPagamento = button.data('data');
            
            if (confirm('Confirmar o pagamento desta parcela?')) {
                $.ajax({
                    url: 'atualizar_pagamento.php',
                    method: 'POST',
                    data: {
                        numero_proposta: numeroProposta,
                        parcela_num: index + 1,
                        valor_parcela: valorParcela,
                        data_pagamento: dataPagamento
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const statusCell = $(`#status-${index}`);
                            statusCell.text('Pago');
                            button.prop('disabled', true).text('Pago');
                            
                            alert('Pagamento confirmado com sucesso!');
                        } else {
                            alert('Erro ao confirmar pagamento: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erro ao processar pagamento. Por favor, tente novamente.');
                    }
                });
            }
        });
    });

    </script>
    <script src="js/campo_pesquisa.js"></script>
</body>
</html>

