<select class="form-control" name="category" id="category">
    <?php
    include("./common/db.php");
    $query = "select * from category";
    $result = $conn->query($query);
    foreach ($result as $row) {
        $name = htmlspecialchars(ucfirst($row['category']), ENT_QUOTES, 'UTF-8');
        $id = (int) $row['id'];
        echo "<option value=\"$id\"> $name</option>";
    }
    ?>
</select>