<?php
include 'conexao.php';

if (isset($_GET['mes']) && isset($_GET['ano'])) {
    $mes = $_GET['mes'];
    $ano = $_GET['ano'];
    
    $query = "SELECT id, descricao, valor, DATE_FORMAT(data, '%d/%m/%Y') as data 
              FROM despesas_fixas 
              WHERE MONTH(data) = ? AND YEAR(data) = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $mes, $ano);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $despesas = array();
    while ($row = $result->fetch_assoc()) {
        $despesas[] = array(
            'id' => $row['id'],
            'descricao' => $row['descricao'],
            'valor' => $row['valor'],
            'data' => $row['data']
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode($despesas);
}