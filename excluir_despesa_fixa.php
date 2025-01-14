<?php
// excluir_despesa_fixa.php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $response = array();
    
    $conn->begin_transaction();
    
    try {
        $sql = "DELETE FROM despesas_fixas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = 'Despesa excluída com sucesso!';
        } else {
            throw new Exception("Erro ao excluir a despesa");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['status'] = 'error';
        $response['message'] = 'Erro ao excluir a despesa: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>