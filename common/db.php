<?php
    $host = "localhost";
    $username = "root";
    $password = null;
    $database = "softwareproject";
    $database1 = "software_document";

    $conn = new mysqli($host,$username,$password,$database);
    if ($conn->connect_error) {
        die("Database not connected". $conn->connect_error);
    }
    $conn1 = new mysqli($host,$username,$password,$database1);
    if ($conn1->connect_error) {
        die("Database not connected". $conn1->connect_error);
    }
?>