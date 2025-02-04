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
    $meses = $_POST['meses']; // Array com nomes dos meses
    $ano = intval($_POST['ano']);

    // Validar ano
    if ($ano < 1900 || $ano > 2100) {
        throw new Exception('Ano inválido');
    }

    // Array de conversão de nomes de meses para números
    $mesesNumeros = [
        'Janeiro' => '01',
        'Fevereiro' => '02',
        'Março' => '03',
        'Abril' => '04',
        'Maio' => '05',
        'Junho' => '06',
        'Julho' => '07',
        'Agosto' => '08',
        'Setembro' => '09',
        'Outubro' => '10',
        'Novembro' => '11',
        'Dezembro' => '12'
    ];

    // Buscar informações da despesa original
    $stmt = $conn->prepare("SELECT descricao, valor FROM despesas_fixas WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $despesa_id);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar a query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $despesa_original = $result->fetch_assoc();

    if (!$despesa_original) {
        throw new Exception('Despesa original não encontrada');
    }

    $replicatedCount = 0;
    
    // Preparar statement para inserção
    $insert_stmt = $conn->prepare("INSERT INTO despesas_fixas (descricao, valor, data) VALUES (?, ?, ?)");
    if (!$insert_stmt) {
        throw new Exception("Erro na preparação da query de inserção: " . $conn->error);
    }

    // Iniciar transação
    $conn->begin_transaction();

    foreach ($meses as $mesNome) {
        if (!isset($mesesNumeros[$mesNome])) {
            throw new Exception('Mês inválido: ' . $mesNome);
        }

        $mesNumero = $mesesNumeros[$mesNome];
        $data = sprintf('%04d-%02d-01', $ano, intval($mesNumero));
        
        // Verificar se já existe uma despesa igual neste mês/ano
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM despesas_fixas 
                                    WHERE descricao = ? 
                                    AND MONTH(data) = ? 
                                    AND YEAR(data) = ?");
        if (!$check_stmt) {
            throw new Exception("Erro na preparação da query de verificação: " . $conn->error);
        }

        // Corrigido: Criar variáveis para bind_param
        $mes_numero = intval($mesNumero);
        $descricao = $despesa_original['descricao'];
        
        $check_stmt->bind_param("sii", $descricao, $mes_numero, $ano);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $count = $check_result->fetch_assoc()['count'];

        if ($count == 0) {
            // Corrigido: Criar variáveis para bind_param
            $valor = $despesa_original['valor'];
            
            $insert_stmt->bind_param("sds", 
                $descricao,
                $valor,
                $data
            );
            
            if ($insert_stmt->execute()) {
                $replicatedCount++;
            } else {
                throw new Exception("Erro ao inserir despesa para o mês $mesNome: " . $insert_stmt->error);
            }
        }

        $check_stmt->close();
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

// Fechar conexões
if (isset($stmt)) $stmt->close();
if (isset($insert_stmt)) $insert_stmt->close();
$conn->close();
?>