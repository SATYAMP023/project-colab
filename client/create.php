<div class="container" style="color: white;">
<?php
if (isset($_SESSION['message'])) {
  echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . '</div>';
  unset($_SESSION['message']);
}
?>
  <h1 class="heading">Create New Project</h1>
  <form method="POST" action="./server/requests.php">

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="title" class="form-label">Title of Project</label>
    <input type="text" name="title" class="form-control" id="title" placeholder="Enter Your Project Name" required maxlength="100">
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" class="form-control" id="descripton" placeholder="Description" required maxlength="1000"></textarea>
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="category" class="form-label">Category</label>
    
    <?php
    include("category.php");
    ?>
  </div>

  <div class="col-6 offset-sm-8">
    <button type="submit" name="create" class="btn btn-primary">submit</button>
  </div>

  </form>
</div>