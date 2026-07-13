<?php

session_start();

include 'includes/db.php';
require('fpdf/fpdf.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = mysqli_prepare($conn,
    "SELECT students.*, ict_centres.centre_name
     FROM students
     LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
     WHERE students.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// A certificate can only be downloaded once an admin has issued it
// (i.e. certificate_serial is already set). Students cannot self-generate.
if (empty($student['certificate_serial'])) {
    die("Your certificate hasn't been issued yet. Please check back once your ICT centre has processed it.");
}

// ---- QR code is mandatory: no QR, no certificate ----
// This intentionally blocks output entirely if the QR service is unreachable,
// rather than silently issuing an unverifiable certificate.
$verify_url = "https://ict-centres-makueni-county.onrender.com/verify_certificate.php?serial=" . urlencode($student['certificate_serial']);
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($verify_url);
$qr_data = @file_get_contents($qr_api);

if ($qr_data === false) {
    die("Certificate generation is temporarily unavailable (verification service unreachable). Please try again shortly.");
}

$qr_path = sys_get_temp_dir() . '/qr_cert_' . $student_id . '_' . uniqid() . '.png';
file_put_contents($qr_path, $qr_data);

// ============================================================
// PDF subclass with transparency support (for the watermark).
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

    // ---- Rotation support (for the diagonal tiled watermark text) ----
    var $angle = 0;

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
function RotatedText($pdf, $x, $y, $txt, $angle) {
    // Text rotation via FPDF command string
    $p = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 
                 cos(deg2rad($angle)), sin(deg2rad($angle)), 
                 -sin(deg2rad($angle)), cos(deg2rad($angle)), 
                 $x * $pdf->k, ($pdf->h - $y) * $pdf->k, 
                 str_replace(')', '\\)', str_replace('(', '\\(', $txt)));
    $pdf->_out($p);
}
    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    // ---- Rounded rectangle (for smoother, non-overlapping border corners) ----
    function RoundedRect($x, $y, $w, $h, $r, $style = 'D')
    {
        $k = $this->k;
        $hp = $this->h;
        $op = ($style == 'F') ? 'f' : 'S';
        $myArc = 4 / 3 * (sqrt(2) - 1);

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x + $w - $r; $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_Arc($xc + $r * $myArc, $yc - $r, $xc + $r, $yc - $r * $myArc, $xc + $r, $yc);

        $xc = $x + $w - $r; $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $myArc, $xc + $r * $myArc, $yc + $r, $xc, $yc + $r);

        $xc = $x + $r; $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $myArc, $yc + $r, $xc - $r, $yc + $r * $myArc, $xc - $r, $yc);

        $xc = $x + $r; $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $myArc, $xc - $r * $myArc, $yc - $r, $xc, $yc - $r);

        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $k = $this->k;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $k, ($h - $y1) * $k, $x2 * $k, ($h - $y2) * $k, $x3 * $k, ($h - $y3) * $k));
    }
}

// ============================================================
// BUILD THE CERTIFICATE
// ============================================================

$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$page_w = 297;
$page_h = 210;

// ---- Watermark: tiled diagonal text, matching the county's style ----
// ==================== CONTINUOUS SINE WAVE WATERMARK ====================
$current_x = $pdf->GetX();
$current_y = $pdf->GetY();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(232, 234, 232); // Faint background tint

$watermark_text = "Government of Makueni County - ICT Capacity Building Programme - ";
$text_length = strlen($watermark_text);

// Vertical grid rows
for ($base_y = 15; $base_y < $page_h - 10; $base_y += 24) {
    
    $char_index = 0;
    // Step letter-by-letter across the entire page width seamlessly
    for ($x = 12; $x < $page_w - 12; $x += 2.1) { // 2.1mm step per character spacing
        
        // 1. Calculate wave properties
        $angle_modifier = $x * 0.05; // Alters wave frequency
        $wave_offset = sin($angle_modifier) * 6.5; // Amplitude/height of wave
        $calculated_y = $base_y + $wave_offset;
        
        // 2. Calculate the exact slope/tangent angle of the wave at this precise coordinate
        $slope = cos($angle_modifier) * 6.5 * 0.05;
        $angle = rad2deg(atan($slope));
        
        // 3. Extract the exact current character to stitch them together seamlessly
        $char = $watermark_text[$char_index % $text_length];
        
        // 4. Print the characters with rotation matching the curve direction
        RotatedText($pdf, $x, $calculated_y, $char, $angle);
        
        $char_index++;
    }
}

$pdf->SetXY($current_x, $current_y);
// ========================================================================
// ===================================================================
// ---- Nested border frame, rounded corners ----
// 1. Setup the exact shared dimensions
$outer_inset  = 9;   // Blue frame spacing
$middle_inset = 12;  // Yellow/Gold frame spacing
$inner_inset  = 15;  // Green frame spacing

$line_start_y = $outer_inset;
$line_end_y   = $page_h - $outer_inset;

$pdf->SetLineWidth(1.5);

// 2. Draw the vertical flag lines on the left side
$pdf->SetDrawColor(0, 0, 0); // Black
$pdf->Line($outer_inset, $line_start_y, $outer_inset, $line_end_y);

$pdf->SetDrawColor(200, 16, 46); // Red
$pdf->Line($middle_inset, $line_start_y, $middle_inset, $line_end_y);

$pdf->SetDrawColor(0, 128, 50); // Green
$pdf->Line($inner_inset, $line_start_y, $inner_inset, $line_end_y);


