<?php

include '../includes/admin_auth.php';

include '../includes/db.php';

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

// Small helper: run a COUNT query with bound params, used for the "year" summary block
function count_students($conn, $where_clause, $params, $types)
{
    $sql = "SELECT COUNT(*) AS total FROM students WHERE $where_clause";
    $stmt = mysqli_prepare($conn, $sql);
    if (count($params) > 0) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return (int) mysqli_fetch_assoc($result)['total'];
}

// ---- Build filter conditions (all values bound, none interpolated) ----
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$conditions = [];
$params = [];
$types = "";

if ($restrict) {
    $conditions[] = "students.centre_id = ?";
    $params[] = $centre_id;
    $types .= "i";
}

$report_title = 'Makueni ICT Students Report';
$summary_line = null; // Only populated for the "year" filter

switch ($filter) {

    // ---- 1. Current class: same definition as the "Active Students" card ----
    case 'current_class':
        $conditions[] = "students.completion_status='incomplete'";
        $conditions[] = "students.status='active'";
        $conditions[] = "students.training_start_date <= CURDATE()";
        $report_title = 'Current Class Report';
        break;

    // ---- 2. Month range within the current year ----
    case 'month_range':
        $from_month = isset($_GET['from_month']) ? (int)$_GET['from_month'] : 1;
        $to_month   = isset($_GET['to_month'])   ? (int)$_GET['to_month']   : 12;
        $from_month = max(1, min(12, $from_month));
        $to_month   = max(1, min(12, $to_month));
        $year_now   = date('Y');

        $from_date = $year_now . '-' . str_pad($from_month, 2, '0', STR_PAD_LEFT) . '-01';
        $to_date   = date('Y-m-t', strtotime($year_now . '-' . str_pad($to_month, 2, '0', STR_PAD_LEFT) . '-01'));

        $conditions[] = "students.training_start_date BETWEEN ? AND ?";
        $params[] = $from_date;
        $params[] = $to_date;
        $types .= "ss";

        $report_title = 'Students Report: ' . date('F', mktime(0, 0, 0, $from_month, 1)) . ' - ' .
                         date('F', mktime(0, 0, 0, $to_month, 1)) . ' ' . $year_now;
        break;

    // ---- 3. Entire year, with a summary block ----
    case 'year':
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $conditions[] = "YEAR(students.training_start_date) = ?";
        $params[] = $year;
        $types .= "i";
        $report_title = "Students Report: $year";

        // Summary counts reuse the same role restriction
        $summary_where = "YEAR(training_start_date) = ?";
        $summary_params = [$year];
        $summary_types = "i";
        if ($restrict) {
            $summary_where .= " AND centre_id = ?";
            $summary_params[] = $centre_id;
            $summary_types .= "i";
        }

        $year_total = count_students($conn, $summary_where, $summary_params, $summary_types);

        $year_removed = count_students(
            $conn,
            $summary_where . " AND status='removed'",
            $summary_params,
            $summary_types
        );

        $year_active = count_students(
            $conn,
            $summary_where . " AND status='active' AND completion_status='incomplete'",
            $summary_params,
            $summary_types
        );

        $summary_line = "Total: $year_total    |    Active: $year_active    |    Removed: $year_removed";
        break;

    // ---- default: no extra filter, same as the original export ----
    default:
        break;
}

$where_sql = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

$sql = "SELECT students.*, ict_centres.centre_name
FROM students
LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
$where_sql
ORDER BY students.training_start_date ASC";

$stmt = mysqli_prepare($conn, $sql);

if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pdf = new FPDF();

$pdf->AddPage();

$left_margin = $pdf->GetX();

$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, $report_title, 0, 1, 'C');

// Summary block, only for the "year" filter
if ($summary_line !== null) {
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, $summary_line, 0, 1, 'C');
}

$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);

$col_widths = [10, 50, 35, 50, 40];

Row($pdf, ['#', 'Name', 'Phone', 'ICT Centre', 'Training start Date'], $col_widths, $left_margin);

$pdf->SetFont('Arial', '', 9);

$row_number = 1;
$has_rows = false;

while ($student = mysqli_fetch_assoc($result)) {
    $has_rows = true;

    Row(
        $pdf,
        [
            $row_number++,
            $student['fullname'],
            $student['phone'],
            $student['centre_name'],
            $student['training_start_date'],
        ],
        $col_widths,
        $left_margin
    );
}

if (!$has_rows) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'No students found for this filter.', 1, 1, 'C');
}

$pdf->Output('D', 'Students_Report.pdf');

?>
