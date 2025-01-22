<?php
include 'conexao.php';

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
    <title>Cadastrar Serviço</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #838282;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --sidebar-width: 250px;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --primary-dark: #1e40af;
            --background-color: #ffffff;
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 2rem auto;
        }

        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        h2 {
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background-color: var(--accent-color);
            border-radius: 2px;
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }

        .form {
            padding: 20px; /* Espaçamento interno */
            border-radius: 8px; /* Bordas arredondadas */
            background: #f8f9fa; /* Fundo suave para o formulário */
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1); /* Sombra leve */
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            transition: border-color 0.2s; /* Transição suave para a borda */
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus {
            border-color: var(--accent-color); /* Cor da borda ao focar */
            outline: none; /* Remove o contorno padrão */
        }

        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px; /* Espaçamento entre os botões */
        }

        .btn {
            padding: 10px 20px; /* Aumenta o padding para um botão mais espaçoso */
            border-radius: 5px; /* Bordas arredondadas */
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s; /* Transições suaves */
        }

        .btn-primary {
            background: #007bff; /* Cor do botão primário */
            color: white; /* Cor do texto */
        }

        .btn-primary:hover {
            background: #0056b3; /* Cor ao passar o mouse */
        }

        .btn-danger {
            background: #dc3545; /* Cor do botão de perigo */
            color: white; /* Cor do texto */
        }

        .btn-danger:hover {
            background: #c82333; /* Cor ao passar o mouse */
        }
    </style>
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
            // Consolidar a função de busca de CEP e preenchimento de endereço
            function buscarEPreencherEndereco(cep) {
                var validacep = /^[0-9]{8}$/;
                if (validacep.test(cep)) {
                    $("#rua").val("...");
                    $("#bairro").val("...");
                    $("#cidade").val("...");
                    $("#estado").val("...");
                    $("#coordenada").val("Buscando coordenadas...");

                    $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(dados) {
                        if (!("erro" in dados)) {
                            preencheCamposEndereco(dados);
                            buscarCoordenadas(dados);
                        } else {
                            limpaFormularioCep();
                            alert("CEP não encontrado.");
                        }
                    }).fail(function() {
                        limpaFormularioCep();
                        alert("Erro ao buscar CEP. Tente novamente mais tarde.");
                    });
                } else {
                    limpaFormularioCep();
                    alert("Formato de CEP inválido.");
                }
            }

            // Evento para o campo de CEP
            $("#cep").on('blur change', function() {
                var cep = $(this).val().replace(/\D/g, '');
                if (cep.length === 8) {
                    buscarEPreencherEndereco(cep);
                } else {
                    limpaFormularioCep();
                }
            });

            // Consolidar a lógica de atualização de status do serviço
            function atualizarStatusServico() {
                const dataInicio = $('#data_inicio').val();
                const dataTermino = $('#data_termino').val();
                const statusServico = $('#status_servico');
                const hoje = new Date().toISOString().split('T')[0];

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

                if (!dataInicio) {
                    statusServico.val('');
                } else if (dataTermino) {
                    statusServico.val('CONCLUIDO');
                } else {
                    statusServico.val('EM ANDAMENTO');
                }
            }

            // Adiciona os event listeners
            $('#data_inicio, #data_termino').on('change', atualizarStatusServico);
            atualizarStatusServico(); // Inicializa o status ao carregar a página
        });

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

        //RETORNA O CPF/CNPJ DO CLIENTE SELECIONADO
        function buscarCNPJCPF(clienteId) {
            if (clienteId) {
                const formData = new FormData();
                formData.append("buscar_cliente", true);
                formData.append("cliente_id", clienteId);

                fetch("cadastro_servicos.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("cnpj_cpf").value = data.cnpj ? data.cnpj : data.cpf;
                })
                .catch(error => console.error('Erro ao buscar CNPJ/CPF:', error));
            }
        }
    </script>
</body>
</html>