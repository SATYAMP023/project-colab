<?php
session_start();
include("../common/db.php"); // Ensure this is correct

if (!isset($_GET['file_id'])) {
    die("Error: File ID is missing.");
}

$file_id = intval($_GET['file_id']); // Sanitize input

// Fetch file details from the database
$sql = "SELECT * FROM documents WHERE id = ?";
$stmt = $conn1->prepare($sql);
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: File not found in database.");
}

$file = $result->fetch_assoc();
$file_path = realpath(__DIR__ . "/../server/uploads/" . $file['filename']); // Corrected path

// Debugging - Check if file exists
if (!$file_path || !file_exists($file_path)) {
    echo "<h3>File not found!</h3><p>Please check if the file exists.</p>";
    exit;
}

// Get the file type
$file_type = mime_content_type($file_path);

// Set headers to display the file
header("Content-Type: $file_type");
header("Content-Disposition: inline; filename=\"" . basename($file_path) . "\"");
header("Content-Length: " . filesize($file_path));

readfile($file_path);
exit;
?>
