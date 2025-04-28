<div class="container" id="cont">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    ?>
    <div class="row">
        <div class="col-8">
            <h1 class="heading">Projects</h1>
            
            <div class="frame-container">
            <div id="info-card" style="
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            background-color: black;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            min-width: 300px;">
                <span id="close-info-card" style="float: right; cursor: pointer;">✖</span>
                <div id="info-card-content"></div>
            </div>




            <?php
            include('./common/db.php');

            $allProjectsByUser = [];

            if(isset($_GET['c-id'])){
                $cid = (int)$_GET['c-id'];
                $query = "select * from projects where category_id = $cid";
            }
            else if(isset($_GET['u-id'])){
                $uid = (int)$_GET['u-id'];
                $query = "select * from projects where user_id = $uid";
            }
            else if(isset($_GET['latest'])){
                $query = "select * from projects order by id desc";
            }
            else if(isset($_GET['search'])){
                $searchterm = $conn->real_escape_string($_GET['search']);
                $query = "select * from projects where `title` LIKE '%$searchterm%'";
            }
            else{
                $query = "select * from projects";
            }  
            $result = $conn->query($query);
            foreach($result as $row)
            {
                $title = ucfirst($row['title']);
                $id = (int) $row['id'];
                $user_id = (int) $row['user_id'];
                if(isset($_SESSION['user']['user_id'])){
                    $current_user_id = $_SESSION['user']['user_id'];
                }

                $members = $conn->prepare("SELECT member_ids FROM projects WHERE id = ?");
                $members->bind_param("i", $id);
                $members->execute();
                $all_members = $members->get_result();
                $alluser = $all_members->fetch_assoc();
                $member_ids_string = $alluser['member_ids'];

                $member_ids = array_filter(explode(',', $member_ids_string));

                $usernames = [];

                foreach ($member_ids as $userid) {
                    $userQuery = $conn->prepare("SELECT username FROM users WHERE id = ?");
                    $userQuery->bind_param("i", $userid);
                    $userQuery->execute();
                    $userResult = $userQuery->get_result();
    
                    if ($userRow = $userResult->fetch_assoc()) {
                        $usernames[] = $userRow['username'];
                    }
                }

                $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $userStmt->bind_param("i", $user_id);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $user = $userResult->fetch_assoc();
                $username = $user ? htmlspecialchars($user['username']) : 'Unknown';

                $profile_image = "./public/profile-user.png";
                $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
                $profile_query->bind_param("i", $user_id);
                $profile_query->execute();
                $profile_result = $profile_query->get_result();
                if ($profile_result->num_rows > 0) {
                    $image_row = $profile_result->fetch_assoc();
                    $filename = basename($image_row['filename']);
                    $profile_image = "./server/profile/" . $filename;
                }

                if (!isset($allProjectsByUser[$user_id])) {
                    $userProjectsQuery = "SELECT title FROM projects WHERE user_id = $user_id";
                    $userProjectsResult = $conn->query($userProjectsQuery);
                    $userProjects = [];
                    while($proj = $userProjectsResult->fetch_assoc()) {
                        $userProjects[] = ucfirst($proj['title']);
                    }
                    $allProjectsByUser[$user_id] = $userProjects;
                }

                $userProjectsJson = htmlspecialchars(json_encode($allProjectsByUser[$user_id]), ENT_QUOTES, 'UTF-8');
                $usernamesJson = htmlspecialchars(json_encode($usernames), ENT_QUOTES, 'UTF-8');

                echo "<div class='row question-list' style='display: flex; align-items: center; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 20px;'>";

                    echo "<div class='col-auto'>";
                        echo "<a href='#' data-toggle='tooltip' title='User: $username'>";
                            echo "<span class='profile-button' data-project-id='$id' data-username='$username' data-projects='$userProjectsJson' data-usernames='$usernamesJson' data-image='$profile_image' style='cursor:pointer; margin-left: 10px; color: blue;'><img src='$profile_image' alt='Profile' style='width: 30px; height: 30px; border-radius: 50%;'></span>";
                        echo "</a>";
                        echo "</div>";
                        
                    echo "<div class='col'>";
                    echo "<h4 class='my-questions' style='margin: 0;'> <a href='?p-id=$id'> $title </a></h4>";
                    echo "</div>";
                    
                    if (isset($uid)) {
                        echo "<div class='col-auto ml-auto'>";
                        echo "<a href='?project-id=$id' class='btn btn-sm chat-btn' project-id='$id' style='margin-bottom: 5px;'>chat</a>";
                        echo "</div>";
                        
                        echo "<div class='col-auto ml-auto'>";
                        echo "<a href='./server/requests.php?delete=$id' style='margin-bottom: 5px;' class='btn btn-sm btn-danger'>Delete</a>";
                        echo "</div>";
                    }
                    
                    if (isset($_SESSION['user']['user_id']) && !isset($uid)) {
                        if($_SESSION['user']['user_id'] != $user_id){
                            echo "<div class='col-auto ml-auto'>";
                            echo "<a href='./server/requests.php?join=$id' class='btn btn-outline-light' style='margin-bottom: 5px;'>Join</a>";
                            echo "</div>";
                        }
                    }
                    echo "</div>";
                }
                ?>
        </div>
    </div>
    <div class="col-4">
        <?php
            include('category-list.php');
            ?>
        </div>
    </div>
    <?php
    if (isset($uid)) {
    ?>
        <div class="col-8">
        <h1 class="heading">Joined Projects</h1>
        <?php
        $query2 = "SELECT * FROM projects WHERE FIND_IN_SET($uid, member_ids)";
        $result2 = $conn->query($query2);
        foreach($result2 as $row2)
        {
            $title1 = ucfirst($row2['title']);
            $id1 = $row2['id'];
            $user_id1 = $row2['user_id'];

            $allProjectsByUser1 = [];

            $userStmt1 = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $userStmt1->bind_param("i", $user_id1);
            $userStmt1->execute();
            $userResult1 = $userStmt1->get_result();
            $user1 = $userResult1->fetch_assoc();
            $username1 = $user1 ? htmlspecialchars($user1['username']) : 'Unknown';
            
            $profile_image1 = "./public/profile-user.png";
            $profile_query1 = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
            $profile_query1->bind_param("i", $user_id1);
            $profile_query1->execute();
            $profile_result1 = $profile_query1->get_result();
            if ($profile_result1->num_rows > 0) {
                $image_row1 = $profile_result1->fetch_assoc();
                $filename1 = basename($image_row['filename']);
                $profile_image1 = "./server/profile/" . $filename1;
            }

            if (!isset($allProjectsByUser1[$user_id1])) {
                $userProjectsQuery1 = "SELECT title FROM projects WHERE user_id = $user_id1";
                $userProjectsResult1 = $conn->query($userProjectsQuery1);
                $userProjects1 = [];
                while($proj1 = $userProjectsResult1->fetch_assoc()) {
                    $userProjects1[] = ucfirst($proj1['title']);
                }
                $allProjectsByUser1[$user_id1] = $userProjects1;
            }

            $members1 = $conn->prepare("SELECT member_ids FROM projects WHERE id = ?");
            $members1->bind_param("i", $id1);
            $members1->execute();
            $all_members1 = $members1->get_result();
            $alluser1 = $all_members1->fetch_assoc();
            $member_ids_string1 = $alluser1['member_ids'];

            $member_ids1 = array_filter(explode(',', $member_ids_string1));

            $usernames1 = [];

            foreach ($member_ids1 as $userid1) {
                $userQuery1 = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $userQuery1->bind_param("i", $userid1);
                $userQuery1->execute();
                $userResult1 = $userQuery1->get_result();
    
                if ($userRow1 = $userResult1->fetch_assoc()) {
                    $usernames1[] = $userRow1['username'];
                }
            }

            $userProjectsJson1 = htmlspecialchars(json_encode($allProjectsByUser1[$user_id1]), ENT_QUOTES, 'UTF-8');
            $usernamesJson1 = htmlspecialchars(json_encode($usernames1), ENT_QUOTES, 'UTF-8');
            
            echo "<div class='row question-list' style='display: flex; align-items: center; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 20px;'>";
            
            echo "<div class='col-auto'>";
            echo "<a href='#' data-toggle='tooltip' title='User: $username1'>";
            echo "<span class='profile-button' data-project-id='$id1' data-username='$username1' data-projects='$userProjectsJson1' data-usernames='$usernamesJson1' data-image='$profile_image1' style='cursor:pointer; margin-left: 10px; color: blue;'><img src='$profile_image1' alt='Profile' style='width: 30px; height: 30px; border-radius: 50%;'></span>";
            echo "</a>";
            echo "</div>";
            
            echo "<div class='col'>";
            echo "<h4 class='my-questions' style='margin: 0;'> <a href='?p-id=$id1'> $title1 </a></h4>";
            echo "</div>";
            
            if (isset($uid)) {
                echo "<div class='col-auto ml-auto'>";
                echo "<a href='?project-id=$id1' class='btn btn-sm chat-btn' project-id='$id1' style='margin-bottom: 5px;'>chat</a>";
                echo "</div>";
            }
            echo "</div>";
            
        }
        ?>
        </div>
    <?php
    }
    ?>


