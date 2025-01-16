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
            <div class="header-content">
                <h2>Relatório de Serviços</h2>
                <div class="search-container">
                    <input type="text" name="search" id="search" class="search-input" placeholder="Buscar serviços...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table">
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
                                <th>Detalhes</th>
                                <th>Responsável</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($servicos)): ?>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr data-proposta="<?php echo $servico['numero_proposta']; ?>">
                                        <td><?php echo htmlspecialchars($servico['numero_proposta']); ?></td>
                                        <td><?php echo htmlspecialchars($servico['cliente_nome_ou_razao']); ?></td>
                                        <td><?php echo htmlspecialchars($servico['cnpj_cpf']); ?></td>
                                        <td><?php echo empty($servico['tipos_servico']) ? 'Nenhum' : htmlspecialchars($servico['tipos_servico']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($servico['data_inicio'])); ?></td>
                                        <td><?php echo ($servico['data_termino'] && $servico['data_termino'] != '0000-00-00') ? date('d/m/Y', strtotime($servico['data_termino'])) : ''; ?></td>
                                        <td class="status-cell status-<?php echo strtolower(str_replace(' ', '-', $servico['status_servico'])); ?>">
                                            <?php echo htmlspecialchars($servico['status_servico']); ?>
                                        </td>
                                        <td class="valor">R$ <?php echo number_format($servico['valor_total'], 2, ',', '.'); ?></td>
                                        <td class="valor">R$ <?php echo number_format($servico['valor_entrada'], 2, ',', '.'); ?></td>
                                        <td class="valor">R$ <?php echo number_format($servico['valor_a_pagar'], 2, ',', '.'); ?></td>
                                        <td class="valor">R$ <?php echo number_format($servico['total_despesas'], 2, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($servico['forma_pagamento']); ?></td>
                                        <td><?php echo htmlspecialchars($servico['parcelamento']); ?></td>
                                        <td class="status-cell status-<?php echo strtolower(str_replace(' ', '-', $servico['status_pagamento'])); ?>">
                                            <?php echo htmlspecialchars($servico['status_pagamento']); ?>
                                        </td>
                                        <td class="valor valor-pago">R$ <?php echo number_format($servico['total_pago'], 2, ',', '.'); ?></td>
                                        <td class="valor valor-pendente">R$ <?php echo number_format($servico['total_pendente'], 2, ',', '.'); ?></td>
                                        <td class="proximo-pagamento">
                                            <?php 
                                            if ($servico['status_pagamento'] == 'FINALIZADO') {
                                                echo '-';
                                            } else {
                                                echo $servico['proximo_pagamento'] ? date('d/m/Y', strtotime($servico['proximo_pagamento'])) : '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn-detalhes" onclick="verDetalhes(<?php echo $servico['numero_proposta']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        </td>
                                        <td><?php echo htmlspecialchars($servico['responsavel_execucao']); ?></td>
                                        <td class="actions">
                                            <button type="button" class="btn-editar" onclick="window.location.href='editar_servico.php?id=<?php echo $servico['numero_proposta']; ?>'">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn-excluir" onclick="confirmarExclusao(<?php echo $servico['numero_proposta']; ?>)">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="20" class="text-center">Nenhum serviço encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detalhes do Pagamento</h3>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="detalhesContent"></div>
            </div>
        </div>
    </div>

    <script>
    function confirmarExclusao(numeroProposta) {
        if (confirm('Tem certeza que deseja excluir este serviço?')) {
            // Criar um formulário dinâmico para enviar via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'excluir_servico.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'numero_proposta';
            input.value = numeroProposta;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function verDetalhes(numeroProposta) {
        const modal = document.getElementById('modalDetalhes');
        modal.style.display = 'block';
        document.getElementById('detalhesContent').innerHTML = '<p>Carregando...</p>';
        
        $.ajax({
            url: 'get_payment_details.php',
            method: 'GET',
            data: { numero_proposta: numeroProposta },
            dataType: 'json',
            success: function(response) {
                try {
                    if (response.success && response.parcelas && response.parcelas.length > 0) {
                        let html = `
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Parcela</th>
                                        <th>Status</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        response.parcelas.forEach((parcela) => {
                            const statusClass = parcela.status_pagamento.toLowerCase();
                            html += `
                                <tr>
                                    <td>${parcela.parcela_num}</td>
                                    <td class="status-cell status-${statusClass}">${parcela.status_pagamento}</td>
                                    <td>${parcela.dia_pagamento}</td>
                                    <td class="valor">R$ ${parcela.valor_parcela}</td>
                                    <td>
                                        <button class="btn-primary" 
                                                ${parcela.status_pagamento.toLowerCase() === 'pago' ? 'disabled' : ''} 
                                                onclick="confirmarPagamento(${numeroProposta}, ${parcela.parcela_num}, '${parcela.valor_parcela}', '${parcela.dia_pagamento}')">
                                            ${parcela.status_pagamento.toLowerCase() === 'pago' ? 'Pago' : 'Pagar'}
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        html += `</tbody></table>`;
                        document.getElementById('detalhesContent').innerHTML = html;
                    } else {
                        document.getElementById('detalhesContent').innerHTML = '<p>Nenhuma parcela encontrada para este serviço.</p>';
                    }
                } catch (e) {
                    console.error('Erro ao processar resposta:', e);
                    console.error('Resposta recebida:', response);
                    document.getElementById('detalhesContent').innerHTML = '<p>Erro ao processar dados do pagamento.</p>';
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição:', error);
                console.error('Status:', status);
                console.error('Resposta:', xhr.responseText);
                document.getElementById('detalhesContent').innerHTML = '<p>Erro ao carregar detalhes do pagamento.</p>';
            }
        });
    }

    function fecharModal() {
        document.getElementById('modalDetalhes').style.display = 'none';
    }

    // Fechar modal quando clicar fora
    window.onclick = function(event) {
        const modal = document.getElementById('modalDetalhes');
        if (event.target == modal) {
            fecharModal();
        }
    }

    function confirmarPagamento(numeroProposta, parcela, valor, data) {
        if (confirm('Confirmar o pagamento desta parcela?')) {
            $.ajax({
                url: 'atualizar_pagamento.php',
                method: 'POST',
                data: {
                    numero_proposta: numeroProposta,
                    parcela_num: parcela,
                    valor_parcela: valor,
                    data_pagamento: data
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Atualiza o status e o botão da parcela
                        const linha = $(`button[onclick*="confirmarPagamento(${numeroProposta}, ${parcela})"]`).closest('tr');
                        linha.find('.status-cell').removeClass('status-aberto').addClass('status-pago').text('Pago');
                        linha.find('button').prop('disabled', true).text('Pago');

                        // Atualiza os valores na tabela principal
                        const linhaServico = $(`tr[data-proposta="${numeroProposta}"]`);
                        linhaServico.find('.valor-pago').text(`R$ ${formatarMoeda(response.total_pago)}`);
                        linhaServico.find('.valor-pendente').text(`R$ ${formatarMoeda(response.total_pendente)}`);
                        
                        // Atualiza o próximo pagamento
                        if (response.proximo_pagamento) {
                            linhaServico.find('.proximo-pagamento').text(response.proximo_pagamento);
                        } else {
                            linhaServico.find('.proximo-pagamento').text('-');
                        }

                        // Verifica se todas as parcelas foram pagas
                        if (parseFloat(response.total_pendente) <= 0) {
                            // Atualiza o status de pagamento para FINALIZADO
                            const statusCell = linhaServico.find('.status-pagamento');
                            statusCell
                                .removeClass('status-pendente status-aberto')
                                .addClass('status-finalizado')
                                .text('FINALIZADO');

                            // Atualiza a célula de próximo pagamento
                            linhaServico.find('.proximo-pagamento').text('-');
                        }

                        // Recarrega os detalhes do pagamento
                        verDetalhes(numeroProposta);
                    } else {
                        alert('Erro ao confirmar pagamento: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', error);
                    alert('Erro ao processar pagamento');
                }
            });
        }
    }

    // Função auxiliar para formatar valores monetários
    function formatarMoeda(valor) {
        return parseFloat(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    </script>
</body>
</html>

<style>
/* Estilos do Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-body {
    padding: 20px;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

/* Estilo do botão de detalhes */
.btn-detalhes {
    background-color: #17a2b8;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-detalhes:hover {
    background-color: #138496;
}
</style>

