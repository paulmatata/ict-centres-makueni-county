<?php

include '../includes/db.php';
include '../includes/admin_auth.php';
include '../includes/admin_header.php';
include '../includes/admin_sidebar.php';

$role = $_SESSION['admin_role'];

$centre_id = $_SESSION['centre_id'];

if($role == 'super_admin'){

    $sql = "SELECT reviews.*, students.fullname,

    ict_centres.centre_name

    FROM reviews

    LEFT JOIN students

    ON reviews.student_id =
    students.id

    LEFT JOIN ict_centres

    ON reviews.centre_id =
    ict_centres.id";

    $stmt = mysqli_prepare($conn, $sql);

}else{

    $sql = "SELECT reviews.*, students.fullname,

    ict_centres.centre_name

    FROM reviews

    LEFT JOIN students

    ON reviews.student_id =
    students.id

    LEFT JOIN ict_centres

    ON reviews.centre_id =
    ict_centres.id

    WHERE reviews.centre_id=?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $centre_id);

}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<div class="admin-content">
<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center container mb-4">
<h3 class="mb-0">Student Reviews</h3>
<a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
</div>

</div>
<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>Student</th>
<th>Centre</th>
<th>Rating</th>
<th>Review</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php

while($review =
mysqli_fetch_assoc($result)){

?>

<tr>

<td>

<?php
echo $review['fullname'];
?>

</td>

<td>

<?php
echo $review['centre_name'];
?>

</td>

<td>

<?php

for(
$i=1;
$i<=$review['rating'];
$i++
){

echo "⭐";

}

?>

</td>

<td>

<?php
echo $review['review'];
?>

</td>

<td>

<?php
echo $review['created_at'];
?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>
</div>
<?php include '../includes/admin_footer.php'; ?>
