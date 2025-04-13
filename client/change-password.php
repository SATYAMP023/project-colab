<?php
if (!isset($_SESSION['user']['user_id'])) {
  echo "<div class='alert alert-danger'>You must be logged in to change your password.</div>";
  exit;
}
?>

<div class="container py-5">
  <h2 class="text-center mb-4 heading">Change Password</h2>

  <?php
    if (isset($_SESSION['message'])) {
      echo '<div class="alert alert-info text-center">' . $_SESSION['message'] . '</div>';
      unset($_SESSION['message']);
    }
  ?>

  <form method="POST" action="./server/requests.php" class="w-50 mx-auto" id="password-change">
    <input type="hidden" name="action" value="change_password">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['user_id']; ?>">
    <div class="row">
      <div class="mb-3">
        <label class="form-label fw-bold">Current Password</label>
        <input type="password" class="form-control" id="old_password" name="current_password" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-bold">New Password</label>
        <input type="password" class="form-control" id="password" name="new_password" required>
        <small id="password-error" style="color: red; display: none;">Password must contain at least one uppercase letter, one number, and one special character.</small>
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-bold">Confirm New Password</label>
        <input type="password" class="form-control" id="c_password" name="confirm_password" required>
        <small id="confirm-password-error" style="color: red; display: none;">Passwords don't match.</small>
        
      </div>
      
      <div class="d-grid">
        <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
      </div>
    </form>
    
    <div class="text-center mt-4">
      <a href="index.php?profile=<?php echo $_SESSION['user']['user_id']; ?>" class="btn btn-secondary">Back to Profile</a>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('password-change');
    const oldpasswordField = document.getElementById('old_password');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('c_password');
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');

    const passwordFields = document.querySelectorAll("input[type='password']");
    passwordFields.forEach(function(field) {
      field.addEventListener("copy", function(e) {
        e.preventDefault();
        alert("Copying is not allowed!");
      });

      field.addEventListener("cut", function(e) {
        e.preventDefault();
        alert("Cutting is not allowed!");
      });

      field.addEventListener("paste", function(e) {
        e.preventDefault();
        alert("Pasting is not allowed!");
      });

      field.setAttribute("oncopy", "return false");
      field.setAttribute("onpaste", "return false");
      field.setAttribute("oncut", "return false");
    });

    function validatePassword(password) {
      const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
      return passwordPattern.test(password);
    }

    function confirmPasswordsMatch(password, confirmPassword) {
      return password === confirmPassword;
    }

    form.addEventListener('submit', function(e) {
      let isValid = true;

      if (!validatePassword(passwordField.value)) {
        passwordError.style.display = 'block';
        isValid = false;
      } else {
        passwordError.style.display = 'none';
      }

      if (!confirmPasswordsMatch(passwordField.value, confirmPasswordField.value)) {
        confirmPasswordError.style.display = 'block';
        isValid = false;
      } else {
        confirmPasswordError.style.display = 'none';
      }

      if (oldpasswordField.value === passwordField.value) {
        confirmPasswordError.textContent = "New password must be different from the current password.";
        confirmPasswordError.style.display = 'block';
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  });
</script>