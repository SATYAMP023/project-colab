<div class="container" id="cont">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['message']);
    }
    ?>
    <div class="row">
        <div class="col-8">
            <h1 class="heading">Jobs</h1>

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

            <div id="job-info-card" style="
            display: none;
            position: fixed;
            top: 25%;
            left: 50%;
            transform: translateX(-50%);
            background-color: black;
            padding: 20px;
            border: 1px solid #aaa;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 1100;
            min-width: 300px;
            max-width: 600px;">
                <span id="close-job-info-card" style="float: right; cursor: pointer;">✖</span>
                <div id="job-info-card-content"></div>
            </div>


            <?php
            include('./common/db.php');

            $alljobsByUser = [];

            if (isset($_GET['skill'])) {
                $skill = trim($_GET['skill']);
                $skill = filter_var($skill, FILTER_SANITIZE_STRING);
                $query = $conn->prepare("SELECT * FROM jobs WHERE LOWER(skills) LIKE CONCAT('%', LOWER(?), '%')");
                $query->bind_param("s", $skill);
                $query->execute();
                $result = $query->get_result();
            } else {
                $result = $conn->query("SELECT * FROM jobs");
            }

            foreach($result as $row)
            {
                $name = htmlspecialchars(ucfirst($row['title']), ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                $user_id = (int) $row['user_id'];

                $query = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $query->bind_param("i", $user_id);
                $query->execute();
                $user_result = $query->get_result();
                if ($user_result->num_rows > 0) {
                    $userdetail = $user_result->fetch_assoc();
                    $username = htmlspecialchars($userdetail['username'], ENT_QUOTES, 'UTF-8');
                }

                $id = $row['id'];

                $profile_image = "./public/profile-user.png";
                $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
                $profile_query->bind_param("i", $user_id);
                $profile_query->execute();
                $profile_result = $profile_query->get_result();
                if ($profile_result->num_rows > 0) {
                    $image_row = $profile_result->fetch_assoc();
                    $profile_image = "./server/profile/" . htmlspecialchars($image_row['filename'], ENT_QUOTES, 'UTF-8');
                }

                if (!isset($alljobsByUser[$user_id])) {
                    $userProjectsQuery = "SELECT title FROM jobs WHERE user_id = $user_id";
                    $userProjectsResult = $conn->query($userProjectsQuery);
                    $userProjects = [];
                    while($proj = $userProjectsResult->fetch_assoc()) {
                        $userProjects[] = htmlspecialchars(ucfirst($proj['title']), ENT_QUOTES, 'UTF-8');
                    }
                    $alljobsByUser[$user_id] = $userProjects;
                }

                $userProjectsJson = htmlspecialchars(json_encode($alljobsByUser[$user_id]), ENT_QUOTES, 'UTF-8');



                echo "<div class='row question-list' style='display: flex; align-items: center; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 20px;'>";

                    echo "<div class='col-auto'>";
                        echo "<a href='#' data-toggle='tooltip' title='User: $username'>";
                            echo "<span class='profile-button' data-user-id='$user_id' data-username='$username' data-projects='$userProjectsJson' data-image='$profile_image' style='cursor:pointer; margin-left: 10px; color: blue;'><img src='$profile_image' alt='Profile' style='width: 30px; height: 30px; border-radius: 50%;'></span>";
                        echo "</a>";
                    echo "</div>";

                    echo "<div class='col'>";
                        echo "<h4 class='my-questions' style='margin: 0;'> 
                            <a href='javascript:void(0);' 
                                class='job-title' 
                                data-title='$name' 
                                data-description=\"" . htmlspecialchars($description, ENT_QUOTES) . "\">
                                $name
                            </a>
                        </h4>";

                        $skills = explode(',', $row['skills']);
                        foreach ($skills as $s) {
                            $skillTag = trim($s);
                            echo "<a href='?skill=" . urlencode($skillTag) . "' class='badge badge-info mr-1'>$skillTag</a>";
                        }
                
                    echo "</div>";

                    echo "<div class='col-auto ml-auto'>";
                        $jobDescQuery = $conn1->prepare("SELECT filename FROM `job-description` WHERE job_id = ? ORDER BY upload_date DESC LIMIT 1");
                        $jobDescQuery->bind_param("i", $id);
                        $jobDescQuery->execute();
                        $descResult = $jobDescQuery->get_result();
                        if ($descResult->num_rows > 0) {
                            $descRow = $descResult->fetch_assoc();
                            $descFile = htmlspecialchars($descRow['filename'], ENT_QUOTES, 'UTF-8');
                            echo "<a href='./server/job-description/$descFile' target='_blank' class='btn btn-sm btn-outline-info' style='margin-bottom: 10px; margin-top: 20px'>View JD</a>";
                        } else {
                        ?>
                            <form action="./server/requests.php" method="post" enctype="multipart/form-data" style="display: inline;">
                                <input type="hidden" name="job_id" value="<?php echo $id; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
    
                                <label for="file-<?php echo $user_id; ?>" class="upload-btn">
                                    Upload JD
                                    <input type="file" id="file-<?php echo $user_id; ?>" name="job_description_file" onchange="this.form.submit()">
                                </label>
                            </form>
                        <?php        
                        }
                    echo "</div>";

                    echo "<div class='col-auto ml-auto'>";
                    if ($_SESSION['user']['user_type'] == "student") {
                        echo "<a href='./server/requests.php?apply=$id' >Apply</a>";
                    }
                    echo "</div>";

                echo "</div>";
            }
            ?>
        </div>

        <div class="col-4">

        <?php
            include('skill-list.php');
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
                <h5>Jobs Offered by ${username}:</h5>
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

    const jobTitleLinks = document.querySelectorAll('.job-title');
    const jobInfoCard = document.getElementById('job-info-card');
    const jobInfoContent = document.getElementById('job-info-card-content');
    const closeJobBtn = document.getElementById('close-job-info-card');

    jobTitleLinks.forEach(link => {
        link.addEventListener('click', function() {
            const title = this.getAttribute('data-title');
            const description = this.getAttribute('data-description') || 'No description provided.';

            jobInfoContent.innerHTML = `
                <h4>${title}</h4>
                <hr>
                <p>${description}</p>
            `;
            jobInfoCard.style.display = 'block';
        });
    });

    closeJobBtn.addEventListener('click', function() {
        jobInfoCard.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === jobInfoCard) {
            jobInfoCard.style.display = 'none';
        }
    });
});
</script>