<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM areas_atuacao ORDER BY nome";
    $result = $conn->query($query);
    
    $areas = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $areas[] = array(
                'id' => $row['id'],
                'nome' => htmlspecialchars($row['nome'])
            );
        }
    }
    
    echo json_encode($areas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?> 