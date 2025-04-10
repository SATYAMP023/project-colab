<?php
session_start();
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

        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }

        if(isset($_GET['signup']))
        {
            include('./client/signup.php');
        }

        elseif(isset($_GET['login']))
        {
            include('./client/login.php');
        }

        else if(isset($_GET['u-id'])){
            $uid = $_GET['u-id'];
            include ('./client/projects.php');
        }

        else if(isset($_GET['create'])){
            include ('./client/create.php');
        }

        else if(isset($_GET['latest'])){
            include ('./client/projects.php');
        }

        else if(isset($_GET['p-id'])){
            $pid = $_GET['p-id'];
            include ('./client/projects-detail.php');
        }

        else if(isset($_GET['c-id'])){
            $cid = $_GET['c-id'];
            include ('./client/projects.php');
        }

        else if(isset($_GET['search'])){
            $search = $_GET['search'];
            include ('./client/projects.php');
        }

        else if(isset($_GET['profile'])){
            $user_id = $_GET['profile'];
            include ('./client/profile.php');
        }

        else if(isset($_GET['change-password'])) {
            include('./client/change-password.php');
        }

        else if(isset($_GET['forgot-password'])) {
            include('./client/forgot_password.php');
        }

        else if(isset($_GET['user'])){
            include ('./client/users.php');
        }

        else if(isset($_GET['addjob'])){
            include ('./client/jobs.php');
        }
    
        else if(isset($_GET['alljob'])){
            include ('./client/showjobs.php');
        }

        else {
            include('./client/projects.php');
        }
        ?>

    </body>
</html>