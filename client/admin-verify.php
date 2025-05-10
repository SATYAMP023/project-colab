<div class="container container-signup">
<?php
if (isset($_SESSION['message'])) {
  echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
  unset($_SESSION['message']);
}
?>
    <h1 class="heading">Admin-Login</h1>
    <form method="POST" action="./server/requests.php" id="login-form">
      
    <div class="row">
      <div class="col-10 offset-sm-1 margin-bottom-15">
        <label for="keyword" class="form-label">Admin Keyword <SUp>*</SUp></label>
        <input type="password" name="keyword" class="form-control" id="keyword" placeholder="Enter Your Keyword" required>
      </div>
    </div>
      
  <div class="signup-button">
    
    <div class="col-2 offset-sm-9">
      <button type="submit" name="sent" class="btn btn-primary">Sent</button>
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