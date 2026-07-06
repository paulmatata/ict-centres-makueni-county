<?php

session_start();

include 'includes/db.php';

include 'includes/header.php';

if(!isset($_SESSION['student_id'])){

    header("Location: login.php");

    exit();

}

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

if(

$student['completion_status']
!= 'completed'

){

die("Review available after training completion.");

}

$check_stmt =
mysqli_prepare($conn,

"SELECT * FROM reviews

WHERE student_id=?"

);
mysqli_stmt_bind_param($check_stmt, "i", $student_id);
mysqli_stmt_execute($check_stmt);
$check_review = mysqli_stmt_get_result($check_stmt);

if(mysqli_num_rows($check_review) > 0){

die("You already submitted a review.");

}

if(isset($_POST['submit'])){

    $rating =
    (int) $_POST['rating'];

    $review =
    trim($_POST['review']);

    $centre_id = $student['centre_id'];

    $insert_stmt = mysqli_prepare($conn,

    "INSERT INTO reviews(

        student_id,
        centre_id,
        rating,
        review

    )

    VALUES(?, ?, ?, ?)"

    );
    mysqli_stmt_bind_param($insert_stmt, "iiis", $student_id, $centre_id, $rating, $review);
    mysqli_stmt_execute($insert_stmt);

    $success =
    "Review submitted successfully";

}

?>

<div class="container py-4">

<div class="row justify-content-center">

<div class="col-lg-6">

<div class="card border-0 shadow-lg rounded-4 p-4">

<h3 class="mb-4">
Rate Your ICT Center
</h3>

<p>

Centre:
<strong>

<?php
echo $student['centre_name'];
?>

</strong>

</p>

<?php

if(isset($success)){

echo "

<div class='alert alert-success'>

$success

</div>

";

}

?>

<form method="POST">

<div class="mb-3">

<label>Rating</label>

<select
name="rating"
class="form-control"
required>

<option value="">
Select Rating
</option>

<option value="5">
⭐⭐⭐⭐⭐ Excellent
</option>

<option value="4">
⭐⭐⭐⭐ Very Good
</option>

<option value="3">
⭐⭐⭐ Good
</option>

<option value="2">
⭐⭐ Fair
</option>

<option value="1">
⭐ Poor
</option>

</select>

</div>

<div class="mb-4">

<label>Review</label>

<textarea
name="review"
class="form-control"
rows="5"
required></textarea>

</div>

<button
name="submit"
class="btn btn-primary w-100">

Submit Review

</button>

</form>

</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>