// 3. Draw the Outer BLUE border (Open on the left side—starts from the green line)
$pdf->SetDrawColor(11, 61, 105);
$pdf->SetLineWidth(0.5);
// Top Line: Starts at inner_inset X, runs to right edge
$pdf->Line($inner_inset, $outer_inset, $page_w - $outer_inset, $outer_inset);
// Right Line: Runs down the right edge
$pdf->Line($page_w - $outer_inset, $outer_inset, $page_w - $outer_inset, $page_h - $outer_inset);
// Bottom Line: Runs back to the inner_inset X
$pdf->Line($inner_inset, $page_h - $outer_inset, $page_w - $outer_inset, $page_h - $outer_inset);


// 4. Draw the Middle GOLD border (Open on the left side—starts from the green line)
$pdf->SetDrawColor(212, 160, 23);
$pdf->SetLineWidth(0.5);
// Top Line
$pdf->Line($inner_inset, $middle_inset, $page_w - $middle_inset, $middle_inset);
// Right Line
$pdf->Line($page_w - $middle_inset, $middle_inset, $page_w - $middle_inset, $page_h - $middle_inset);
// Bottom Line
$pdf->Line($inner_inset, $page_h - $middle_inset, $page_w - $middle_inset, $page_h - $middle_inset);


// 5. Draw the complete INNER GREEN frame using a normal Rect or RoundedRect
$pdf->SetDrawColor(27, 122, 61);
$pdf->SetLineWidth(0.5);
// By using a complete rectangle here, it naturally forms the vertical line on the left
// and completes the top, right, and bottom lines perfectly flush with the other colors.
$pdf->RoundedRect($inner_inset, $inner_inset, $page_w - (2 * $inner_inset), $page_h - (2 * $inner_inset), 4, '1234', 'D');

// ---- Header ----
// Kenya coat of arms only — no county seal (the QR code now covers that role)
$pdf->SetXY(20, 20);
$pdf->Cell($page_w, 4,  $student['certificate_serial'], 0, 1, 'l');
foreach (['png', 'jpg', 'jpeg'] as $ext) {
    $kenya_logo = 'assets/images/Kenya_logo1.' . $ext;
    if (file_exists($kenya_logo)) {
        $pdf->Image($kenya_logo, 20, 28, 22);
        break;
    }
   $pdf->Image('assets/images/kenya-logo.png', $page_w - 40, 28, 22);
}

$pdf->SetXY(0, 24);
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
$pdf->SetTextColor(27, 122, 61);
$pdf->Cell($page_w, 10, 'DIGITAL EMPOWERMENT PROGRAMME', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(200, 30, 60);
$pdf->Cell($page_w, 9, 'Certificate of Completion', 0, 1, 'C');

$pdf->Ln(4);

// ---- Body ----
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($page_w, 7, 'This is to certify that', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(11, 61, 105);
$pdf->Cell($page_w, 12, strtoupper($student['fullname']), 0, 1, 'C');

// 1. line positioning using current cursor position.
$current_y = $pdf->GetY() + -1; // Add a small 4mm gap below the name

// 2. Set the line styling
$pdf->SetDrawColor(128, 128, 128); // Grey color matching the certificate
$pdf->SetLineWidth(0.75);           // Line thickness

// 3. Draw the extended line 
// Move the start left (from 40 to 20) and the end right (from 257 to 277)
$pdf->Line(20, $current_y, 277, $current_y);
$pdf->SetY($current_y + 2);


$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($page_w, 7, 'Has successfully completed a two months training in the following', 0, 1, 'C');
$pdf->Cell($page_w, 7, 'Basic Computer Applications', 0, 1, 'C');

$pdf->Ln(4);

// ---- Skills list ----
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

$pdf->SetY($y + 6);

// ---- Footer: signatures (uploaded images) + centre/serial + QR ----
$footer_y = $page_h - 46;

// Chief Officer signature
$co_sig = 'assets/images/co.png';
if (file_exists($co_sig)) {
    $pdf->Image($co_sig, 30, $footer_y - 12, 45);
}
$pdf->SetY($footer_y);
$pdf->SetX(25);
$pdf->Cell(55, 5, str_repeat('_', 30), 0, 2, 'C');
$pdf->SetX(25);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(55, 5, 'CHIEF OFFICER - ICT', 0, 0, 'C');

// CECM signature
$cecm_sig = 'assets/images/cecm.png';
if (file_exists($cecm_sig)) {
    $pdf->Image($cecm_sig, $page_w - 100, $footer_y - 12, 45);
}
$pdf->SetY($footer_y);
$pdf->SetX($page_w - 105);
$pdf->Cell(55, 5, str_repeat('_', 30), 0, 2, 'C');
$pdf->SetX($page_w - 105);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(55, 5, 'CECM - ICT, EDUCATION & INTERNSHIP', 0, 0, 'C');

// Centre + serial + QR, centered at the bottom
$pdf->SetXY(($page_w - 40) / 2, $footer_y - 18);
$pdf->Image($qr_path, ($page_w - 22) / 2, $footer_y - 20, 22);

$pdf->SetXY(0, $footer_y + 4);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($page_w, 4, $student['centre_name'] . '  |  ' .'Issued: ' . date('d M Y'), 0, 0, 'C');

$pdf->Output('D', 'Certificate_' . preg_replace('/[^A-Za-z0-9]/', '_', $student['fullname']) . '.pdf');

if (file_exists($qr_path)) {
    unlink($qr_path);
}

?>
