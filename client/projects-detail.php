<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<div class="container">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    ?>
    <h1 class="heading">Projects</h1>
    <div class="row">
        <div class="col-7">
            <?php
            include("./common/db.php");

            $pid = isset($_GET['p-id']) ? (int) $_GET['p-id'] : 0;

            $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $cid = $row['category_id'];
            echo "<h3 class='margin-bottom-15 project-title'>Project: ".ucfirst($row['title'])."</h3>
            <p class='margin-bottom-15'>".$row['description']."</p>";
            include("upload.php");
            ?>
            <form action="./server/requests.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="project_id" value="<?php echo $pid ?>">
                <label for="file" class="form-label">Select file</label>
                <input type="file" class="form-control margin-bottom-15" name="file" id = "file" placeholder="upload your file here...">
                <button type="submit" class="btn btn-primary">upload file</button>
            </form>
        </div>
        <div class="col-1"></div>
        <div class="col-4">
            <?php
            $stmt = $conn->prepare("SELECT category FROM category WHERE id = ?");
            $stmt->bind_param("i", $cid);
            $stmt->execute();
            $cat_result = $stmt->get_result();
            $categoryrow = $cat_result->fetch_assoc();
            echo "<h3 class='heading2'>".ucfirst($categoryrow['category'])."</h3>";

            $stmt = $conn->prepare("SELECT * FROM projects WHERE category_id = ? AND id != ?");
            $stmt->bind_param("ii", $cid, $pid);
            $stmt->execute();
            $result = $stmt->get_result();
            foreach($result as $row)
            {
                $title = htmlspecialchars(ucfirst($row['title']));
                $id = (int) $row['id'];
                echo "<div class='row question-list'>
                <h4> <a href='?p-id=$id'> $title </a> </h4>
                </div>";
            }
            ?>
        </div>
    </div>
</div>
<div style="margin-top: 20px; margin-bottom: 20px;" class="container">
    <div class="row">
        <div class="col-7">
            <div class="offset-sm-1">
                <h5>Comments:</h5>
                <?php
                $stmt = $conn->prepare("SELECT * FROM comments WHERE project_id = ?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $result = $stmt->get_result();
                if(isset($_SESSION['user']['user_id'])){
                    $userid2 = $_SESSION['user']['user_id'];
                }
                if ($result->num_rows > 0) {
                    foreach($result as $row){
                        $comment = nl2br(htmlspecialchars($row['comment']));
                        $id = (int) $row['id'];
                        $userid1 = (int) $row['user_id'];
                        
                        echo "<div class='row align-items-center mb-2'> 
                        <div class='col-8'>
                        <p class='comment-wrapper'>$comment</p>
                        </div>";
                        
                        if (isset($_SESSION['user']['user_id']) && $userid1 == $userid2) {
                            echo "<div class='col-4 text-end'>
                            <a href='./server/requests.php?deletecomment=$id' class='btn btn-danger btn-sm'>Delete</a>
                            </div>";
                        }
                        echo "</div>";
                    }
                }
                else {
                    echo "<div class='row align-items-center mb-2'> 
                        <div class='col-8'>
                        <p class='comment-wrapper'>No Comments Posted Yet...</p>
                        </div>
                    </div>";
                }
                
                ?>    
            </div>
        </div>
        <div class="col-1">

        </div>
        <div class="col-4">
        <h5>Leave your Comment here:</h5>
            <form action="./server/requests.php" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $pid ?>">
                <textarea name="comment" class="form-control margin-bottom-15" placeholder="Your comment..." required maxlength="1000" pattern=".{1,1000}" title="Comment must be between 1 and 1000 characters."></textarea>
                <button class="btn btn-primary">submit</button>
            </form>
        </div>
    </div>
</div>