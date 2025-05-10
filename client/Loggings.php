<?php
include('./common/db.php');

$sql = "SELECT id, user_id, ip_address, working, time FROM logging ORDER BY id DESC";
$result = $conn1->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Logging Activity</title>
    <style>
        body {
            color: white;
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            margin: 20px auto;
            width: 90%;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f3f3f3;
        }
        h2 {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<h2>Admin Logging Activity</h2>

<table>
    <thead>
        <tr>
            <th style="color: black;">ID</th>
            <th style="color: black;">User ID</th>
            <th style="color: black;">IP Address</th>
            <th style="color: black;">Action</th>
            <th style="color: black;">Time</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['user_id']}</td>
                        <td>{$row['ip_address']}</td>
                        <td>{$row['working']}</td>
                        <td>{$row['time']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No log records found.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
