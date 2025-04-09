<div class="container">
    <h1 class="heading">signup</h1>
    <form method="POST" action="./server/requests.php" id="signup-form">

    <div class="row">
      <div class="col-6 offset-sm-3 margin-bottom-15" >
        <label for="username" class="form-label">User Name <SUp>*</SUp></label>
        <input type="text" name="username" class="form-control" id="username" placeholder="Enter Your name" required>
      </div>
      
      <div class="col-6 offset-sm-3 margin-bottom-15">
        <label for="email" class="form-label">User Email <SUp>*</SUp></label>
        <input type="email" name="email" class="form-control" id="email" placeholder="Enter User Email" required>
        <small id="emailcorrectness" style="color: red; display: none;"></small>
      </div>
      
      <div class="col-6 offset-sm-3 margin-bottom-15">
        <label for="password" class="form-label">Password <SUp>*</SUp></label>
        <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
        <small id="password-error" style="color: red; display: none;">Password must contain at least one uppercase letter, one number, and one special character.</small>
      </div>

      <div class="col-6 offset-sm-3 margin-bottom-15">
        <label for="password" class="form-label">Confirm Your Password <SUp>*</SUp></label>
        <input type="password" name="c_password" class="form-control" id="c_password" placeholder="Confirm your password" required>
        <small id="confirm-password-error" style="color: red; display: none;">Passwords don't match.</small>
      </div>
      
      <div class="col-6 offset-sm-3 margin-bottom-15">
        <label for="phone" class="form-label">Enter Your Phone Number <SUp>*</SUp></label>
        <input type="number" name="phone" class="form-control" id="phone-id" placeholder="Enter your phone number" required>
      </div>

      <div class="col-6 offset-sm-3 margin-bottom-15">
        <label for="User Type" class="form-label">Choose Your Profession</label>
        <select name="user_type" id="user_type" class="form-control">
          <option value="student">Student</option>
          <option value="faculty">Faculty/Mentors</option>
          <option value="com-rep">Company Representative</option>
        </select>
      </div>
    </div>
  
  <div class="signup-button">
    
    <div class="col-2 offset-sm-3">
      <button type="submit" name="signup" class="btn btn-primary">signup</button>
    </div>
    
  </div>

  </form>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('signup-form');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('c_password');
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');
    const emailField = document.getElementById("email");
    const emailFeedback = document.getElementById("emailcorrectness");

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