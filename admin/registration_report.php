<?php

include '../includes/admin_auth.php';

include '../includes/db.php';

include '../includes/admin_header.php';

$sql = "SELECT DATE(created_at)
AS reg_date,

COUNT(id)
AS total

FROM students

GROUP BY DATE(created_at)

ORDER BY reg_date DESC";

$result = mysqli_query($conn, $sql);

?>

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<h3 class="mb-4">
Daily Registration Report
</h3>

<table class="table table-hover">

<thead>

<tr>

<th>Date</th>
<th>Registrations</th>

</tr>

</thead>

<tbody>

<?php

while($row =
mysqli_fetch_assoc($result)){

?>

<tr>

<td>

<?php
echo $row['reg_date'];
?>

</td>

<td>

<?php
echo $row['total'];
?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<?php include '../includes/admin_footer.php'; ?>
