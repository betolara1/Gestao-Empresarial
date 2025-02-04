<?php
require('fpdf/fpdf.php');
require('conexao.php');

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Relatorio de Despesas Fixas', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$exportType = $_POST['exportType'];
$exportMonth = $_POST['exportMonth'] ?? null;
$exportYear = $_POST['exportYear'] ?? null;

// Query principal para obter os meses
$query = "SELECT DISTINCT 
            DATE_FORMAT(data, '%Y-%m') as mes_ano,
            DATE_FORMAT(data, '%m/%Y') as mes_ano_formatado
          FROM despesas_fixas";

if ($exportType == 'month') {
    $query .= " WHERE YEAR(data) = ? AND MONTH(data) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $exportYear, $exportMonth);
} elseif ($exportType == 'year') {
    $query .= " WHERE YEAR(data) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $exportYear);
}

$query .= " ORDER BY mes_ano";
$stmt = isset($stmt) ? $stmt : $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$totalGeral = 0;

while ($row = $result->fetch_assoc()) {
    // Cabeçalho do mês
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Mes: ' . $row['mes_ano_formatado'], 0, 1);
    
    // Cabeçalho da tabela de despesas
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(120, 10, 'Descricao', 1);
    $pdf->Cell(70, 10, 'Valor', 1, 1, 'R');
    
    // Buscar despesas do mês
    $queryDespesas = "SELECT descricao, valor 
                      FROM despesas_fixas 
                      WHERE DATE_FORMAT(data, '%Y-%m') = ?
                      ORDER BY descricao";
    
    $stmtDespesas = $conn->prepare($queryDespesas);
    $stmtDespesas->bind_param("s", $row['mes_ano']);
    $stmtDespesas->execute();
    $resultDespesas = $stmtDespesas->get_result();
    
    $totalMes = 0;
    $pdf->SetFont('Arial', '', 12);
    
    while ($despesa = $resultDespesas->fetch_assoc()) {
        $pdf->Cell(120, 10, utf8_decode($despesa['descricao']), 1);
        $pdf->Cell(70, 10, 'R$ ' . number_format($despesa['valor'], 2, ',', '.'), 1, 1, 'R');
        $totalMes += $despesa['valor'];
    }
    
    // Total do mês
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(120, 10, 'Total do Mes:', 1);
    $pdf->Cell(70, 10, 'R$ ' . number_format($totalMes, 2, ',', '.'), 1, 1, 'R');
    
    $totalGeral += $totalMes;
    $pdf->Ln(10);
    
    $stmtDespesas->close();
}

// Total Geral
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(120, 10, 'Total Geral:', 0);
$pdf->Cell(70, 10, 'R$ ' . number_format($totalGeral, 2, ',', '.'), 0, 1, 'R');

// Adicionar informações do período
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
if ($exportType == 'month') {
    $pdf->Cell(0, 10, "Período: {$exportMonth}/{$exportYear}", 0, 1);
} elseif ($exportType == 'year') {
    $pdf->Cell(0, 10, "Período: Ano {$exportYear}", 0, 1);
} else {
    $pdf->Cell(0, 10, "Período: Todos os registros", 0, 1);
}

$pdf->Output('D', 'relatorio_despesas.pdf');