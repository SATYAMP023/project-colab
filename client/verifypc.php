
<?php include('commonfiles.php') ?>

<div class="container">
    <h1 class="heading">OTP Verification to Change Your Old Password</h1>
    <form method="POST" action="../server/requests.php">

<div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="otp" class="form-label">Enter OTP <SUp>*</SUp></label>
    <input type="number" name="otp" class="form-control" id="otp-id" placeholder="Enter OTP">
</div>

<div class="signup-button">
    <div class="col-2 offset-sm-3 margin-bottom-15">
      <button type="submit" name="otp-verpc" class="btn btn-primary">Submit</button>
    </div>
</div>

<style>
.heading{
    text-align: center;
}
.margin-bottom-15{
  margin-bottom: 15px;
}
</style>

</form>
</div>