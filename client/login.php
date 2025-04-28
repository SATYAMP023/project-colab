<div class="container container-signup">
<?php
if (isset($_SESSION['message'])) {
  echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
  unset($_SESSION['message']);
}
?>
    <h1 class="heading">Login</h1>
    <form method="POST" action="./server/requests.php" id="login-form">
      
    <div class="row">
      <div class="col-10 offset-sm-1 margin-bottom-15">
        <label for="email" class="form-label">User Email <SUp>*</SUp></label>
        <input type="email" name="email" class="form-control" id="email" placeholder="Enter Your Registered Email" required>
      </div>
      
      <div class="col-10 offset-sm-1 margin-bottom-15">
        <label for="password" class="form-label">Password <SUp>*</SUp></label>
        <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
    
        <div class="text-end mt-2">
          <a href="index.php?forgot-password" class="text-decoration-none">Forgot Password?</a>
        </div>
      </div>
    </div>
      
  <div class="signup-button">
    
    <div class="col-2 offset-sm-9">
      <button type="submit" name="login" class="btn btn-primary">login</button>
    </div>
    
  </div>

  </form>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  if (!email || !password) {
    alert('Please fill in all fields.');
    e.preventDefault();
    return;
  }

  if (password.length < 8) {
    alert('Password must be at least 8 characters.');
    e.preventDefault();
    return;
  }
});
</script>