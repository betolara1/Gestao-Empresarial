<?php
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
        $mensagem = "Pró-labore registrado com sucesso!";
    } else {
        $mensagem = "Erro ao registrar pró-labore: " . $conn->error;
    }
}

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
    <link rel="stylesheet" href="css/main.css">
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
                        <label for="valor">Valor do Pró-Labore:</label>
                        <input type="number" step="0.01" id="valor" name="valor" required>
                    </div>
                    <div class="form-group">
                        <label for="data">Data:</label>
                        <input type="date" id="data" name="data" required>
                    </div>
                </div>
                <button type="submit" class="btn">Registrar Pró-Labore</button>
            </form>

            <h2>Histórico de Pró-Labore</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Mês/Ano</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $historico->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['data_retirada'])); ?></td>
                            <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                            <td><?php echo $row['mes'] . '/' . $row['ano']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <a href="gerenciar_empresa.php" class="btn">Voltar</a>
        </div>
    </div>
</body>
</html> 