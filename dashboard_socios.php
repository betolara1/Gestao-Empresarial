<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'conexao.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');
        $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
        
        if (!$conn) {
            throw new Exception("Erro na conexão com o banco de dados");
        }
        
        // Busca o faturamento total do mês selecionado
        $sqlFaturamento = "
            SELECT COALESCE(SUM(valor_total), 0) as faturamento_total
            FROM servicos 
            WHERE YEAR(data_inicio) = ? 
            AND MONTH(data_inicio) = ?";
        
        $stmtFaturamento = $conn->prepare($sqlFaturamento);
        if (!$stmtFaturamento) {
            throw new Exception("Erro ao preparar query de faturamento: " . $conn->error);
        }
        
        $stmtFaturamento->bind_param("ii", $ano, $mes);
        $stmtFaturamento->execute();
        $resultFaturamento = $stmtFaturamento->get_result();
        $faturamentoTotal = $resultFaturamento->fetch_assoc()['faturamento_total'];
        
        // Busca informações dos sócios
        $sqlSocios = "SELECT id, nome, porcentagem_sociedade, porcentagem_comissao, valor_pro_labore FROM socios";
        $resultSocios = $conn->query($sqlSocios);
        
        if (!$resultSocios) {
            throw new Exception("Erro ao buscar sócios: " . $conn->error);
        }
        
        $socios = [];
        
        while ($socio = $resultSocios->fetch_assoc()) {
            // Calcula a comissão do sócio baseada no faturamento total
            $comissaoCalculada = ($faturamentoTotal * $socio['porcentagem_comissao']) / 100;
            
            // Calcula o valor da sociedade baseado no faturamento total
            $valorSociedade = ($faturamentoTotal * $socio['porcentagem_sociedade']) / 100;
            
            // Valor do pró-labore base do sócio
            $valorProLaboreBase = $socio['valor_pro_labore'];
            
            // Busca as retiradas do sócio no mês
            $sqlRetiradas = "
                SELECT 
                    COALESCE(SUM(CASE WHEN tipo = 'LABORE' THEN valor ELSE 0 END), 0) as total_labore,
                    COALESCE(SUM(CASE WHEN tipo = 'COMISSAO' THEN valor ELSE 0 END), 0) as total_comissao
                FROM retiradas_socios
                WHERE socio_id = ? 
                AND ano = ? 
                AND mes = ?";
            
            $stmtRetiradas = $conn->prepare($sqlRetiradas);
            if (!$stmtRetiradas) {
                throw new Exception("Erro ao preparar query de retiradas: " . $conn->error);
            }
            
            $stmtRetiradas->bind_param("iii", $socio['id'], $ano, $mes);
            $stmtRetiradas->execute();
            $retiradas = $stmtRetiradas->get_result()->fetch_assoc();
            
            $totalRetirado = $retiradas['total_labore'] + $retiradas['total_comissao'];
            
            // Calcula o pró-labore disponível (valor base - retiradas já feitas)
            $proLaboreDisponivel = $valorProLaboreBase - $retiradas['total_labore'];
            
            // Adiciona as informações do sócio no array
            $socios[] = [
                'id' => $socio['id'],
                'nome' => $socio['nome'],
                'porcentagem_sociedade' => $socio['porcentagem_sociedade'],
                'porcentagem_comissao' => $socio['porcentagem_comissao'],
                'pro_labore_base' => $valorProLaboreBase,
                'pro_labore_retirado' => $retiradas['total_labore'],
                'pro_labore_disponivel' => $proLaboreDisponivel,
                'comissao' => $comissaoCalculada,
                'comissao_retirada' => $retiradas['total_comissao'],
                'valor_disponivel' => $valorSociedade - $totalRetirado,
                // Adicionando detalhes das retiradas do mês
                'retiradas_mes' => [
                    'labore' => [
                        'valor_base' => $valorProLaboreBase,
                        'valor_retirado' => $retiradas['total_labore'],
                        'valor_disponivel' => $proLaboreDisponivel
                    ],
                    'comissao' => [
                        'valor_calculado' => $comissaoCalculada,
                        'valor_retirado' => $retiradas['total_comissao'],
                        'valor_disponivel' => $comissaoCalculada - $retiradas['total_comissao']
                    ],
                    'sociedade' => [
                        'valor_calculado' => $valorSociedade,
                        'valor_retirado' => $totalRetirado,
                        'valor_disponivel' => $valorSociedade - $totalRetirado
                    ]
                ]
            ];
        }
        
        // Retorna os dados em formato JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'periodo' => [
                'ano' => $ano,
                'mes' => $mes,
                'faturamento_total' => $faturamentoTotal
            ],
            'socios' => $socios
        ]);
        
    } else {
        throw new Exception("Método não permitido");
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>