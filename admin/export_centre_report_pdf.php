<?php

include '../includes/admin_auth.php';

include '../includes/db.php';
include '../includes/admin_header.php';
include '../includes/admin_sidebar.php';

require('../fpdf/fpdf.php');

$role = $_SESSION['admin_role'];
$centre_id = $_SESSION['centre_id'];

$restrict = ($role != 'super_admin');

// ---- Helpers for wrapping long text (e.g. ICT Centre names) across rows ----
// Uses only public FPDF methods (GetStringWidth) — no protected properties.
function NbLines($pdf, $w, $txt)
{
    $words = explode(' ', $txt);
    $line = '';
    $nl = 1;
    $usable = $w - 2; // approximate cell padding

    foreach ($words as $word) {
        $test = ($line === '') ? $word : $line . ' ' . $word;
        if ($pdf->GetStringWidth($test) > $usable && $line !== '') {
            $nl++;
            $line = $word;
        } else {
            $line = $test;
        }
    }

    return $nl;
}

function Row($pdf, $data, $widths, $left_margin, $lineHeight = 5)
{
    $nb = 1;
    foreach ($data as $i => $txt) {
        $nb = max($nb, NbLines($pdf, $widths[$i], $txt));
    }
    $height = $lineHeight * $nb;

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    foreach ($data as $i => $txt) {
        $w = $widths[$i];
        $pdf->Rect($x, $y, $w, $height);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $lineHeight, $txt, 0, 'L');
        $x += $w;
    }
    $pdf->SetXY($left_margin, $y + $height);
}

// ---- Filter setup (join condition built with placeholders, not interpolated values) ----
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$join_extra = "";
$join_params = [];
$join_types = "";
$report_title = 'ICT Centre Report: Entire History';

switch ($filter) {

    case 'month_range':
        $from_month = isset($_GET['from_month']) ? (int)$_GET['from_month'] : 1;
        $to_month   = isset($_GET['to_month'])   ? (int)$_GET['to_month']   : 12;
        $from_month = max(1, min(12, $from_month));
        $to_month   = max(1, min(12, $to_month));
        $year_now   = date('Y');

        $from_date = $year_now . '-' . str_pad($from_month, 2, '0', STR_PAD_LEFT) . '-01';
        $to_date   = date('Y-m-t', strtotime($year_now . '-' . str_pad($to_month, 2, '0', STR_PAD_LEFT) . '-01'));

        $join_extra = " AND students.training_start_date BETWEEN ? AND ?";
        $join_params = [$from_date, $to_date];
        $join_types = "ss";

        $report_title = 'ICT Centre Report: ' . date('F', mktime(0, 0, 0, $from_month, 1)) . ' - ' .
                         date('F', mktime(0, 0, 0, $to_month, 1)) . ' ' . $year_now;
        break;

    case 'year':
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $join_extra = " AND YEAR(students.training_start_date) = ?";
        $join_params = [$year];
        $join_types = "i";
        $report_title = "ICT Centre Report: $year";
        break;

    default:
        // 'all' — no date restriction
        break;
}

// ---- Role restriction ----
$where_sql = "";
$where_params = [];
$where_types = "";

if ($restrict) {
    $where_sql = " WHERE ict_centres.id = ?";
    $where_params = [$centre_id];
    $where_types = "i";
}

$sql = "SELECT ict_centres.centre_name,

COUNT(students.id) AS total_students,

SUM(CASE WHEN students.completion_status='completed' THEN 1 ELSE 0 END) AS completed_students,

SUM(CASE WHEN students.status='removed' THEN 1 ELSE 0 END) AS removed_students,

SUM(CASE WHEN students.status='active' AND students.completion_status='incomplete'
    AND students.training_start_date <= CURDATE() THEN 1 ELSE 0 END) AS in_training,

SUM(CASE WHEN students.status='active' AND students.completion_status='incomplete'
    AND students.training_start_date > CURDATE() THEN 1 ELSE 0 END) AS pending_training

FROM ict_centres

LEFT JOIN students ON ict_centres.id = students.centre_id $join_extra

$where_sql

GROUP BY ict_centres.id";

$stmt = mysqli_prepare($conn, $sql);

$all_params = array_merge($join_params, $where_params);
$all_types  = $join_types . $where_types;

if (count($all_params) > 0) {
    mysqli_stmt_bind_param($stmt, $all_types, ...$all_params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pdf = new FPDF();

$pdf->AddPage();

$left_margin = $pdf->GetX();

$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, $report_title, 0, 1, 'C');

$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 9);

$col_widths = [55, 25, 25, 25, 30, 30];

Row($pdf, ['ICT Centre', 'Total', 'Completed', 'Removed', 'In Training', 'Pending'], $col_widths, $left_margin);

$pdf->SetFont('Arial', '', 9);

$has_rows = false;

while ($row = mysqli_fetch_assoc($result)) {
    $has_rows = true;

    Row(
        $pdf,
        [
            $row['centre_name'],
            $row['total_students'],
            $row['completed_students'],
            $row['removed_students'],
            $row['in_training'],
            $row['pending_training'],
        ],
        $col_widths,
        $left_margin
    );
}

if (!$has_rows) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'No data found for this filter.', 1, 1, 'C');
}

$pdf->Output('D', 'Centre_Report.pdf');

?>
<div class="admin-content">
</div>
