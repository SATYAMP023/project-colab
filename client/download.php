<?php
session_start();
include("../common/db.php");

if (!isset($_SESSION['user']['user_id'])) {
    die("You must be logged in to download files.");
}

if (!isset($_GET['file_id'])) {
    die("Invalid request.");
}

$file_id = intval($_GET['file_id']);

$query = $conn1->prepare("SELECT * FROM documents WHERE id = ?");
$query->bind_param("i", $file_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    die("File not found.");
}

$file = $result->fetch_assoc();
$file_path = realpath(__DIR__ . "/../server/uploads/" . $file['filename']);

if (!file_exists($file_path)) {
    die("File not found on server.");
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $file['filetype']);
header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>
