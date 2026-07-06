<?php

session_start();

include 'includes/db.php';

require('fpdf/fpdf.php');

if(!isset($_SESSION['student_id'])){

    header("Location: login.php");

    exit();

}

$student_id =
$_SESSION['student_id'];

$sql = "SELECT students.*, ict_centres.centre_name

FROM students

LEFT JOIN ict_centres

ON students.centre_id =
ict_centres.id

WHERE students.id=?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$student =
mysqli_fetch_assoc($result);

class PDF extends FPDF{

function Header(){

    $this->Image(
        'assets/images/makueni-logo.png',
        10,
        8,
        25
    );

    $this->SetFont(
        'Arial',
        'B',
        15
    );

    $this->Cell(
        0,
        10,
        'MAKUENI COUNTY ICT TRAINING',
        0,
        1,
        'C'
    );

    $this->Ln(10);

}

function Footer(){

    $this->SetY(-15);

    $this->SetFont(
        'Arial',
        'I',
        8
    );

    $this->Cell(
        0,
        10,
        'Makueni County ICT Centers',
        0,
        0,
        'C'
    );

}

}

$pdf = new PDF();

$pdf->SetAutoPageBreak(true,20);

$date_today =
date('d M Y');

$start_date =
date(
'd M Y',
strtotime(
$student['training_start_date']
)
);

$end_date =
date(
'd M Y',
strtotime(
'+39 days',
strtotime(
$student['training_start_date']
)
)
);

$ref =
"MKICT-" .
date('Y') .
"-" .
$student['id'];





/*
=================================
LETTER 1
STUDENT LETTER
=================================
*/

$pdf->AddPage();

$pdf->SetFont(
'Arial',
'',
12
);

$pdf->Cell(
0,
8,
"To: " .
$student['fullname'],
0,
1
);

$pdf->Cell(
0,
8,
"From: Makueni County ICT Department",
0,
1
);

$pdf->Cell(
0,
8,
"Date: " .
$date_today,
0,
1
);

$pdf->Cell(
0,
8,
"REF No: " .
$ref,
0,
1
);

$pdf->Ln(5);

$pdf->SetFont(
'Arial',
'B',
12
);

$pdf->MultiCell(
0,
8,
"RE: ICT TRAINING ADMISSION FOR " .
strtoupper(
$student['fullname']
)
);

$pdf->Ln(5);

$pdf->SetFont(
'Arial',
'',
12
);

$body1 =

"Following your successful registration "

."for Computer Packages and "
."Digital Skills Training at "

.$student['centre_name']

.", you are required to report "

."to the ICT Center Manager on "

.$start_date."."

."\n\n"

."Your training will run from "

.$start_date

." to "

.$end_date."."

."\n\n"

."You are expected to adhere "

."to the regulations and code "

."of conduct of the Makueni "

."County ICT Centers."

."\n\n"

."Training Fee: Ksh.1000 "

."payable physically at the "

."ICT Center.";

$pdf->MultiCell(
0,
8,
$body1
);

$pdf->Ln(10);

$pdf->Cell(
0,
8,
"Yours Faithfully,",
0,
1
);

$pdf->Ln(10);

$pdf->Cell(
0,
8,
"County ICT Coordinator",
0,
1
);





/*
=================================
LETTER 2
ICT MANAGER LETTER
=================================
*/

$pdf->AddPage();

$pdf->SetFont(
'Arial',
'',
12
);

$pdf->Cell(
0,
8,
"To: ICT Center Manager",
0,
1
);

$pdf->Cell(
0,
8,
$student['centre_name'],
0,
1
);

$pdf->Cell(
0,
8,
"Date: " .
$date_today,
0,
1
);

$pdf->Cell(
0,
8,
"REF No: " .
$ref,
0,
1
);

$pdf->Ln(5);

$pdf->SetFont(
'Arial',
'B',
12
);

$pdf->MultiCell(
0,
8,
"RE: TRAINING PLACEMENT FOR " .
strtoupper(
$student['fullname']
)
);

$pdf->Ln(5);

$pdf->SetFont(
'Arial',
'',
12
);

$body2 =

"The following student has "

."successfully been admitted "

."for ICT digital training "

."at your center."

."\n\n"

."NAME: "
.$student['fullname']

."\n"


."ICT CENTER: "
.$student['centre_name']

."\n"

."PHONE: "
.$student['phone']

."\n"

."REPORTING DATE: "
.$start_date
."\n"
."PROGRAM: Computer Packages "
."and Digital Skills Training"

."\n\n"

."Kindly prepare the training "

."environment and assist the "

."student during the training period.";

$pdf->MultiCell(
0,
8,
$body2
);

$pdf->Ln(10);

$pdf->Cell(
0,
8,
"Yours Faithfully,",
0,
1
);

$pdf->Ln(10);

$pdf->Cell(
0,
8,
"County ICT Coordinator",
0,
1
);

$pdf->Output(
'D',
'Computer_Packages_Training_Letter.pdf'
);

?>
