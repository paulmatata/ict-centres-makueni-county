<?php
// 1. Include dependencies first (Ensures ob_start rules apply safely)
include '../includes/db.php';
include '../includes/admin_auth.php';
include '../includes/admin_header.php';
include '../includes/admin_sidebar';

// 2. Include Composer's Autoloader for the Cloudinary SDK
require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

// 3. Configure Cloudinary using Render Environment Variables
Configuration::instance([
    'cloud' => [
        'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
        'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
        'api_secret' => $_ENV['CLOUDINARY_API_SECRET']
    ],
    'url' => [
        'secure' => true
    ]
]);

$centre_id = $_SESSION['centre_id'];
$admin_id = $_SESSION['admin_id'];

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    $file = $_FILES['note_file'];
    $tmp_name = $file['tmp_name'];
    
    // Strict Verification: Validate the file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file_ext != 'pdf') {
        $error = "Only PDF files allowed";
    } else {
        try {
            // 4. Upload the file binary stream straight to Cloudinary
            $uploadApi = new UploadApi();
            $response = $uploadApi->upload($tmp_name, [
                'resource_type' => 'raw', // Mandatory flag for processing PDFs
                'folder'        => 'makueni_ict_notes',
                'use_filename'  => true
            ]);

            // 5. Grab the permanent secure URL
            $secure_url = $response['secure_url'];

            // 6. Save the secure URL into your Aiven MySQL Database
            $sql = "INSERT INTO notes(
                title,
                description,
                file_name,
                centre_id,
                uploaded_by
            ) VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssii", $title, $description, $secure_url, $centre_id, $admin_id);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Notes Uploaded Successfully";
            } else {
                $error = "Database entry failed: " . mysqli_error($conn);
            }

        } catch (Exception $e) {
            $error = "Cloudinary Transmission Error: " . $e->getMessage();
        }
    }
}
?>
<div class="admin-content">
<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
Upload Notes
</h3>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

<?php

if(isset($error)){

echo "<div class='alert alert-danger'>$error</div>";

}

if(isset($success)){

echo "<div class='alert alert-success'>$success</div>";

}

?>

<form method="POST"
enctype="multipart/form-data">

<div class="mb-3">

<label>Notes Title</label>

<input
type="text"
name="title"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"
rows="4"
required></textarea>

</div>

<div class="mb-4">

<label>Upload PDF Notes</label>

<input
type="file"
name="note_file"
class="form-control"
accept=".pdf"
required>

</div>

<button
name="submit"
class="btn btn-primary">

Upload Notes

</button>

</form>

</div>

</div>
</div>
<?php include '../includes/admin_footer.php'; ?>
