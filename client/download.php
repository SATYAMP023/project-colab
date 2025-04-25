<?php
session_start();
include("../common/db.php");

if (!isset($_SESSION['user']['user_id'])) {
    die("You must be logged in to download files.");
}

if (!isset($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
    die("Invalid request.");
}

$file_id = intval($_GET['file_id']);

$query = $conn1->prepare("SELECT * FROM documents WHERE id = ?");
$query->bind_param("i", $file_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die("File not found.");
}

$file = $result->fetch_assoc();
$filename = basename($file['filename']);
$filepath = realpath(__DIR__ . "/../server/uploads/" . $filename);

if (!$filepath || !file_exists($filepath) || strpos($filepath, realpath(__DIR__ . "/../server/uploads")) !== 0) {
    die("File not found on server.");
}

header('Content-Description: File Transfer');
header('Content-Type: ' . htmlspecialchars($file['filetype'], ENT_QUOTES, 'UTF-8'));
header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>
