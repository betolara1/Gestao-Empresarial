<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexao.php';

$ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');

// Log dos parâmetros recebidos
error_log("Parâmetros recebidos - Ano: $ano, Mês: $mes");

try {
    // Preparar array de resposta
    $response = [];

    // Buscar Despesas Fixas Mensais
    $sqlDespesasFixas = "
        SELECT MONTH(data) as mes, SUM(valor) as total
        FROM despesas_fixas
        WHERE YEAR(data) = ?
        " . ($mes ? "AND MONTH(data) = ?" : "") . "
        GROUP BY MONTH(data)
        ORDER BY MONTH(data)";

    $stmt = $conn->prepare($sqlDespesasFixas);
    if ($mes) {
        $stmt->bind_param("ii", $ano, $mes);
    } else {
        $stmt->bind_param("i", $ano);
    }
    $stmt->execute();
    $resultDespesasFixas = $stmt->get_result();

    $despesasFixas = array_fill(1, 12, 0);
    while ($row = $resultDespesasFixas->fetch_assoc()) {
        $despesasFixas[$row['mes']] = (float)$row['total'];
    }
    $response['despesasFixas'] = array_values($despesasFixas);

    // Buscar Serviços por Status
    $sqlServicosStatus = "
        SELECT status_servico, COUNT(*) as total
        FROM servicos
        WHERE YEAR(data_inicio) = ?
        " . ($mes ? "AND MONTH(data_inicio) = ?" : "") . "
        GROUP BY status_servico";

    $stmt = $conn->prepare($sqlServicosStatus);
    if ($mes) {
        $stmt->bind_param("ii", $ano, $mes);
    } else {
        $stmt->bind_param("i", $ano);
    }
    $stmt->execute();
    $resultServicosStatus = $stmt->get_result();

    $servicosStatus = [];
    while ($row = $resultServicosStatus->fetch_assoc()) {
        $servicosStatus[$row['status_servico']] = (int)$row['total'];
    }
    $response['servicosStatus'] = $servicosStatus;

    // Buscar dados de projeção mensal
    $sqlProjecaoMensal = "
        SELECT 
            MONTH(s.data_inicio) as mes,
            COALESCE(SUM(s.valor_total), 0) - COALESCE(
                (SELECT SUM(df.valor)
                 FROM despesas_fixas df
                 WHERE YEAR(df.data) = ? AND MONTH(df.data) = MONTH(s.data_inicio)
                ), 0
            ) as projecao
        FROM servicos s
        WHERE YEAR(s.data_inicio) = ?
        GROUP BY MONTH(s.data_inicio)
        ORDER BY MONTH(s.data_inicio)
    ";

    $stmt = $conn->prepare($sqlProjecaoMensal);
    $stmt->bind_param("ii", $ano, $ano);
    $stmt->execute();
    $resultProjecao = $stmt->get_result();

    // Inicializar array com zeros para todos os meses
    $projecoesMensais = array_fill(0, 12, 0);

    // Preencher com os valores reais
    while ($row = $resultProjecao->fetch_assoc()) {
        $mes = (int)$row['mes'] - 1; // Ajustar para índice 0-11
        $projecoesMensais[$mes] = (float)$row['projecao'];
    }

    $response['projecoesMensais'] = $projecoesMensais;
    

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage());
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'projecoesMensais' => array_fill(0, 12, 0) // Fallback com zeros
    ]);
}
?> 