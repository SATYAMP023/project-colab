<div class="container">
  <h1 class="heading">Create New Project</h1>
  <form method="POST" action="./server/requests.php">

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="title" class="form-label">Title of Project</label>
    <input type="text" name="title" class="form-control" id="title" placeholder="Enter Your Project Name">
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" class="form-control" id="descripton" placeholder="Description"></textarea>
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="category" class="form-label">Category</label>
    
    <?php
    include("category.php");
    ?>
  </div>

  <div class="col-6 offset-sm-3">
    <button type="submit" name="create" class="btn btn-primary">submit</button>
  </div>

  </form>
</div>