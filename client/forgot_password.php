<div class="container py-5">
  <h2 class="text-center mb-4">Forgot Password</h2>

  <?php
    if (isset($_SESSION['message'])) {
      echo '<div class="alert alert-info text-center">' . $_SESSION['message'] . '</div>';
      unset($_SESSION['message']);
    }
  ?>

  <form method="POST" action="./server/requests.php" class="w-50 mx-auto" id="forgot-password">

    <div class="mb-3">
        <label for="email" class="form-label fw-bold">User Email </label>
        <input type="email" name="email" class="form-control" id="email" placeholder="Enter User Email" required>
        <small id="emailcorrectness" style="color: red; display: none;"></small>
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
      <button type="submit" name="update_forget_password" class="btn btn-primary">Update Password</button>
    </div>
  </form>

  <div class="text-center mt-4">
    <a href="index.php?login=true" class="btn btn-secondary">Back to Login</a>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('forgot-password');
    const emailField = document.getElementById("email");
    const emailFeedback = document.getElementById("emailcorrectness");
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('c_password');
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');

    // Prevent copy, cut, and paste in password fields
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

    // Password validation criteria
    function validatePassword(password) {
        const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return passwordPattern.test(password);
    }

    function emailchecker() {
        const emailValue = emailField.value;
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Valid email pattern

        if (emailValue === "") {
            emailFeedback.style.display = "none"; // Hide feedback when empty
        } else if (pattern.test(emailValue)) {
            emailFeedback.style.display = "block";
            emailFeedback.style.color = "green";
            emailFeedback.textContent = "Good to go";
        } else {
            emailFeedback.style.display = "block";
            emailFeedback.style.color = "red";
            emailFeedback.textContent = "Enter a valid email address";
        }
    }

    emailField.addEventListener("input", emailchecker);

    // Confirm password match
    function confirmPasswordsMatch(password, confirmPassword) {
        return password === confirmPassword;
    }

    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validate password
        if (!validatePassword(passwordField.value)) {
            passwordError.style.display = 'block';
            isValid = false;
        } else {
            passwordError.style.display = 'none';
        }

        // Validate confirm password
        if (!confirmPasswordsMatch(passwordField.value, confirmPasswordField.value)) {
            confirmPasswordError.style.display = 'block';
            isValid = false;
        } else {
            confirmPasswordError.style.display = 'none';
        }

        if (!isValid) {
            e.preventDefault(); // Prevent form submission if validation fails
        }
    });
});
  </script>