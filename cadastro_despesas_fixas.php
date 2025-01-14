<?php 
include 'conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Despesas</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="form-row">
                <div class="form-group">
                    <h1>Cadastro de Despesas Fixas</h1>
                    <form action="salvar_despesa_fixa.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="mes">Mês:</label>
                                <select name="mes" id="mes" required>
                                    <option value="">Selecione...</option>
                                    <option value="01">Janeiro</option>
                                    <option value="02">Fevereiro</option>
                                    <option value="03">Março</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Maio</option>
                                    <option value="06">Junho</option>
                                    <option value="07">Julho</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Setembro</option>
                                    <option value="10">Outubro</option>
                                    <option value="11">Novembro</option>
                                    <option value="12">Dezembro</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ano">Ano:</label>
                                <select name="ano" id="ano" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $anoAtual = date('Y');
                                    for($i = $anoAtual - 5; $i <= $anoAtual + 5; $i++) {
                                        echo "<option value='$i'" . ($i == $anoAtual ? " selected" : "") . ">$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <label for="descricao">Descrição da despesa:</label>
                        <input type="text" name="descricao" id="descricao" required>

                        <label for="valor">Valor:</label>
                        <input type="number" name="valor" id="valor" step="0.01" required>

                        <label></label>
                        <label></label>
                        <div class="form-row">
                            <div class="form-group">
                                <input class="btn" type="submit" value="Adicionar Despesa">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <h1>Despesas Fixas</h1>
                        <br>
                        <table border="1">
                            <thead>
                                <tr>
                                    <th>Nome da Despesa</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- As despesas serão carregadas via JavaScript -->
                            </tbody>
                        </table>
                        <div class="total-box">
                            <strong>Total do Mês: </strong>
                            <span id="total-despesas">R$ 0,00</span>
                        </div>
                    </div>

                    <!-- Modal para replicar despesas -->
                    <div id="replicarModal" class="modal" style="display: none;">
                        <div class="modal-content">
                            <h2>Replicar Despesa</h2>
                            <p>Selecione os meses para replicar:</p>
                            <div class="meses-container">
                                <label><input type="checkbox" value="01"> Janeiro</label>
                                <label><input type="checkbox" value="02"> Fevereiro</label>
                                <label><input type="checkbox" value="03"> Março</label>
                                <label><input type="checkbox" value="04"> Abril</label>
                                <label><input type="checkbox" value="05"> Maio</label>
                                <label><input type="checkbox" value="06"> Junho</label>
                                <label><input type="checkbox" value="07"> Julho</label>
                                <label><input type="checkbox" value="08"> Agosto</label>
                                <label><input type="checkbox" value="09"> Setembro</label>
                                <label><input type="checkbox" value="10"> Outubro</label>
                                <label><input type="checkbox" value="11"> Novembro</label>
                                <label><input type="checkbox" value="12"> Dezembro</label>
                            </div>
                            <button class="btn" onclick="confirmarReplicacao()">Confirmar</button>
                            <button class="btn" onclick="fecharModal()">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group">
                    <h2>Exportar Despesas para PDF</h2>
                    <form id="exportForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="exportType">Tipo de Exportação:</label>
                                <select name="exportType" id="exportType" required>
                                    <option value="month">Mês Específico</option>
                                    <option value="year">Ano Completo</option>
                                    <option value="total">Total Geral</option>
                                </select>
                            </div>
                            <div class="form-group" id="exportMonthGroup">
                                <label for="exportMonth">Mês:</label>
                                <select name="exportMonth" id="exportMonth">
                                    <option value="01">Janeiro</option>
                                    <option value="02">Fevereiro</option>
                                    <option value="03">Março</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Maio</option>
                                    <option value="06">Junho</option>
                                    <option value="07">Julho</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Setembro</option>
                                    <option value="10">Outubro</option>
                                    <option value="11">Novembro</option>
                                    <option value="12">Dezembro</option>
                                </select>
                            </div>
                            <div class="form-group" id="exportYearGroup">
                                <label for="exportYear">Ano:</label>
                                <select name="exportYear" id="exportYear">
                                    <!-- Years will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                        <button type="button" class="btn" onclick="exportToPDF()">Exportar para PDF</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
    let despesaParaReplicar = null;
    $(document).ready(function() {
        function buscarDespesas() {
            const mes = $('#mes').val();
            const ano = $('#ano').val();
            
            if (mes && ano) {
                $.ajax({
                    url: 'buscar_despesas.php',
                    type: 'GET',
                    data: { 
                        mes: mes,
                        ano: ano 
                    },
                    success: function(despesas) {
                        let html = '';
                        let total = 0;
                        
                        if (despesas.length > 0) {
                            despesas.forEach(function(despesa) {
                                total += parseFloat(despesa.valor);
                                html += `
                                    <tr id="row-${despesa.id}">
                                        <td>${despesa.descricao}</td>
                                        <td>R$ ${parseFloat(despesa.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                                        <td>
                                            <button type="button" class="btn-excluir" onclick="excluirDespesa(${despesa.id})">
                                                Excluir
                                            </button>
                                            <button type="button" class="btn-replicar" onclick="abrirModalReplicar(${despesa.id})">
                                                Replicar
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="5">Nenhuma despesa encontrada para o período selecionado.</td></tr>';
                        }
                        $('table tbody').html(html);
                        $('#total-despesas').text(`R$ ${total.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`);
                    },
                    error: function() {
                        alert('Erro ao buscar as despesas.');
                    }
                });
            }
        }

        // Atualiza a tabela quando mês ou ano são alterados
        $('#mes, #ano').change(buscarDespesas);
    });

    function abrirModalReplicar(despesaId) {
        despesaParaReplicar = despesaId;
        document.getElementById('replicarModal').style.display = 'block';
    }

    function fecharModal() {
        document.getElementById('replicarModal').style.display = 'none';
        despesaParaReplicar = null;
    }

    function confirmarReplicacao() {
        const mesesSelecionados = [];
        document.querySelectorAll('.meses-container input:checked').forEach(checkbox => {
            mesesSelecionados.push(checkbox.value);
        });

        if (mesesSelecionados.length === 0) {
            alert('Selecione pelo menos um mês para replicar.');
            return;
        }

        $.ajax({
            url: 'replicar_despesa.php',
            type: 'POST',
            dataType: 'json',
            data: {
                despesa_id: despesaParaReplicar,
                meses: mesesSelecionados,
                ano: $('#ano').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    fecharModal();
                    buscarDespesas(); // Atualiza a lista
                } else {
                    alert('Erro: ' + (response.error || 'Erro ao replicar despesa'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição:', xhr.responseText);
                alert('Erro ao replicar a despesa. Verifique o console para mais detalhes.');
            }
        });
    }
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


    // Add this new function for PDF export
    function exportToPDF() {
        const exportType = $('#exportType').val();
        const exportMonth = $('#exportMonth').val();
        const exportYear = $('#exportYear').val();

        $.ajax({
            url: 'gerar_pdf.php',
            type: 'POST',
            data: {
                exportType: exportType,
                exportMonth: exportMonth,
                exportYear: exportYear
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response) {
                const blob = new Blob([response], { type: 'application/pdf' });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'despesas.pdf';
                link.click();
            },
            error: function() {
                alert('Erro ao gerar o PDF.');
            }
        });
    }

    // Show/hide month and year selects based on export type
    $('#exportType').change(function() {
        const exportType = $(this).val();
        if (exportType === 'month') {
            $('#exportMonthGroup, #exportYearGroup').show();
        } else if (exportType === 'year') {
            $('#exportMonthGroup').hide();
            $('#exportYearGroup').show();
        } else {
            $('#exportMonthGroup, #exportYearGroup').hide();
        }
    });

    // Initialize the export form
    $(document).ready(function() {
        // Populate export year select
        const currentYear = new Date().getFullYear();
        for (let i = currentYear - 5; i <= currentYear + 5; i++) {
            $('#exportYear').append($('<option>', {
                value: i,
                text: i
            }));
        }

        // Set current month and year as default
        const currentMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
        $('#exportMonth').val(currentMonth);
        $('#exportYear').val(currentYear);

        // Trigger change event to set initial visibility
        $('#exportType').trigger('change');
    });
    </script>

</body>
</html>
