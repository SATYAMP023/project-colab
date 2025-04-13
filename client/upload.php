<?php

if(isset($_SESSION['user']['user_id'])){
    $uid = $_SESSION['user']['user_id'];
}

$sql = "SELECT *FROM documents where project_id = $pid";
$result = $conn1->query($sql);

?>

<div class="container">
    <div class="offset-sm-1">
        <h5>Files and Folders:</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>File Type</th>
                    <th>View</th>
                    <th>Download</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?phP
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $file_path = "uploads/" . $row['filename'];
                        ?>
                        <tr>
                            <td><?php echo $row['filename']; ?></td>
                            <td><?php echo $row['filesize']; ?> bytes</td>
                            <td><?php echo $row['filetype']; ?></td>
                            <td> <a href="client/view.php?file_id=<?php echo $row['id']; ?>" class="btn btn-primary" target="_blank">View</a> </td>
                            <td>
                            <?php if (isset($_SESSION['user']['user_id'])) { ?>
                                <a href="client/download.php?file_id=<?php echo $row['id']; ?>" class="btn btn-primary" download>Download</a>
                            <?php } else { ?>
                                <span class="text-muted">No permission</span>
                            <?php } ?>
                            </td>
                            <td>
                                <?php if (isset($_SESSION['user']['user_id']) && $row['user_id'] == $uid) { ?>
                                    <a href="./server/requests.php?deletefile=<?php echo $row['id']; ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this file?');">
                                       Delete
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">No permission</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4">No files uploaded yet.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>    
    </div>
</div>