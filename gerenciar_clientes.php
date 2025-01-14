<?php
include 'conexao.php';
include 'php/gerenciar_clientes.php'
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
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>Relatório de Clientes</h1>
            
            <div class="search-container">
                <div class="search-wrapper">
                    <input type="text" id="tableSearch" placeholder="Buscar clientes..." class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <table border=1>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pessoa</th>
                        <th>Nome/Razão Social</th>
                        <th>CNPJ</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Celular</th>
                        <th>Cidade</th>
                        <th>Estado</th>
                        <th>Data Cadastro</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['id']); ?></td>
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
                                    <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12">Nenhum cliente encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
    </script>
</body>
</html>