<?php

session_start();

include 'includes/db.php';

include 'includes/header.php';

$student_id =
$_SESSION['student_id'];

$get_stmt = mysqli_prepare($conn,

"SELECT students.*, ict_centres.centre_name

FROM students

LEFT JOIN ict_centres

ON students.centre_id =
ict_centres.id

WHERE students.id=?"

);
mysqli_stmt_bind_param($get_stmt, "i", $student_id);
mysqli_stmt_execute($get_stmt);
$get = mysqli_stmt_get_result($get_stmt);

$student =
mysqli_fetch_assoc($get);

?>

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="text-center mb-4">

<h2>
MAKUENI County ICT TRAINING LETTER
</h2>

</div>

<p>

Dear
<strong>

<?php
echo $student['fullname'];
?>

</strong>,

</p>

<p>

You have successfully been
admitted for digital training at:

<strong>

<?php
echo $student['centre_name'];
?>

</strong>

</p>

<p>

Your training is scheduled to begin on:

<strong>

<?php
echo $student['training_start_date'];
?>

</strong>

</p>

<p>

You are required to report to the center
by 8:00 AM.

</p>

<div class="alert alert-info">

Training Fee:
Ksh.1000 payable at the ICT Center.
<br>
<hr>
This Training Letter will Be required at the ICT center.

</div>

<a
href="generate_training_letter.php"

class="btn btn-primary">

Download Training Letter

</a>
<hr>
</div>

</div>

<?php include 'includes/footer.php'; ?>
