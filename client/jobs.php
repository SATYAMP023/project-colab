<div class="container">
<?php
session_start();
if (isset($_SESSION['message'])) {
  echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
  unset($_SESSION['message']);
}
?>
    <h1 class="heading">Add New Job Offers</h1>
    <form method="POST" action="./server/requests.php">

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="title" class="form-label">Title of Job</label>
    <input type="text" name="title" class="form-control" id="title" placeholder="Enter The Job Name">
  </div>
  
  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="companyname" class="form-label">Name of Company</label>
    <input type="text" name="companyname" class="form-control" id="companyname" placeholder="Enter The Company Name">
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" class="form-control" id="descripton" placeholder="Description"></textarea>
  </div>
  
  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="skills" class="form-label">Skills Required</label>
    <textarea name="skills" class="form-control" id="skills" placeholder="Skills"></textarea>
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="category" class="form-label">Category</label>
    
    <?php
    include("category.php");
    ?>
  </div>

  <div class="col-6 offset-sm-3">
    <button type="submit" name="createjob" class="btn btn-primary">submit</button>
  </div>

  </form>
</div>