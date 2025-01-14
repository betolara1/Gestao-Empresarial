<?php 
include 'conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>   
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Painel de Controle</h1>
        </div>
        
        <div class="cards-container">
            <a href="gerenciar_clientes.php" class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Clientes</h3>
                </div>
                <div class="card-content">
                    <p>Gerencie seus clientes de forma eficiente e mantenha seus registros atualizados.</p>
                </div>
            </a>

            <a href="dashboard.php" class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-dashboard"></i>
                    </div>
                    <h3 class="card-title">Dashboard</h3>
                </div>
                <div class="card-content">
                    <p>Visualize e acompanhe receitas, despesas e fluxo de caixa em tempo real.</p>
                </div>
            </a>

            <a href="cadastro_despesas_fixas.php" class="card">
                <div class="card-header">
                    <div class="card-icon"> 
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="card-title">Despesas Fixas</h3>
                </div>
                <div class="card-content">
                    <p>Controle e gerencie suas despesas mensais fixas de forma organizada.</p>
                </div>
            </a>

            
            <a href="gerenciar_relatorio.php" class="card">
                <div class="card-header">
                    <div class="card-icon"> 
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="card-title">Gerenciar Relatórios</h3>
                </div>
                <div class="card-content">
                    <p>Controle e gerencie por aqui seus relatórios de serviço.</p>
                </div>
            </a>
        </div>
    </main>
</body>
</html>