<script>
    document.addEventListener('DOMContentLoaded', function() {
                
        const infoButtons = document.querySelectorAll('.profile-button');
        const infoCard = document.getElementById('info-card');
        const infoContent = document.getElementById('info-card-content');
        const closeBtn = document.getElementById('close-info-card');
                
        infoButtons.forEach(button => {
            button.addEventListener('click', function() {
                const projectId = this.getAttribute('data-project-id');
                const username = this.getAttribute('data-username');
                const image = this.getAttribute('data-image');
                const projects = JSON.parse(this.getAttribute('data-projects') || '[]');
                const usernames = JSON.parse(this.getAttribute('data-usernames') || '[]');

                const projectListHTML = projects.length
                ? '<ul style="margin-top: 10px;">' + projects.map(p => `<li>${p}</li>`).join('') + '</ul>'
                : '<p>No other projects found.</p>';

                infoContent.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="${image}" alt="Profile Image" style="width: 60px; height: 60px; border-radius: 50%;">
                    <div>
                        <h4 style="margin: 0;">${username}</h4>
                        <p style="margin: 0;">Project ID: ${projectId}</p>
                    </div>
                </div>
                <hr>
                <h5>Team Members:</h5>
                ${usernames.length ? '<ul style="margin-top: 10px;">' + usernames.map(u => `<li>${u}</li>`).join('') + '</ul>' : '<p>No team members listed.</p>'}
                <hr>
                <h5>Other Projects by ${username}:</h5>
                ${projectListHTML}
                `;
                infoCard.style.display = 'block';
            });
        });

        closeBtn.addEventListener('click', function() {
            infoCard.style.display = 'none';
        });

        window.addEventListener('click', function(e) {
            if (e.target === infoCard) {
                infoCard.style.display = 'none';
            }
        });
    });
</script>