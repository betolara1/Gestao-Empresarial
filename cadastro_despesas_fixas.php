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
    <style>
        /* Estilos para o formulário */
        .form-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eef2f7;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label.required:after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dce0e4;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            margin-top: -30px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .total-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .popup-content h2 {
            margin: 0;
            padding: 20px;
            background-color: #3498db;
            color: white;
            border-radius: 8px 8px 0 0;
            font-size: 1.2rem;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap; /* Permite que os checkboxes se movam para a próxima linha se não houver espaço */
            gap: 1rem; /* Espaçamento entre os checkboxes */
            margin-top: 0.5rem; /* Espaçamento acima do grupo de checkboxes */
        }

        .meses-container label {
            display: flex;
            align-items: center; /* Alinha o texto e o checkbox verticalmente */
            cursor: pointer; /* Muda o cursor para indicar que é clicável */
            font-size: 0.95rem; /* Tamanho da fonte */
            color: var(--text-color); /* Cor do texto */
        }

        .meses-container input[type="checkbox"] {
            margin-right: 0.5rem; /* Espaçamento entre o checkbox e o texto */
            transform: scale(1.2); /* Aumenta o tamanho do checkbox */
            cursor: pointer; /* Muda o cursor para indicar que é clicável */
        }

        /* Efeito de foco para acessibilidade */
        .meses-container input[type="checkbox"]:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.3); /* Sombra ao focar no checkbox */
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            color: white;
            cursor: pointer;
            z-index: 1;
            transition: transform 0.2s;
        }

        .close:hover {
            transform: scale(1.1);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        /* Estilo para os botões dentro do popup */
        .popup-content .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .popup-content .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
        }

        .popup-content .btn-primary:hover {
            background-color: #2980b9;
        }

        .popup-content .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
        }

        .popup-content .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <!-- Seção de Cadastro -->
            <div class="form-section">
                <h2>Cadastro de Despesas Fixas</h2>
                <form action="salvar_despesa_fixa.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mes" class="required">Mês</label>
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
                            <label for="ano" class="required">Ano</label>
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

                    <div class="form-row">
                        <div class="form-group">
                            <label for="descricao" class="required">Descrição da despesa</label>
                            <input type="text" name="descricao" id="descricao" required>
                        </div>
                        <div class="form-group">
                            <label for="valor" class="required">Valor</label>
                            <div class="input-money">
                                <input type="number" name="valor" id="valor" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Adicionar Despesa
                        </button>
                    </div>
                </form>
            </div>

            <!-- Seção de Listagem -->
            <div class="form-section">
                <h2>Despesas Fixas Cadastradas</h2>
                <div class="table-responsive">
                    <table id="tabelaDespesas">
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
                </div>
                <div class="total-box">
                    <strong>Total do Mês: </strong>
                    <span id="total-despesas">R$ 0,00</span>
                </div>
            </div>

            <!-- Seção de Exportação -->
            <div class="form-section">
                <h2>Exportar Despesas</h2>
                <form id="exportForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exportType">Tipo de Exportação</label>
                            <select name="exportType" id="exportType" required>
                                <option value="month">Mês Específico</option>
                                <option value="year">Ano Completo</option>
                                <option value="total">Total Geral</option>
                            </select>
                        </div>
                        <div class="form-group" id="exportMonthGroup">
                            <label for="exportMonth">Mês</label>
                            <select name="exportMonth" id="exportMonth">
                                <!-- Options mantidas como estão -->
                            </select>
                        </div>
                        <div class="form-group" id="exportYearGroup">
                            <label for="exportYear">Ano</label>
                            <select name="exportYear" id="exportYear">
                                <!-- Preenchido via JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> Exportar para PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Replicação -->
    <div id="replicarModal" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2>Replicar Despesa</h2>
            <div class="form-group">
                <p>Selecione os meses para replicar:</p>
                <div class="checkbox-group meses-container">
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
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmarReplicacao()">Confirmar</button>
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
                                    <tr id="linha-${despesa.id}">
                                        <td>${despesa.descricao}</td>
                                        <td class="valor-despesa">R$ ${parseFloat(despesa.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                                        <td>
                                            <div class="btn-actions">
                                                <button type="button" class="btn btn-danger" onclick="excluirDespesa(${despesa.id})">
                                                    <i class="fas fa-trash"></i> 
                                                </button>
                                                <button type="button" class="btn-replicar" onclick="abrirModalReplicar(${despesa.id})">
                                                    <i class="fas fa-copy"></i> 
                                                </button>
                                            </div>
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

        // Preencher o select de meses
        const meses = [
            { value: "01", text: "Janeiro" },
            { value: "02", text: "Fevereiro" },
            { value: "03", text: "Março" },
            { value: "04", text: "Abril" },
            { value: "05", text: "Maio" },
            { value: "06", text: "Junho" },
            { value: "07", text: "Julho" },
            { value: "08", text: "Agosto" },
            { value: "09", text: "Setembro" },
            { value: "10", text: "Outubro" },
            { value: "11", text: "Novembro" },
            { value: "12", text: "Dezembro" }
        ];

        // Preencher o select de meses
        const $exportMonth = $('#exportMonth');
        meses.forEach(mes => {
            $exportMonth.append(new Option(mes.text, mes.value));
        });

        // Inicializar o select de anos
        const currentYear = new Date().getFullYear();
        for (let i = currentYear - 5; i <= currentYear + 5; i++) {
            $('#exportYear').append($('<option>', {
                value: i,
                text: i
            }));
        }

        // Setar mês e ano atuais como padrão
        const currentMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
        $('#exportMonth').val(currentMonth);
        $('#exportYear').val(currentYear);

        // Trigger change event to set initial visibility
        $('#exportType').trigger('change');
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
    function excluirDespesa(despesaId) {
        $.ajax({
            url: 'excluir_despesa_fixa.php',
            type: 'POST',
            data: { id: despesaId },
            success: function(response) {
                const data = JSON.parse(response); // Certifique-se de que a resposta seja analisada corretamente
                if (data.success) {
                    // Remover a linha da tabela
                    $(`#linha-${despesaId}`).remove(); // Supondo que você tenha um ID de linha

                    // Atualizar o total
                    atualizarTotalDespesas();
                    alert(data.message); // Exibir mensagem de sucesso
                } else {
                    alert('Erro ao excluir despesa: ' + data.message);
                }
            },
            error: function() {
                alert('Erro ao realizar a requisição.');
            }
        });
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

    function atualizarTotalDespesas() {
        let total = 0;

        // Iterar sobre as linhas da tabela e somar os valores
        $('#tabelaDespesas tbody tr').each(function() {
            const valor = parseFloat($(this).find('.valor-despesa').text().replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;
            total += valor;
        });

        // Atualizar o total no DOM
        $('#total-despesas').text('R$ ' + total.toFixed(2).replace('.', ','));
    }
    </script>

</body>
</html>
