<?php
session_start();
include("../common/db.php");

if (!isset($_GET['file_id']) || !ctype_digit($_GET['file_id'])) {
    die("Error: File ID is missing.");
}

$file_id = intval($_GET['file_id']);

$sql = "SELECT * FROM resumes WHERE id = ?";
$stmt = $conn1->prepare($sql);
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: File not found in database.");
}

$file = $result->fetch_assoc();
$file_path = realpath(__DIR__ . "/../server/Resume/" . $file['filename']);

if (!$file_path || !file_exists($file_path)) {
    echo "<h3>File not found!</h3><p>Please check if the file exists.</p>";
    exit;
}

$file_type = mime_content_type($file_path);

header("Content-Type: $file_type");
header("Content-Disposition: inline; filename=\"" . basename($file_path) . "\"");
header("Content-Length: " . filesize($file_path));

readfile($file_path);
exit;
?>
