<?php 
include 'conexao.php';

$sql_empresa = "SELECT * FROM empresa LIMIT 1"; 
$result_empresa = $conn->query($sql_empresa);

// Verifica se encontrou registros
if ($result_empresa->num_rows > 0) {
    $empresa = $result_empresa->fetch_assoc(); // Pega o primeiro registro
} else {
    $empresa = 0; // Caso não encontre dados, inicializa como vazio
}

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        echo '<div class="message success">Imagem salva com sucesso!</div>';
    } elseif ($_GET['status'] == 'error') {
        echo '<div class="message error">Erro ao salvar imagem. Tente novamente.</div>';
    }
}

$sql_logo = "SELECT image_data FROM logos ORDER BY created_at DESC LIMIT 1";
$result_logo = $conn->query($sql_logo);
$logoImage = null;

if ($result_logo->num_rows > 0) {
    $result_logo_row = $result_logo->fetch_assoc();
    $logoImage = $result_logo_row['image_data'];
}
?>
