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

$query = "SELECT DATE_FORMAT(data, '%Y-%m') as mes_ano, descricao, valor FROM despesas_fixas";

if ($exportType == 'month') {
    $query .= " WHERE YEAR(data) = ? AND MONTH(data) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $exportYear, $exportMonth);
} elseif ($exportType == 'year') {
    $query .= " WHERE YEAR(data) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $exportYear);
} else {
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

$currentMonth = '';
$totalMonth = 0;
$totalYear = 0;
$grandTotal = 0;

while ($row = $result->fetch_assoc()) {
    $mesAno = $row['mes_ano'];
    list($ano, $mes) = explode('-', $mesAno);
    
    if ($mesAno != $currentMonth) {
        if ($currentMonth != '') {
            $pdf->Cell(0, 10, 'Total do mes: R$ ' . number_format($totalMonth, 2, ',', '.'), 0, 1);
            $pdf->Ln(5);
        }
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Mes: ' . $mes . '/' . $ano, 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(100, 10, 'Descricao', 1);
        $pdf->Cell(90, 10, 'Valor (R$)', 1, 1);
        $currentMonth = $mesAno;
        $totalMonth = 0;
    }

    $pdf->Cell(100, 10, $row['descricao'], 1);
    $pdf->Cell(90, 10, 'R$ ' . number_format($row['valor'], 2, ',', '.'), 1, 1);
    
    $totalMonth += $row['valor'];
    $totalYear += $row['valor'];
    $grandTotal += $row['valor'];
}

$pdf->Cell(0, 10, 'Total do mes: R$ ' . number_format($totalMonth, 2, ',', '.'), 0, 1);
$pdf->Ln(5);

if ($exportType == 'year' || $exportType == 'total') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Total do Ano: R$ ' . number_format($totalYear, 2, ',', '.'), 0, 1);
}

if ($exportType == 'total') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Total Geral: R$ ' . number_format($grandTotal, 2, ',', '.'), 0, 1);
}

$pdf->Output('D', 'relatorio_despesas.pdf');