<?php
session_start();
include("./common/db.php");

function logUserAction($conn1, $user_id, $action, $ip_address) {
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $user_id, $ip_address, $action);
    $log_stmt->execute();
}

// Log user action (visiting pages)
$ip_address = $_SERVER['REMOTE_ADDR'];
if ($ip_address === '::1') {
    $ip_address = '127.0.0.1';
}
$ip_address = (string)$ip_address;
$user_id = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project-Colab</title>
        <?php include('./client/commonfiles.php') ?>
    </head>
    <body>
        <?php
        include('./client/header.php');

        ?>
        <hr>
        <style>
        hr {
            border: none;
            border-top: 10px solid white; 
            margin: 5px 0; 
        }
    </style>
        <?php

        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }

        if(isset($_GET['signup']))
        {
            $user_id = 0;
            logUserAction($conn1, $user_id, "Visited signup page", $ip_address);
            include('./client/signup.php');
        }

        elseif(isset($_GET['login']))
        {
            $user_id = 0;
            logUserAction($conn1, $user_id, "Visited login page", $ip_address);    
            include('./client/login.php');
        }

        else if(isset($_GET['u-id'])){
            $uid = $_GET['u-id'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed user profile with ID $uid", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Viewed user profile with ID $uid", $ip_address);
            }
            include ('./client/projects.php');
        }

        else if(isset($_GET['create'])){
            if ($user_id) {
                logUserAction($conn1, $user_id, "Visited create project page", $ip_address);
            }
            include ('./client/create.php');
        }

        else if(isset($_GET['latest'])){
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed latest projects", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Viewed latest projects", $ip_address);
            }
            include ('./client/projects.php');
        }

        else if(isset($_GET['p-id'])){
            $pid = $_GET['p-id'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed project details with ID $pid", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Viewed project details with ID $pid", $ip_address);
            }
            include ('./client/projects-detail.php');
        }
        
        else if(isset($_GET['c-id'])){
            $cid = $_GET['c-id'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed project with category ID $cid", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Viewed project with category ID $cid", $ip_address);
            }
            include ('./client/projects.php');
        }
        
        else if(isset($_GET['search'])){
            $search = $_GET['search'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Searched for projects with keyword $search", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Searched for projects with keyword $search", $ip_address);
            }
            include ('./client/projects.php');
        }
        
        else if(isset($_GET['profile'])){
            $user_id = $_GET['profile'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed profile with ID $user_id", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Viewed profile with ID $user_id", $ip_address);
            }
            if ($user_id === 55555) {
                //
            }
            else{
                include ('./client/profile.php');
            }
        }
        
        else if(isset($_GET['change-password'])) {
            if ($user_id) {
                logUserAction($conn1, $user_id, "Visited change password page", $ip_address);
            }
            include('./client/change-password.php');
        }
        
        else if(isset($_GET['forgot-password'])) {
            if ($user_id) {
                logUserAction($conn1, $user_id, "Visited forgot password page", $ip_address);
            }
            else{
                $user_id = 0;
                logUserAction($conn1, $user_id, "Visited forgot password page", $ip_address);
            }
            include('./client/forgot_password.php');
        }
        
        else if(isset($_GET['user'])){
            if ($user_id) {
                logUserAction($conn1, $user_id, "Visited user management page", $ip_address);
            }
            include ('./client/users.php');
        }
        
        else if(isset($_GET['addjob'])){
            if ($user_id) {
                logUserAction($conn1, $user_id, "Visited add job page", $ip_address);
            }
            include ('./client/jobs.php');
        }
        
        else if(isset($_GET['alljob'])){
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed all job listings", $ip_address);
            }
            include ('./client/showjobs.php');
        }
        
        else if(isset($_GET['job-id'])){
            $job_id = $_GET['job-id'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed job form for job ID $job_id", $ip_address);
            }
            include ('./client/job-form.php');
        }

        else if(isset($_GET['skill'])){
            $skill = $_GET['skill'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Searched for jobs with skill $skill", $ip_address);
            }
            include ('./client/showjobs.php');
        }
        
        else if(isset($_GET['userskill'])){
            $skill = $_GET['userskill'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Searched for users with skill $skill", $ip_address);
            }
            include ('./client/users.php');
        }

        else if(isset($_GET['project-id'])){
            $project_id = $_GET['project-id'];
            if ($user_id) {
                logUserAction($conn1, $user_id, "Viewed project group with ID $project_id", $ip_address);
            }
            include ('./client/groups.php');
        }

        else if(isset($_GET['admin-login'])) {
            $user_id = 0;
            logUserAction($conn1, $user_id, "Visited admin login page", $ip_address);
            include('./client/admin-verify.php');
        }

        else if(isset($_GET['logging'])){
            include ('./client/Loggings.php');
        }

        else {
            if ($user_id) {
                logUserAction($conn1, $user_id, "Visited projects page", $ip_address);
            }
            include('./client/projects.php');
        }
        ?>

</body>
</html>