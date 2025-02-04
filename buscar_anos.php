<?php
include 'conexao.php';

header('Content-Type: application/json');

try {
    $query = "SELECT DISTINCT YEAR(data) as ano FROM despesas_fixas ORDER BY ano DESC";
    $result = $conn->query($query);
    
    $anos = array();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $anos[] = $row['ano'];
        }
    }
    
    echo json_encode($anos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?> 