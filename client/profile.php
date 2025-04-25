<div class="container py-5">

  <?php
    if (isset($_SESSION['message'])) {
      echo '<div class="alert alert-info text-center">' . htmlspecialchars($_SESSION['message']) . '</div>';
      unset($_SESSION['message']);
    }

    if (isset($_SESSION['user']['user_id'])) {
      include('./common/db.php');
      $user_id = (int) $_SESSION['user']['user_id'];
      $query = "SELECT * FROM users WHERE id = $user_id";
      $result = $conn->query($query); 
      $row = $result->fetch_assoc();

      $username = htmlspecialchars($row['username']);
      $email = htmlspecialchars($row['email']);
      $phone = htmlspecialchars($row['phone']);


      $queryskill = $conn->prepare("SELECT skill FROM skills WHERE user_id = ?");
      $queryskill->bind_param("i", $user_id);
      $queryskill->execute();
      $resultskill = $queryskill->get_result();
      $skills = "";
      if ($rowskill = $resultskill->fetch_assoc()) {
        $skills = htmlspecialchars($rowskill['skill']);
      }

      $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
      $profile_query->bind_param("i", $user_id);
      $profile_query->execute();
      $profile_result = $profile_query->get_result();
      $profile_image = "./public/profile-user.png";

      if ($profile_result->num_rows > 0) {
        $image_row = $profile_result->fetch_assoc();
        $profile_image = "./server/profile/" . htmlspecialchars($image_row['filename']);
      }
  ?>

      <div class="text-center mb-4">
        <img src="<?php echo $profile_image; ?>" alt="Profile Picture" class="rounded-circle shadow" width="150" height="150">
    
        <form action="./server/requests.php" method="POST" enctype="multipart/form-data" class="mt-4">
          <input type="hidden" name="user_id" value="<?php echo $user_id ?>">

          <div class="mb-2 w-50 mx-auto">
            <label for="image" class="form-label fw-bold" style="color: white;">Select Image</label>
            <input type="file" class="form-control" name="image" id="image">
          </div>

          <button type="submit" class="btn btn-primary px-4">
            <?php
            if ($profile_image != "./public/profile-user.png") {
              ?>
              Change Image
            <?php
            }else {
            ?>
              Upload Image
            <?php
            } 
            ?>
          </button>
        </form>
      </div>

      <h1 class="text-center mt-4 text-light fw-semibold"><?php echo htmlspecialchars(ucfirst($username)); ?></h1>

      <div class="mt-4 text-center text-light">
        
        <?php if (!empty($skills)): ?>
          <div class="mt-3">
            <p><strong>Skills:</strong></p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <?php 
              $skillsArray = explode(',', $skills);
              foreach ($skillsArray as $skill) {
                $skill = trim($skill);
                echo "<span class='badge bg-success'>$skill</span>";
              }
              ?>
            </div>
          </div>
        <?php else: ?>
          <p><strong>Skills:</strong> Not added</p>
        <?php endif; ?>

        <div class="text-center mt-3">
          <button class="btn btn-outline-light" onclick="document.getElementById('skill-form').style.display='block'">+ Add Skill</button>
        </div>

        <!-- Skill Form (Hidden Initially) -->
        <div id="skill-form" style="display: none;" class="mt-3">
          <form action="./server/requests.php" method="POST" class="text-center">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <div class="mb-2 w-50 mx-auto">
              <label for="new-skill" class="form-label fw-bold text-white">Enter new skill:</label>
              <input type="text" name="new_skill" id="new-skill" class="form-control" placeholder="e.g., Python, React" required>
            </div>
            <button type="submit" name="add_skill" class="btn btn-success">Save Skill</button>
          </form>
        </div>  

        <!-- Resume Display Section -->
        <?php
        $resume_query = $conn1->prepare("SELECT id, filename FROM resumes WHERE user_id = ?");
        $resume_query->bind_param("i", $user_id);
        $resume_query->execute();
        $resume_result = $resume_query->get_result();
        $resume_file = "";
        $resume_id = null;

        if ($resume_result->num_rows > 0) {
          $resume_row = $resume_result->fetch_assoc();
          $resume_file = "./server/resumes/" . htmlspecialchars($resume_row['filename']);
          $resume_id = (int) $resume_row['id'];
        }
        ?>

        <div class="mt-4" style="color: white;">
          <p><strong>Resume:</strong></p>
          <?php if (!empty($resume_file)): ?>
            <a href="client/view_resume.php?file_id=<?php echo $resume_id; ?>" class="btn btn-outline-info" target="_blank">View / Download Resume</a>
          <?php else: ?>
            <p>Resume not uploaded yet.</p>
          <?php endif; ?>
        </div>

        <!-- Resume Upload Button -->
        <div class="text-center mt-3" style="color: white;">
          <button class="btn btn-outline-light" onclick="document.getElementById('resume-form').style.display='block'">+ Upload Resume</button>
        </div>

        <!-- Resume Form (Hidden Initially) -->
        <div id="resume-form" style="display: none; color: white;" class="mt-3">
          <form action="./server/requests.php" method="POST" class="text-center" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <div class="mb-2 w-50 mx-auto">
              <label for="resume" class="form-label fw-bold text-white">Upload Your Resume:</label>
              <input type="file" name="resume" id="resume" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>
            <button type="submit" name="upload_resume" class="btn btn-primary">Upload Resume</button>
          </form>
        </div>
                  
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
        <a href="index.php?change-password" class="btn btn-warning px-4 mt-3">Change Password</a>
      </div>
    </div>


  <?php } else {
     echo "<div class='alert alert-warning'>You must be logged in to view your profile.</div>";
  } 
  ?>
</div>
