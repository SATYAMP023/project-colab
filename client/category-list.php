<div>
    <h1 class="heading">category</h1>
    <div class="frame-container">
    <?php
    include('./common/db.php');

    function sanitize($input) {
        return htmlspecialchars(strip_tags($input));
    }

    $query = "select * from category";
    $result = $conn->query($query);
    foreach($result as $row)
    {
        $category = sanitize(ucfirst($row['category']));
        $id = (int) $row['id'];

        $query1 = $conn->prepare("SELECT * FROM projects WHERE category_id = ?");
        $query1->bind_param("i", $id);
        $query1->execute();
        $query1_result = $query1->get_result();
        $count = $query1_result->num_rows;

        echo "<div class='row question-list' style='margin-bottom: 20px;'>
        <h4> <a href='?c-id=$id'>( $count ) $category </a> </h4>
        </div>";
    }
    ?>
    </div>
</div>