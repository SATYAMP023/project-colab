<div class="container py-5">

  <?php
    if (isset($_SESSION['message'])) {
      echo '<div class="alert alert-info text-center">' . $_SESSION['message'] . '</div>';
      unset($_SESSION['message']);
    }

    if (isset($_SESSION['user']['user_id'])) {
      include('./common/db.php');
      $user_id = $_SESSION['user']['user_id'];
      $query = "SELECT * FROM users WHERE id = $user_id";
      $result = $conn->query($query);
      $row = $result->fetch_assoc();
      $username = $row['username'];
      $email = $row['email'];
      $phone = $row['phone'];


      $queryskill = "SELECT * FROM skills WHERE user_id = $user_id";
      $resultskill = $conn->query($queryskill);
      $rowskill = $resultskill->fetch_assoc();
      $skills = "";
      if ($rowskill) {
        $skills = $rowskill['skill'];
      }

      $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
      $profile_query->bind_param("i", $user_id);
      $profile_query->execute();
      $profile_result = $profile_query->get_result();
      $profile_image = "./public/profile-user.png";

      if ($profile_result->num_rows > 0) {
        $image_row = $profile_result->fetch_assoc();
        $profile_image = "./server/profile/" . $image_row['filename'];
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
