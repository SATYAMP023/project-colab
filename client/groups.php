<div class="container" id="cont">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }

    include('./common/db.php');
    $project_id = $_GET['project-id'];

    $query1 = "select * from projects where id = $project_id";
    $result1 = $conn->query($query1);
    foreach($result1 as $row1)
    {
        $admin_user_id = (int) $row1['user_id'];
    }

    $query2 = "select * from users where id = $admin_user_id";
    $result2 = $conn->query($query2);
    foreach($result2 as $row2)
    {
        $admin_name = $row2['username'];
        $admin_email = $row2['email'];
    }
    ?>
    <div class="row">

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
            min-width: 300px;
            max-width: 400px;">
                <span id="close-info-card" style="float: right; cursor: pointer;">✖</span>
                <div id="info-card-content"></div>
            </div>


        <div class="col-7">

            <?php
            $query = "select * from projects where id = $project_id";
            $result = $conn->query($query);
            foreach($result as $row)
            {
                $title = ucfirst($row['title']);
            }
            ?>

            <h1 class="heading">
                <?php
                echo $title;
                ?>
            </h1>
            <div class="frame-container">
                <div class="messages" style="height: 350px; background-color: #a0e6f4; color: white;">

                <?php
                $profile_image_admin = "./public/profile-user.png";
                $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
                $profile_query->bind_param("i", $admin_user_id);
                $profile_query->execute();
                $profile_result = $profile_query->get_result();
                if ($profile_result->num_rows > 0) {
                    $image_row = $profile_result->fetch_assoc();
                    $profile_image_admin = "./server/profile/" . htmlspecialchars($image_row['filename'], ENT_QUOTES, 'UTF-8');
                }

                $userProjectsQuery = "SELECT title FROM projects WHERE user_id = $admin_user_id";
                $userProjectsResult = $conn->query($userProjectsQuery);
                $userProjects = [];
                while($proj = $userProjectsResult->fetch_assoc()) {
                    $userProjects[] = ucfirst($proj['title']);
                }
                $allProjectsByadmin[$admin_user_id] = $userProjects;

                $userProjectsJsonbyadmin = htmlspecialchars(json_encode($allProjectsByadmin[$admin_user_id]), ENT_QUOTES, 'UTF-8');

                // Fetch messages for this project
                $chat_query = "SELECT c.message, u.username, c.chat_time, c.user_id
                FROM chat c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.project_id = $project_id 
                ORDER BY c.chat_time ASC";

                $chat_result = $conn->query($chat_query);

                if ($chat_result->num_rows > 0) {
                    foreach ($chat_result as $chat) {
                        $username = htmlspecialchars($chat['username']);
                        $message = htmlspecialchars($chat['message']);
                        $timestamp = date('d M Y, h:i A', strtotime($chat['chat_time']));
                        $sender_user_id = (int) $chat['user_id'];
                        $current_user_id = (int) $_SESSION['user']['user_id'];
                        if ($sender_user_id === $current_user_id) {
                            echo "
                                <div class='chat-message' style='text-align: right; margin-bottom: 10px;'>
                                    <strong>{$username}:</strong> {$message} <br>
                                    <small style='color: gray;'>{$timestamp}</small>
                                </div>
                            ";
                        }
                        else{
                            echo "
                                <div class='chat-message' style='margin-bottom: 10px;'>
                                    <strong>{$username}:</strong> {$message} <br>
                                    <small style='color: gray;'>{$timestamp}</small>
                                </div>
                            ";
                        }
                    }
                } else {
                    echo "<p style='color: black; text-align:center;'>No messages yet. Start the conversation!</p>";
                }
                ?>


                </div>
            </div>
                <form action="./server/requests.php" method="POST" style="display: flex; gap: 5px; height: 55px;">
                    <input type="hidden" name="project_id" value="<?php echo isset($project_id) ? (int)$project_id : 0; ?>">
                    <input type="text" class="form-control" name="message" id="chat-input" placeholder="Type your message..." style="flex: 1; padding: 5px; margin-bottom: 10px;">
                    <button type="submit" name="send_chat" class="btn btn-sm chat-btn" style="margin-bottom: 10px;">Send</button>
                </form>
        </div>
        <div class="col-1"></div>
        <div class="col-4">
            <h3 class="heading">Team Members</h3>
            
            <?php
            $query = $conn->prepare("SELECT member_ids FROM `projects` WHERE id = ?");
            $query->bind_param("i", $project_id);
            $query->execute();
            $result = $query->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                $member_ids_string = $row['member_ids'];
                
                $member_ids = array_filter(explode(',', $member_ids_string));
            }
            
            echo "<div class='row question-list' style='display: flex; align-items: center; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 15px;'>";
            
                echo "<div class='col-auto'>";
                    echo "<a href='#' data-toggle='tooltip' title='User: $admin_name'>";
                        echo "<span class='profile-button' data-user-id='$admin_user_id' data-username='$admin_name' data-projects='$userProjectsJsonbyadmin' data-image='$profile_image_admin' style='cursor:pointer; margin-left: 10px; color: blue;'><img src='$profile_image_admin' alt='Profile' style='width: 30px; height: 30px; border-radius: 50%;'></span>";
                    echo "</a>";
                echo "</div>";

                echo "<div class='col'>";
                    echo "<h4 class='my-questions' style='margin: 0;'> $admin_name </h4>";
                echo "</div>";
                echo "<div class='col-auto ml-auto'>";
                    echo "Admin";
                echo "</div>";
                echo "<p style='margin: 0; font-size: 14px; color: white;'>$admin_email</p>";

            echo "</div>";

            if (!empty($member_ids)) {
                $member_ids = array_map('intval', $member_ids);
                $placeholders = implode(',', array_fill(0, count($member_ids), '?'));
            
                $user_query = $conn->prepare("SELECT id, username, email FROM `users` WHERE id IN ($placeholders)");
            
                $types = str_repeat('i', count($member_ids));
                $user_query->bind_param($types, ...$member_ids);
                $user_query->execute();
                $user_result = $user_query->get_result();
            
                while ($user = $user_result->fetch_assoc()) {
                    $user_id = (int) $user['id'];
                    $username = htmlspecialchars($user['username']);
                    $email = htmlspecialchars($user['email']);


                    $profile_image = "./public/profile-user.png";
                    $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
                    $profile_query->bind_param("i", $user_id);
                    $profile_query->execute();
                    $profile_result = $profile_query->get_result();
                    if ($profile_result->num_rows > 0) {
                        $image_row = $profile_result->fetch_assoc();
                        $profile_image = "./server/profile/" . htmlspecialchars($image_row['filename'], ENT_QUOTES, 'UTF-8');
                    }

                    $userProjectsQuery = "SELECT title FROM projects WHERE user_id = $user_id";
                    $userProjectsResult = $conn->query($userProjectsQuery);
                    $userProjects = [];
                    while($proj = $userProjectsResult->fetch_assoc()) {
                        $userProjects[] = ucfirst($proj['title']);
                    }
                    $allProjectsByUser[$user_id] = $userProjects;

                    $userProjectsJson = htmlspecialchars(json_encode($allProjectsByUser[$user_id]), ENT_QUOTES, 'UTF-8');

            
            
                    echo "<div class='row question-list' style='display: flex; align-items: center; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 15px;'>";
            
                    echo "<div class='col-auto'>";
                        echo "<a href='#' data-toggle='tooltip' title='User: $username'>";
                            echo "<span class='profile-button' data-user-id='$user_id' data-username='$username' data-projects='$userProjectsJson' data-image='$profile_image' style='cursor:pointer; margin-left: 10px; color: blue;'><img src='$profile_image' alt='Profile' style='width: 30px; height: 30px; border-radius: 50%;'></span>";
                        echo "</a>";
                    echo "</div>";
    
                    echo "<div class='col'>";
                        echo "<h4 class='my-questions' style='margin: 0;'> $username </h4>";
                    echo "</div>";
                    echo "<p style='margin: 0; font-size: 14px; color: white;'>$email</p>";
    
                echo "</div>";
                    }
            }

            ?>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const infoButtons = document.querySelectorAll('.profile-button');
    const infoCard = document.getElementById('info-card');
    const infoContent = document.getElementById('info-card-content');
    const closeBtn = document.getElementById('close-info-card');

    infoButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const image = this.getAttribute('data-image');
            const projects = JSON.parse(this.getAttribute('data-projects') || '[]');

            const projectListHTML = projects.length
                ? '<ul style="margin-top: 10px;">' + projects.map(p => `<li>${p}</li>`).join('') + '</ul>'
                : '<p>No projects found.</p>';

            infoContent.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="${image}" alt="Profile Image" style="width: 60px; height: 60px; border-radius: 50%;">
                    <div>
                        <h4 style="margin: 0;">${username}</h4>
                        <p style="margin: 0;">User ID: ${userId}</p>
                    </div>
                </div>
                <hr>
                <h5>Projects by ${username}:</h5>
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