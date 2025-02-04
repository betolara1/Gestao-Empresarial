<?php
include 'conexao.php';

// Consulta SQL para buscar os clientes
$sql = "SELECT 
            id,
            tipo_pessoa,
            CASE 
                WHEN tipo_pessoa = 'F' THEN nome
                WHEN tipo_pessoa = 'J' THEN razao_social
                ELSE 'Não especificado'
            END AS cliente_nome_ou_razao,
            cnpj,
            cpf,
            cep,
            rua,
            numero,
            complemento,
            bairro,
            cidade,
            estado,
            telefone,
            celular,
            email,
            codigo_cnae,
            data_cadastro
        FROM cliente";

$result = $conn->query($sql);

// Armazena os dados dos clientes em um array
if ($result->num_rows > 0) {
    $clientes = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $clientes = []; // Array vazio caso não haja clientes
}

if (isset($_GET['mensagem'])) {
    echo "<p style='color: green; font-weight: bold; text-align: center;'>" . htmlspecialchars($_GET['mensagem']) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Clientes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        .container {
            max-width: 1200px;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 2rem auto;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            cursor: pointer;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden; /* Para bordas arredondadas */
        }

        th, td {
            padding: 10px; /* Aumenta o espaçamento */
            text-align: center;
            border: 1px solid var(--border-color);
            width: 10%; /* Define uma largura mínima para as colunas */
            white-space: nowrap; /* Impede a quebra de linha */
            overflow: hidden; /* Oculta o texto que excede a largura da célula */
            text-overflow: ellipsis; /* Adiciona reticências (...) para texto que não cabe */
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; /* Cor de fundo alternada para linhas */
        }

        tr:hover {
            background-color: #e9ecef; /* Cor de fundo ao passar o mouse */
        }

        .btn-editar, .btn-excluir {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 0.9rem;
        }

        .btn-editar {
            background-color: #3498db;
        }

        .btn-editar:hover {
            background-color: #2980b9;
        }

        .btn-excluir {
            background-color: #e74c3c;
        }

        .btn-excluir:hover {
            background-color: #c0392b;
        }

        .no-data {
            text-align: center;
            color: #999;
        }

        .no-results {
            text-align: center;
            color: #999;
        }

        #tabelaClientes th i {
            margin-right: 8px;
            color: #666;
        }
        
        #tabelaClientes th {
            white-space: nowrap;
            padding: 12px 15px;
        }
        
        #tabelaClientes th:hover i {
            color: #007bff;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="form-section">
                <h2>Relatório de Clientes</h2>
                
                <div class="search-container">
                    <div class="search-wrapper">
                        <input type="text" id="tableSearch" placeholder="Buscar clientes..." class="search-input">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tabelaClientes">
                        <thead>
                            <tr>
                                <th></th>
                                <th><i class="fas fa-user-circle"></i> Pessoa</th>
                                <th><i class="fas fa-building"></i> Nome/Razão Social</th>
                                <th><i class="fas fa-briefcase"></i> CNPJ</th>
                                <th><i class="fas fa-id-card"></i> CPF</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-mobile-alt"></i> Celular</th>
                                <th><i class="fas fa-city"></i> Cidade</th>
                                <th><i class="fas fa-map-marker-alt"></i> Estado</th>
                                <th><i class="fas fa-calendar-alt"></i> Data Cadastro</th>
                                <th><i class="fas fa-cogs"></i> Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $cliente['tipo_pessoa'] === 'F' ? 'Física' : 'Jurídica'
                                            );
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($cliente['cliente_nome_ou_razao']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['cnpj']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['cpf']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['celular']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['cidade']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['estado']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?></td>
                                        <td>
                                            <button type="button" class="btn-editar" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-edit"></i> 
                                            </button>
                                            <button type="button" class="btn-excluir" onclick="confirmarExclusao(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-trash"></i> 
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="no-data">Nenhum cliente encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#tableSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                
                // Get all table rows except the header
                var rows = $("table tbody tr");
                var hasResults = false;

                rows.each(function() {
                    var rowText = $(this).text().toLowerCase();
                    var match = rowText.indexOf(value) > -1;
                    $(this).toggle(match);
                    
                    if (match) {
                        hasResults = true;
                    }
                });

                // Handle no results
                var noResultsRow = $("table tbody tr.no-results");
                if (!hasResults && value !== "") {
                    if (noResultsRow.length === 0) {
                        $("table tbody").append(
                            '<tr class="no-results"><td colspan="11" class="no-results">Nenhum resultado encontrado</td></tr>'
                        );
                    }
                    noResultsRow.show();
                } else {
                    noResultsRow.remove();
                }
            });

            // Add clear search functionality when search icon is clicked
            $(".search-icon").click(function() {
                $("#tableSearch").val("").trigger("keyup");
            });
        });

        function editarCliente(id) {
            // Redireciona para a página de edição
            window.location.href = 'editar_cliente.php?id=' + id;
        }

        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja excluir este cliente? Todos os serviços relacionados também serão excluídos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Excluindo...',
                        text: 'Aguarde enquanto o cliente é excluído',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            window.location.href = 'excluir_cliente.php?id=' + id;
                        }
                    });
                }
            });
        }
    </script>

</body>
</html>
