<div>
    <h1 class="heading">category</h1>
    <?php
    include('./common/db.php');
    $query = "select * from category";
    $result = $conn->query($query);
    foreach($result as $row)
    {
        $category = ucfirst($row['category']);
        $id = $row['id'];
        echo "<div class='row question-list'>
        <h4> <a href='?c-id=$id'> $category </a> </h4>
        </div>";
    }
    ?>
</div>