<?php
session_start();
include 'conexao.php';

$socio_id = $_GET['id'] ?? null;
$mensagem = '';

// Busca informações do sócio
if ($socio_id) {
    $stmt = $conn->prepare("SELECT nome FROM socios WHERE id = ?");
    $stmt->bind_param("i", $socio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $socio = $result->fetch_assoc();
}

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'] ?? 0;
    $data = $_POST['data'];
    $mes = date('n', strtotime($data)); // Obtém o mês (1-12)
    $ano = date('Y', strtotime($data)); // Obtém o ano
    $tipo = 'LABORE'; // Tipo fixo para pró-labore
    
    $stmt = $conn->prepare("INSERT INTO retiradas_socios (socio_id, valor, tipo, mes, ano, data_retirada) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsiis", $socio_id, $valor, $tipo, $mes, $ano, $data);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Pró-labore registrado com sucesso!";
    } else {
        $_SESSION['mensagem'] = "Erro ao registrar pró-labore: " . $conn->error;
    }
    
    // Redireciona após o POST para evitar resubmissão
    header("Location: pro_labore.php?id=" . $socio_id);
    exit();
}

// Adicione esta parte para processar a exclusão
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['registro_id'])) {
    $registro_id = $_GET['registro_id'];
    
    $stmt = $conn->prepare("DELETE FROM retiradas_socios WHERE id = ? AND socio_id = ?");
    $stmt->bind_param("ii", $registro_id, $socio_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Registro excluído com sucesso!";
    } else {
        $_SESSION['mensagem'] = "Erro ao excluir registro: " . $conn->error;
    }
    
    header("Location: pro_labore.php?id=" . $socio_id);
    exit();
}

// Recupera a mensagem da sessão
$mensagem = $_SESSION['mensagem'] ?? '';
unset($_SESSION['mensagem']); // Limpa a mensagem da sessão

// Busca histórico de pró-labore
$stmt = $conn->prepare("SELECT * FROM retiradas_socios WHERE socio_id = ? AND tipo = 'LABORE' ORDER BY data_retirada DESC");
$stmt->bind_param("i", $socio_id);
$stmt->execute();
$historico = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pró-Labore</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .form {
            margin-bottom: 30px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
            min-width: 250px; /* Largura mínima para as colunas */
            margin-right: 15px;
        }

        .form-group:last-child {
            margin-right: 0; /* Remove margem do último item */
        }

        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-shadow: var(--shadow-md);
        }

        .btn {
            padding: 10px 20px; /* Aumenta o padding para um botão mais espaçoso */
            border-radius: 5px; /* Bordas arredondadas */
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s; /* Transições suaves */
            display: inline-flex; /* Alinha o ícone e o texto */
            align-items: center; /* Centraliza verticalmente */
        }

        .btn-primary {
            background: #007bff; /* Cor do botão primário */
            color: white;
        }

        .btn-danger {
            background: #dc3545; /* Cor de fundo do botão */
            color: white; /* Cor do texto */
        }

        .btn:hover {
            opacity: 0.9; /* Efeito de hover */
        }

        .btn-danger:hover {
            background: #c82333; /* Cor de fundo ao passar o mouse */
            transform: scale(1.05); /* Efeito de aumento ao passar o mouse */
        }

        .btn i {
            margin-right: 5px; /* Espaçamento entre o ícone e o texto */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .alert {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <h1>Gerenciar Pró-Labore - <?php echo htmlspecialchars($socio['nome'] ?? ''); ?></h1>
            
            <?php if ($mensagem): ?>
                <div class="alert"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <form method="POST" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="valor"><i class="fas fa-dollar-sign"></i> Valor do Pró-Labore:</label>
                        <input type="number" step="0.01" id="valor" name="valor" required>
                    </div>
                    <div class="form-group">
                        <label for="data"><i class="fas fa-calendar-alt"></i> Data:</label>
                        <input type="date" id="data" name="data" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Pró-Labore
                </button>
            </form>

            <h2><i class="fas fa-history"></i> Histórico de Pró-Labore</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar"></i> Data</th>
                        <th><i class="fas fa-money-bill-wave"></i> Valor</th>
                        <th><i class="fas fa-calendar-check"></i> Mês/Ano</th>
                        <th><i class="fas fa-cogs"></i> Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $historico->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['data_retirada'])); ?></td>
                            <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                            <td><?php echo $row['mes'] . '/' . $row['ano']; ?></td>
                            <td>
                                <button onclick="excluirRegistro(<?php echo $row['id']; ?>)" 
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <a href="gerenciar_empresa.php" class="btn btn-danger">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Adicione este script antes do fechamento do body -->
    <script>
    function excluirRegistro(registroId) {
        Swal.fire({
            title: 'Confirmar exclusão',
            text: 'Tem certeza que deseja excluir este registro?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `pro_labore.php?id=<?php echo $socio_id; ?>&action=delete&registro_id=${registroId}`;
            }
        });
    }
    </script>
</body>
</html> 