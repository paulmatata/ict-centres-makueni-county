<?php

include '../includes/db.php';
include 'includes/admin_auth.php';
require('../fpdf/fpdf.php');

$id = (int) $_GET['id'];

$role = $_SESSION['admin_role'];
$centre_id = $_SESSION['centre_id'];
$restrict = ($role != 'super_admin');

// ---- Fetch the student, respecting the centre restriction ----
$sql = "SELECT students.*, ict_centres.centre_name
    FROM students
    LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
    WHERE students.id = ?" . ($restrict ? " AND students.centre_id = ?" : "");

$stmt = mysqli_prepare($conn, $sql);
if ($restrict) {
    mysqli_stmt_bind_param($stmt, "ii", $id, $centre_id);
} else {
    mysqli_stmt_bind_param($stmt, "i", $id);
}
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$student) {
    die("Student not found, or not in your centre.");
}

if ($student['completion_status'] !== 'completed') {
    die("This student hasn't been marked as completed yet. Certificates are only available after training completion.");
}

// ---- Serial number: generate once, then reuse on every future download ----
if (empty($student['certificate_serial'])) {

    $year = date('Y');

    $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM students WHERE certificate_serial LIKE ?");
    $like = "%/" . $year;
    mysqli_stmt_bind_param($count_stmt, "s", $like);
    mysqli_stmt_execute($count_stmt);
    $count = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];

    $next_number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    $serial = "S/NO:ICT/{$next_number}/{$year}";

    $update_stmt = mysqli_prepare($conn, "UPDATE students SET certificate_serial = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $serial, $id);
    mysqli_stmt_execute($update_stmt);

    $student['certificate_serial'] = $serial;
}

// ---- QR code: generated via a free API call, pointing to a verification page ----
// Requires the server to have outbound internet access (Render does by default).
// If the QR service is unreachable, the certificate still generates — just without the QR.
$verify_url = "https://ict-centres-makueni-county.onrender.com/verify_certificate.php?serial=" . urlencode($student['certificate_serial']);
$qr_path = null;

$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($verify_url);
$qr_data = @file_get_contents($qr_api);

if ($qr_data !== false) {
    $qr_path = sys_get_temp_dir() . '/qr_cert_' . $id . '.png';
    file_put_contents($qr_path, $qr_data);
}

// ============================================================
// PDF subclass with transparency support (for the watermark).
// Core FPDF has no opacity control — this is the standard
// community extension (fpdf.org "Transparency" script) that
// adds SetAlpha(). Works because these are subclass methods,
// which CAN access FPDF's protected internals, unlike calling
// them from an external function.
// ============================================================
class PDF extends FPDF
{
    var $extgstates = array();

