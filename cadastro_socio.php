<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
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
            <div class="card">
                <h1>Cadastro de Sócios</h1>
                <form action="salvar_socios.php" method="POST">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome:</label>
                            <input type="text" name="nome" required>
                        </div>
                        <div class="form-group"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Porcentagem Sociedade:</label>
                            <input type="number" step="0.01" name="porcentagem_sociedade" required>
                        </div>
                        <div class="form-group"></div>
                    </div>
                        
                    <div class="form-row">
                        <div class="form-group">
                            <label>Porcentagem Comissão:</label>
                            <input type="number" step="0.01" name="porcentagem_comissao" required>
                        </div>
                        <div class="form-group"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>