<?php
include 'conexao.php';

$dados = [
    "numero_proposta" => "105",
    "tipos_servico" => [
        ["id" => "5", "tipo_servico" => "Informatica"],
        ["id" => "6", "tipo_servico" => "Programador"]
    ],
    "despesas" => []
];

// Armazene os dados em variáveis PHP em vez de imprimir diretamente
$numero_proposta = $dados['numero_proposta'];
$tipos_servico = $dados['tipos_servico'];
$despesas = $dados['despesas'];

// Verifica se é uma requisição AJAX para buscar dados do cliente
if (isset($_POST['buscar_cliente']) && isset($_POST['cliente_id'])) {
    $cliente_id = $_POST['cliente_id'];
    $sql_cliente = "SELECT cnpj, cpf FROM cliente WHERE id = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente = $result_cliente->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($cliente);
    $stmt_cliente->close();
    exit;
}

try {
    // Busca o último número da proposta e incrementa para o próximo
    $sql_proposta = "SELECT COALESCE(MAX(numero_proposta), 0) + 1 AS proximo_numero FROM servicos FOR UPDATE";
    $result_proposta = $conn->query($sql_proposta);
    $row_proposta = $result_proposta->fetch_assoc();
    $numero_proposta = $row_proposta['proximo_numero'];

    // Consulta SQL para buscar todos os tipos de serviços
    $sqlTipoServico = "SELECT id, tipo_servico FROM tipos_servicos";
    $resultTipoServico = $conn->query($sqlTipoServico);
    $tipos_servico = $resultTipoServico->fetch_all(MYSQLI_ASSOC);

    // Buscar despesas existentes
    $sql = "SELECT id, nome_despesa, valor FROM despesas WHERE proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_proposta);
    $stmt->execute();
    $result = $stmt->get_result();
    $despesas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Commit a transação
    $conn->commit();

    // Se for uma requisição AJAX, retorna JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'numero_proposta' => $numero_proposta,
            'tipos_servico' => $tipos_servico,
            'despesas' => $despesas
        ]);
        exit;
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Serviços</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
                <!-- Popup -->
            <div id="popup" class="popup" style="display: none;">
                <div class="popup-content">
                    <span class="close" onclick="closePopup()">&times;</span>
                    <h2>Adicionar Nova Despesa</h2>
                    <form id="despesaForm">
                        <label for="nome_despesa">Nome da Despesa:</label>
                        <input type="text" id="nome_despesa" name="nome_despesa" required>

                        <label for="valor_despesa">Valor:</label>
                        <input type="number" id="valor_despesa" name="valor_despesa" step="0.01" min="0" required>
                        <label></label>
                        <button class="btn" type="submit">Salvar</button>
                    </form>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                <h2>Cadastro de Despesas</h2>
                    <!-- Tabela -->
                    <table id="tabelaDespesas" boarder="1">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Valor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr id='row-" . $row['id'] . "'>";
                                        echo "<td>" . htmlspecialchars($row['nome_despesa']) . "</td>";
                                        echo "<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
                                        echo "<td>";
                                        echo "<button type='button' class='btn-excluir' onclick='excluirDespesa(" . $row['id'] . ")'>Excluir</button>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                    <button class="btn" onclick="openPopup()">Adicionar Despesa</button>
                </div>  
                <div class="form-group"></div>
            </div>

            <br><br><br>
            <h1>Cadastro de Serviços</h1>
            <form action="salvar_servico.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_proposta">Número da Proposta</label>
                        <input type="text" id="numero_proposta" name="numero_proposta" value="<?php echo htmlspecialchars($numero_proposta); ?>">
                    </div>
                    <div class="form-group">
                        <label for="cliente" class="required">Cliente</label>
                        <select id="cliente" name="cliente" onchange="buscarCNPJCPF(this.value)" required>
                            <option value="">Selecione...</option>
                            <?php
                            $clientes = $conn->query("SELECT id, IFNULL(razao_social, nome) AS nome FROM cliente");
                            while ($cliente = $clientes->fetch_assoc()) {
                                echo "<option value='{$cliente['id']}'>{$cliente['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cnpj_cpf" class="required">CNPJ/CPF</label>
                            <input type="text" id="cnpj_cpf" name="cnpj_cpf" readonly>
                        </div>
                    </div>
                    <div class="form-group"></div>
                    <div class="form-group"></div>
                </div>
                

                <label>Tipos de Serviço:</label>
                <div class="checkbox-group">
                    <?php foreach ($tipos_servico as $servico): ?>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' 
                                name='tipo_servico[]' 
                                id='servico_<?php echo htmlspecialchars($servico['id']); ?>' 
                                value='<?php echo htmlspecialchars($servico['id']); ?>'>
                            <label class='form-check-label' 
                                for='servico_<?php echo htmlspecialchars($servico['id']); ?>'>
                                <?php echo htmlspecialchars($servico['tipo_servico']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <br><br>

                <label>Endereço do Serviço</label>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cep" class="required">CEP:</label>
                        <input type="text" id="cep" name="cep" required placeholder="00000-000">
                        <small id="cep-feedback" class="form-text"></small>
                    </div>
                    <div class="form-group">
                        <label for="rua">Rua:</label>
                        <input type="text" id="rua" name="rua" readonly placeholder="Endereço">
                    </div>
                    <div class="form-group">
                        <label for="numero" class="required">Número:</label>
                        <input type="text" id="numero" name="numero" required placeholder="Número">
                    </div>
                    <div class="form-group">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" placeholder="Apartamento, sala, etc.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" readonly placeholder="Bairro">
                    </div>
                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" readonly placeholder="Cidade">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" readonly placeholder="Estado">
                    </div>
                    <div class="form-group">
                        <label for="coordenada">Coordenada:</label>
                        <input type="text" id="coordenada" name="coordenada" placeholder="Latitude, Longitude">
                        <small id="coordenadas-feedback" class="form-text"></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data_inicio" class="required">Data de Início do Serviço</label>
                        <input type="date" id="data_inicio" name="data_inicio" required>
                    </div>

                    <div class="form-group">
                        <label for="data_termino">Data de Término do Serviço</label>
                        <input type="date" id="data_termino" name="data_termino">
                    </div>

                    <div class="form-group">
                        <label for="status_servico">Status do Serviço</label>
                        <input type="text" id="status_servico" name="status_servico" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="valor_total" class="required">Valor Total</label>
                        <input type="number" id="valor_total" name="valor_total" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="valor_entrada">Valor Entrada</label>
                        <input type="number" id="valor_entrada" name="valor_entrada" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="data_pagamento">Dia para Pagamento</label>
                        <input type="date" id="data_pagamento" name="data_pagamento">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="forma_pagamento" class="required">Forma de Pagamento</label>
                        <select id="forma_pagamento" name="forma_pagamento" required>
                            <option value="">Selecione a forma de pagamento</option>
                            <option value="CARTÃO DE CRÉDITO">Cartão de Crédito</option>
                            <option value="CARTÃO DE DÉBITO">Cartão de Débito</option>
                            <option value="PIX">PIX</option>
                            <option value="DINHEIRO">Dinheiro</option>
                            <option value="BOLETO">Boleto</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="parcelamento">Parcelamento</label>
                        <select id="parcelamento" name="parcelamento">
                            <option value="">Selecione o parcelamento</option>
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                echo "<option value=\"$i\">{$i}x</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group"></div>
                    
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="responsavel_execucao" class="required">Nome do Responsável pela Execução</label>
                        <input type="text" id="responsavel_execucao" name="responsavel_execucao" required>
                    </div>

                    <div class="form-group">
                        <label for="origem_demanda" class="required">Origem da Demanda</label>
                        <select id="origem_demanda" name="origem_demanda" required>
                            <option value="">Selecione...</option>
                            <option value="INTERNET">Internet</option>
                            <option value="FACEBOOK">Facebook</option>
                            <option value="INDICACAO">Indicação</option>
                            <option value="OUTRO">Outro</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="observacao">Observação:</label>
                        <textarea id="observacao" name="observacao" class="form-control" rows="4" placeholder="Digite sua observação aqui"></textarea>
                    </div>
                </div>

                <button class="btn" type="submit">Cadastrar Serviço</button>
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

        function openPopup() {
            document.getElementById('popup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        // Cadastrar Despesa (requisição AJAX)
        function cadastrarDespesa(event) {
            event.preventDefault();
            const nome = document.getElementById('nome_despesa').value;
            const valor = document.getElementById('valor_despesa').value;
            const numeroProposta = document.getElementById('numero_proposta').value;

            const formData = new FormData();
            formData.append('nome_despesa', nome);
            formData.append('valor_despesa', valor);
            formData.append('numero_proposta', numeroProposta);

            fetch('salvar_despesa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Adicionar despesa à tabela
                    const tbody = document.getElementById('tabelaDespesas').querySelector('tbody');
                    const novaLinha = document.createElement('tr');
                    novaLinha.id = `despesa-${data.id}`;

                    const nomeTd = document.createElement('td');
                    nomeTd.textContent = data.nome_despesa;

                    const valorTd = document.createElement('td');
                    valorTd.textContent = `R$ ${parseFloat(data.valor_despesa).toFixed(2)}`;

                    const acoesTd = document.createElement('td');
                    const excluirBtn = document.createElement('button');
                    excluirBtn.textContent = 'Excluir';
                    excluirBtn.className = 'btn-excluir';
                    excluirBtn.onclick = () => excluirDespesa(data.id);
                    acoesTd.appendChild(excluirBtn);

                    novaLinha.appendChild(nomeTd);
                    novaLinha.appendChild(valorTd);
                    novaLinha.appendChild(acoesTd);

                    tbody.appendChild(novaLinha);

                    // Fechar popup
                    closePopup();
                    document.getElementById('despesaForm').reset();
                } else {
                    alert(data.message || 'Erro ao cadastrar despesa.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao cadastrar despesa. Por favor, tente novamente.');
            });
        }

        // Função para excluir despesa
        function excluirDespesa(id) {
            if (confirm('Tem certeza que deseja excluir este registro?')) {
                $.ajax({
                    url: 'excluir_despesa.php',
                    type: 'POST',
                    data: { id_despesa: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Remove a linha da tabela com animação
                            $('#row-' + id).fadeOut(400, function() {
                                $(this).remove();
                            });
                            
                            // Mostra mensagem de sucesso
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro:', error);
                        alert('Erro ao excluir o registro. Tente novamente.');
                    }
                });
            }
        }


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
    <script src="js/busca_cpfcnpj.js"></script>
    <script src="js/cep.js"></script>
    <script src="js/despesas.js"></script>
    <script src="js/status_servico.js"></script>
</body>
</html>