    function SetAlpha($alpha, $bm = 'Normal')
    {
        $gs = $this->AddExtGState(array('ca' => $alpha, 'CA' => $alpha, 'BM' => '/' . $bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates) + 1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if (count($this->extgstates) && $this->PDFVersion < '1.4') {
            $this->PDFVersion = '1.4';
        }
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_put('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k => $v) {
                $this->_put('/' . $k . ' ' . $v);
            }
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_put('/ExtGState <<');
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_put('/GS' . $i . ' ' . $this->extgstates[$i]['n'] . ' 0 R');
        }
        $this->_put('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }
}

// ============================================================
// BUILD THE CERTIFICATE
// ============================================================

$pdf = new PDF('L', 'mm', 'A4'); // Landscape
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$page_w = 297;
$page_h = 210;

// ---- Watermark: faint Makueni logo, centered, behind everything ----
$pdf->SetAlpha(0.07);
$wm_size = 140;
$pdf->Image('../assets/images/makueni-logo.png', ($page_w - $wm_size) / 2, ($page_h - $wm_size) / 2, $wm_size);
$pdf->SetAlpha(1);

// ---- Nested border frame (Makueni palette + gold accent) ----
$colors = [
    ['color' => [11, 61, 105], 'inset' => 6],   // mc-blue
    ['color' => [212, 160, 23], 'inset' => 9],  // gold
    ['color' => [27, 122, 61], 'inset' => 12],  // mc-green
];

foreach ($colors as $c) {
    $pdf->SetDrawColor($c['color'][0], $c['color'][1], $c['color'][2]);
    $pdf->SetLineWidth(1);
    $inset = $c['inset'];
    $pdf->Rect($inset, $inset, $page_w - (2 * $inset), $page_h - (2 * $inset));
}

// ---- Header: crests + titles ----
$pdf->Image('../assets/images/kenya-logo.png', $page_w - 40, 18, 22);

$pdf->SetXY(0, 16);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell($page_w, 6, 'REPUBLIC OF KENYA', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($page_w, 8, 'GOVERNMENT OF MAKUENI COUNTY', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($page_w, 7, 'DEPARTMENT OF ICT, EDUCATION AND INTERNSHIP', 0, 1, 'C');

$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(27, 122, 61); // green
$pdf->Cell($page_w, 10, 'DIGITAL EMPOWERMENT PROGRAMME', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(200, 30, 60); // red/pink accent
$pdf->Cell($page_w, 9, 'Certificate of Completion', 0, 1, 'C');

$pdf->Ln(4);

// ---- Body ----
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($page_w, 7, 'This is to certify that', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(11, 61, 105); // blue
$pdf->Cell($page_w, 12, strtoupper($student['fullname']), 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($page_w, 7, 'Has successfully completed a two months training in the following', 0, 1, 'C');
$pdf->Cell($page_w, 7, 'Basic Computer Applications', 0, 1, 'C');

$pdf->Ln(4);

// ---- Skills list, two columns, centered as a block ----
$skills = ['Microsoft Word', 'Microsoft Excel', 'Microsoft Powerpoint', 'Microsoft Publisher', 'Microsoft Access', 'Internet'];
$col_width = 65;
$block_width = $col_width * 2;
$start_x = ($page_w - $block_width) / 2;

$pdf->SetFont('Arial', '', 11);
$y = $pdf->GetY();

for ($i = 0; $i < count($skills); $i += 2) {
    $pdf->SetXY($start_x, $y);
    $pdf->Cell($col_width, 6, chr(149) . ' ' . $skills[$i], 0, 0, 'L');
    if (isset($skills[$i + 1])) {
        $pdf->SetXY($start_x + $col_width, $y);
        $pdf->Cell($col_width, 6, chr(149) . ' ' . $skills[$i + 1], 0, 0, 'L');
    }
    $y += 6;
}

$pdf->SetY($y + 8);

// ---- Footer block: signatures + QR + centre name ----
$footer_y = $page_h - 42;

// Left signature: Chief Officer - ICT
$pdf->SetY($footer_y);
$pdf->SetX(25);
$pdf->Cell(70, 6, '_________________________', 0, 2, 'C');
$pdf->SetX(25);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 5, 'CHIEF OFFICER - ICT', 0, 2, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetX(25);
$pdf->Cell(70, 5, 'CECM-ICT, Education & Internship', 0, 0, 'C');

// Centre + serial, centered
$pdf->SetXY(($page_w - 100) / 2, $footer_y);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(100, 5, $student['centre_name'], 0, 2, 'C');
$pdf->Cell(100, 5, $student['certificate_serial'], 0, 2, 'C');
$pdf->Cell(100, 5, 'Issued: ' . date('d M Y'), 0, 0, 'C');

// QR code, right side
if ($qr_path !== null) {
    $pdf->Image($qr_path, $page_w - 55, $footer_y - 5, 25);
    $pdf->SetXY($page_w - 55, $footer_y + 21);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(25, 4, 'Scan to verify', 0, 0, 'C');
}

$pdf->Output('D', 'Certificate_' . preg_replace('/[^A-Za-z0-9]/', '_', $student['fullname']) . '.pdf');

// Clean up the temporary QR image
if ($qr_path !== null && file_exists($qr_path)) {
    unlink($qr_path);
}

?>
