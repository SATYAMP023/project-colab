<div class="container" id="cont">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['message']);
    }
    ?>
    <div class="row">
        <div class="col-8">
            <h1 class="heading">Users</h1>

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

            if (isset($_GET['userskill'])) {
                $skill = trim($_GET['userskill']);
                if (!preg_match("/^[a-zA-Z0-9\s]+$/", $skill)) { 
                    die("Invalid skill input.");
                }
                $query = $conn->prepare("
                    SELECT users.* 
                    FROM users 
                    JOIN skills ON users.id = skills.user_id 
                    WHERE LOWER(skills.skill) LIKE CONCAT('%', LOWER(?), '%') 
                    AND users.user_type = 'student'
                ");
                $query->bind_param("s", $skill);

                $query->execute();
                $result = $query->get_result();
            } else {
                $result = $conn->query("select * from users WHERE user_type = 'student'");
            }

            foreach($result as $row)
            {
                $name = ucfirst($row['username']);
                $user_id = intval($row['id']);

                $profile_image = "./public/profile-user.png";
                $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
                $profile_query->bind_param("i", $user_id);
                $profile_query->execute();
                $profile_result = $profile_query->get_result();
                if ($profile_result->num_rows > 0) {
                    $image_row = $profile_result->fetch_assoc();
                    $profile_image = "./server/profile/" . htmlspecialchars($image_row['filename'], ENT_QUOTES, 'UTF-8');
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



                echo "<div class='row question-list' style='display: flex; align-items: center; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 30px;'>";

                    echo "<div class='col-auto'>";
                        echo "<a href='#' data-toggle='tooltip' title='User: $username'>";
                            echo "<span class='profile-button' data-user-id='$user_id' data-username='$name' data-projects='$userProjectsJson' data-image='$profile_image' style='cursor:pointer; margin-left: 10px; color: blue;'><img src='$profile_image' alt='Profile' style='width: 30px; height: 30px; border-radius: 50%;'></span>";
                        echo "</a>";
                    echo "</div>";

                    echo "<div class='col'>";
                        echo "<h4 class='my-questions' style='margin: 0;'> <a href='?u-id=$user_id'> $name </a></h4>";
                    echo "</div>";

                    echo "<div class='col-auto ml-auto'>";
                        echo "<a href='./server/requests.php?offer=$user_id' >Offer_Job</a>";
                    echo "</div>";

                echo "</div>";
            }
            ?>
        </div>

        <div class="col-4">
        <?php
            include('skill-list-user.php');
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