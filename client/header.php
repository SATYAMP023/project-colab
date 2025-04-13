<nav class="navbar navbar-expand-lg navbar-section">
  <div class="container navbarone" style="max-width: 1235px;">
    <a class="navbar-brand" href="./"><img src="./public/logo.png" alt="" id="logo"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link active nav-button" style="color: white;" href="./">Home</a>
        </li>

        <li class="nav-item">
          <a class="nav-link active nav-button" style="color: white;" href="?latest=true">Latest-Projects</a>
        </li>
        
        <?php
        if(!isset($_SESSION['user_status']['status'])){
        ?>
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?signup=true">Signup</a>
          </li>
        
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?login=true">Login</a>
          </li>
        <?php } ?>

        <?php
        if(isset($_SESSION['user_status']['status'])){
          $user_type = $_SESSION['user']['user_type'];
        ?>
        <?php
        if($user_type === "student"){
        ?>
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?u-id=<?php echo $_SESSION['user']['user_id'] ?>">My-Projects</a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?create=true">Create-Project</a>
          </li>
        <?php  
        }
        if($user_type === "com-rep"){
        ?>
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?user=true">Users</a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?addjob=true">Add-Jobs</a>
          </li>
        <?php
        }
        if($user_type === "com-rep" || $user_type ==="student"){
        ?>
          <li class="nav-item">
            <a class="nav-link active nav-button" style="color: white;" href="?alljob=true">All-Jobs</a>
          </li>
        <?php
        }
        ?>

        <li class="nav-item">
          <a class="nav-link active nav-button" style="color: white;" href="./server/requests.php?logout=true">logout(<?php echo ucfirst($_SESSION['user']['username'])?>) <sup> <?php echo ucfirst($_SESSION['user']['user_type']) ?> </sup></a>
        </li>

      <?php } ?>

      </ul>
    </div>
    <form class="d-flex">
      <input type="text" autocomplete="off" name="search" class="input" placeholder="search here...">
      <button class="button ">
        <img id="search-symbol" src="./public/search.png" alt="search">
        Search
        <div class="hoverEffect">
          <div></div>
        </div>
      </button>
    </form>

    <?php
      $profile_image = "./public/profile-user.png";
      if(isset($_SESSION['user']['user_id'])){
        include('./common/db.php');
        $user_id = $_SESSION['user']['user_id'];

        $profile_query = $conn1->prepare("SELECT filename FROM profileimage WHERE user_id = ?");
        $profile_query->bind_param("i", $user_id);
        $profile_query->execute();
        $profile_result = $profile_query->get_result();

        if ($profile_result->num_rows > 0) {
          $image_row = $profile_result->fetch_assoc();
          $profile_image = "./server/profile/" . $image_row['filename'];
        }
      }
    ?>

    <a class="navbar-brand" href="?profile=<?php 
    if(isset($_SESSION['user']['user_id'])){
      echo $_SESSION['user']['user_id']; 
    }
    else{
      echo NULL;
    }
    ?>"><img src="<?php echo $profile_image; ?>" alt="" style=" width: 45px; height: 45px; border-radius: 50%; margin-left: 10px;"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

  </div>
</nav>