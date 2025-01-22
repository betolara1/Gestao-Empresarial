<?php 
include 'conexao.php';

$sql_empresa = "SELECT * FROM empresa LIMIT 1"; 
$result_empresa = $conn->query($sql_empresa);

// Verifica se encontrou registros
if ($result_empresa->num_rows > 0) {
    $empresa = $result_empresa->fetch_assoc(); // Pega o primeiro registro
} else {
    $empresa = 0; // Caso não encontre dados, inicializa como vazio
}

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        echo '<div class="message success">Imagem salva com sucesso!</div>';
    } elseif ($_GET['status'] == 'error') {
        echo '<div class="message error">Erro ao salvar imagem. Tente novamente.</div>';
    }
}

$sql_logo = "SELECT image_data FROM logos ORDER BY created_at DESC LIMIT 1";
$result_logo = $conn->query($sql_logo);
$logoImage = null;

if ($result_logo->num_rows > 0) {
    $result_logo_row = $result_logo->fetch_assoc();
    $logoImage = $result_logo_row['image_data'];
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/sidebar.css">

    <style>
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .company-logo {
            width: 150px;  /* Tamanho fixo para a área da logo */
            height: 150px;
            border-radius: 50%;  /* Torna a logo redonda */
            border: 3px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 0 auto;  /* Centraliza horizontalmente */
            position: relative;
        }

        #logo-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;  /* Mantém a proporção e cobre todo o espaço */
            border-radius: 50%;  /* Garante que a imagem também fique redonda */
        }

        #logo-placeholder {
            font-size: 24px;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        .sidebar-header h3 {
            margin: 10px 0;
            font-size: 1.2rem;
            color: white;
            text-align: center;
            width: 100%;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .nav-item {
            padding: 0 15px;
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        /* Estilos para o formulário de upload */
        #uploadForm {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        /* Animação suave ao carregar a imagem */
        #logo-preview {
            transition: opacity 0.3s ease;
        }

        /* Efeito hover na logo */
        .company-logo:hover {
            border-color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
        }
    </style>
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
                    <i class="fas fa-home"></i>
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
                    <i class="fas fa-user-plus"></i>
                    <span>Cadastrar Clientes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="cadastro_servicos.php" class="nav-link">
                    <i class="fas fa-tools"></i>
                    <span>Cadastrar Serviços</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="cadastro_despesas_fixas.php" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Despesas Fixas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="gerenciar_clientes.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Gerenciar Clientes</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="gerenciar_relatorio.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Gerenciar Relatórios</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
    </nav>

</body>
</html>