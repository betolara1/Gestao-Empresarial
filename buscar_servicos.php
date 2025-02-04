<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM tipos_servicos ORDER BY tipo_servico";
    $result = $conn->query($query);
    
    $servicos = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $servicos[] = array(
                'id' => $row['id'],
                'tipo_servico' => htmlspecialchars($row['tipo_servico'])
            );
        }
    }
    
    echo json_encode($servicos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?> 