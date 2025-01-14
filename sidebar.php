<?php 
include 'conexao.php';
include 'php/sidebar.php'
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Serviços</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="company-logo">
                    <input type="file" name="logo" id="logo-input" accept="image/*" onchange="previewImage(event)" style="display: none;">
                    <?php if ($logoImage): ?>
                        <img id="logo-preview" 
                            src="data:image/jpeg;base64,<?php echo base64_encode($logoImage); ?>" 
                            alt="Logo" />
                    <?php else: ?>
                        <div id="logo-placeholder">LOGO</div>
                    <?php endif; ?>
                </div>
            </form>
            <h3><?php echo htmlspecialchars($empresa['razao_social'] ?? 'Empresa'); ?></h3>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-building"></i>
                    <span>Página Inicial</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="cadastro_empresa.php" class="nav-link">
                    <i class="fas fa-building"></i>
                    <span>Minha Empresa</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="cadastro_cliente.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Cadastrar Clientes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="cadastro_servicos.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Cadastrar Serviços</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="cadastro_despesas_fixas.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Despesas Fixas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="gerenciar_clientes.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Gerenciar Clientes</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="gerenciar_relatorio.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Gerenciar Relatórios</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
    </nav>

</body>
</html>