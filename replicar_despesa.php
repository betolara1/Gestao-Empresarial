<?php
include 'conexao.php';

// Certifica que nenhum output HTML seja enviado antes do JSON
ob_clean();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    if (!isset($_POST['despesa_id']) || !isset($_POST['meses']) || !isset($_POST['ano'])) {
        throw new Exception('Dados incompletos');
    }

    $despesa_id = intval($_POST['despesa_id']);
    $meses = $_POST['meses'];
    $ano = intval($_POST['ano']);

    // Validar ano e meses
    if ($ano < 1900 || $ano > 2100) {
        throw new Exception('Ano inválido');
    }

    // Buscar informações da despesa original
    $stmt = $conn->prepare("SELECT descricao, valor FROM despesas_fixas WHERE id = ?");
    $stmt->bind_param("i", $despesa_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $despesa_original = $result->fetch_assoc();

    if (!$despesa_original) {
        throw new Exception('Despesa original não encontrada');
    }

    $replicatedCount = 0;
    
    // Preparar statement para inserção
    $insert_stmt = $conn->prepare("INSERT INTO despesas_fixas (descricao, valor, data) VALUES (?, ?, ?)");
    
    // Preparar statement para verificação
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM despesas_fixas WHERE descricao = ? AND DATE_FORMAT(data, '%Y-%m') = ?");

    // Iniciar transação
    $conn->begin_transaction();

    foreach ($meses as $mes) {
        // Garantir que o mês está no formato correto (dois dígitos)
        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
        
        // Validar mês
        if ($mes < '01' || $mes > '12') {
            throw new Exception('Mês inválido: ' . $mes);
        }

        // Criar data no formato YYYY-MM-DD
        $data = sprintf('%04d-%02d-01', $ano, intval($mes));
        
        // Verificar mês/ano
        $check_date = "$ano-$mes";
        $check_stmt->bind_param("ss", $despesa_original['descricao'], $check_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $count = $check_result->fetch_assoc()['count'];

        if ($count == 0) {
            $insert_stmt->bind_param("sds", 
                $despesa_original['descricao'],
                $despesa_original['valor'],
                $data
            );
            
            if ($insert_stmt->execute()) {
                $replicatedCount++;
            }
        }
    }

    // Commit da transação
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Despesa replicada com sucesso para $replicatedCount mês(es)."
    ]);

} catch (Exception $e) {
    // Em caso de erro, fazer rollback
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}