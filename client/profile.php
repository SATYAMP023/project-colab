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


      $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
      $profile_query->bind_param("i", $user_id);
      $profile_query->execute();
      $profile_result = $profile_query->get_result();
      $profile_image = "./public/profile-user.png"; // Default image

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
            <label for="image" class="form-label fw-bold">Select Image</label>
            <input type="file" class="form-control" name="image" id="image">
          </div>

          <button type="submit" class="btn btn-primary px-4">Upload Image</button>
        </form>
      </div>

      <h1 class="text-center mt-4 text-dark fw-semibold"><?php echo htmlspecialchars($username); ?></h1>

      <div class="mt-4 text-center">
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
