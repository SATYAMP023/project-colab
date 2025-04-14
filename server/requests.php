<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include("../common/db.php");

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function mailsender($R_email, $message){
    $mail = new PHPMailer(true);
    
    try{
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'satyamofficialcuraj@gmail.com';
        $mail->Password = 'kyotnitkngcozfmr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('satyamofficialcuraj@gmail.com','Project Colab');
        $mail->addAddress($R_email);
        $mail->isHTML(true);
        $mail->Subject = $_POST['otp'];
        $mail->Body = $message;
        
        $mail->send();
    }catch(Exception $e){
        $_SESSION['error'] = "Error: " . $mail->ErrorInfo;
        header("Location: index.php");
    }
}
$count = isset($_SESSION['otp_attempts']) ? $_SESSION['otp_attempts'] : 0;

if (isset($_POST["signup"])) {

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $c_password = $_POST["c_password"];
    $phone = $_POST['phone'];
    $usertype = $_POST["user_type"];

    // Error 1: Not checking if inputs are empty before proceeding
    if (empty($username) || empty($email) || empty($password) || empty($c_password) || empty($phone)) {
        $_SESSION['message'] = "Please fill all the credentials.";
        header("Location: /PROJECT-COLAB/?signup=true");
        exit;
    }

    // Check if email already exists
    $sql = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $emailcheck = $sql->get_result();

    if ($emailcheck->num_rows > 0) {
        $_SESSION['message'] = "Email is already registered.";
        header("Location: /PROJECT-COLAB/?signup=true");
        exit;
    }

    if ($password !== $c_password) {
        $_SESSION['message'] = "Passwords do not match. Please try again.";
        header("Location: /PROJECT-COLAB/?signup=true");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $otp = rand(100000, 999999); // Generate OTP

    // Store data temporarily in session
    $_SESSION['user_signup_credential'] = [
        'username' => $username,
        'email' => $email,
        'password' => $hashed_password,
        'user_type' => $usertype,
        'phone' => $phone,
        'otp' => $otp,
        'otp_send_time' => time()
    ];

    $message = "Your OTP verification code for Project-Colab is: " . $otp;
    mailsender($email, $message); // Assume mailsender is defined elsewhere

    header("Location: ../client/verify.php");
    exit;
}

else if (isset($_POST["otp-ver"])) {

    if (!isset($_SESSION['user_signup_credential'])) {
        $_SESSION['message'] = "Session expired. Please try again.";
        header("Location: /PROJECT-COLAB?signup=true");
        exit;
    }

    $c_otp = $_POST['otp'];
    $stored = $_SESSION['user_signup_credential'];

    if ($c_otp == $stored['otp']) {

        // Error 2: SQL Injection risk due to interpolated variables in query
        $stmt = $conn->prepare("INSERT INTO `users` (`username`, `email`, `password`, `user_type`, `phone`, `otp-send-time`) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $stored['username'], $stored['email'], $stored['password'], $stored['user_type'], $stored['phone']);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            $_SESSION['user'] = [
                'username' => $stored['username'],
                'email' => $stored['email'],
                'user_id' => $user_id,
                'user_type' => $stored['user_type']
            ];
            $_SESSION['user_status']['status'] = '1';
            $_SESSION['message'] = "Registration successful.";

            unset($_SESSION['user_signup_credential']); // Clear session

            header("Location: /PROJECT-COLAB");
            exit;
        } else {
            $_SESSION['message'] = "Error in registration. Try again.";
            header("Location: /PROJECT-COLAB/?signup=true");
            exit;
        }

    } else {
        $_SESSION['otp_attempts'] = isset($_SESSION['otp_attempts']) ? $_SESSION['otp_attempts'] + 1 : 1;
    
        if ($_SESSION['otp_attempts'] >= 3) {
            session_unset();
            session_destroy();
            $_SESSION['message'] = "OTP incorrect. You have Reached Attempt maximum limit try again";
            header("Location: /PROJECT-COLAB/?signup=true");
            exit;
        } else {
            $_SESSION['message'] = "OTP incorrect. Attempt {$_SESSION['otp_attempts']} of 3.";
            header("Location: /PROJECT-COLAB/client/verify.php");
            exit;
        }
    }    
}

else if(isset($_POST["login"])){
    $email = $_POST["email"];
    $password = $_POST["password"];
    $username = "";
    $user_id = 0;
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row["password"];
        
        if (password_verify($password, $hashed_password)) {
            $username = $row["username"];
            $user_id = $row["id"];
            $usertype = $row["user_type"];

            $_SESSION['user_status']['status'] = '1';

            $_SESSION['user'] = [
                'username' => $username,
                'email' => $email,
                'user_id' => $user_id,
                'user_type' => $usertype
            ];
            header("location: /PROJECT-COLAB");
            exit;
        } else {
            echo "Incorrect password. Please try again.";
        }
    } else {
        echo "No user found with that email. Please try again.";
    }
}

else if(isset($_POST["create"])){
    $title = $_POST["title"];
    $description = $_POST["description"];
    $category_id = $_POST["category"];
    $user_id = $_SESSION["user"]["user_id"];

        $project = $conn->prepare("Insert into `projects`
        (`id`,`title`,`description`,`category_id`,`user_id`)
        values(NULL,'$title','$description','$category_id','$user_id');
        ");

        $result = $project->execute();
        if($result){
            echo("Your Project Created and Posted");
            header("location: /PROJECT-COLAB");
        }else{
            echo("Project Not Posted");
        }

}

else if(isset($_GET["logout"])){
    session_unset();
    session_destroy();
    header("location: /PROJECT-COLAB");
    exit;
}

else if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {

    if (isset($_SESSION['user']['user_id'])) {
        
        $file = $_FILES['file'];
        $pid = $_POST["project_id"];
        $uid = $_SESSION['user']['user_id'];
        $filename = $_FILES['file']['name'];
        $filesize = $_FILES['file']['size'];
        $filetype = $_FILES['file']['type'];
        
        $max_filesize = 5 * 1024 * 1024;
        
        if ($filesize > $max_filesize) {
            echo "Error: File size exceeds the limit of 5MB.";
        } else {
            
            $upload_dir = "uploads/";
            $filename = basename($file["name"]);
            $target_file = $upload_dir . $filename;
            
            if(move_uploaded_file($file["tmp_name"], $target_file)) {
                $query = $conn1->prepare("Insert into `documents`
                (`id`,`project_id`, `user_id`,`filename`,`filesize`,`filetype` ,`upload_date`)
                values(NULL,'$pid','$uid','$filename','$filesize','$filetype',NOW());
                ");
                
                $result = $query->execute();
                if($result){
                    echo "File uploaded successfully!";
                    header("location: /PROJECT-COLAB?p-id=$pid");
                }else{
                    echo "Error uploading file.";
                }
            } else {
                echo "Error uploading file.";
            }
        }
    }
    else {
        echo "please Login first to upload file";
    }
}

else if(isset($_GET["delete"])){
    echo $pid = $_GET["delete"];
    $query = $conn->prepare("delete from projects where id = $pid");
    $result = $query->execute();
    if($result){
        echo("Project Deleted");
        header("location: /PROJECT-COLAB");
    }else{
        echo("Project not Deleted error occured");
    }
}

else if(isset($_GET["deletecomment"])){
    $comment_id = intval($_GET["deletecomment"]);

    $query = $conn->prepare("SELECT project_id FROM comments WHERE id = ?");
    $query->bind_param("i", $comment_id);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $project_id = $row["project_id"];

        $deleteQuery = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $deleteQuery->bind_param("i", $comment_id);
        $deleteResult = $deleteQuery->execute();

        if ($deleteResult) {
            echo "Comment Deleted";
            header("Location: /project-colab?p-id=" . $project_id);
            exit;
        } else {
            echo "Comment not deleted. An error occurred.";
        }
    } else {
        echo "Comment not found.";
    }
}

else if(isset($_GET["deletefile"])){
    echo $fid = $_GET["deletefile"];
    $query = $conn1->prepare("delete from documents where id = $fid");
    $result = $query->execute();
        if($result){
            echo("File Deleted");
            header("location: /PROJECT-COLAB");
        }else{
            echo("File not Deleted error occured");
        }
}

else if(isset($_POST["comment"])){
    $comment = $_POST["comment"];
    $project_id = $_POST["project_id"];
    $user_id = $_SESSION["user"]["user_id"];
    echo ($comment);
    echo ($project_id);
    echo ($user_id);

        $query = $conn->prepare("Insert into `comments`
        (`id`,`comment`,`project_id`,`user_id`)
        values(NULL,'$comment','$project_id','$user_id');
        ");

        $result = $query->execute();
        if($result){
            echo("Your Comment Posted Thank you for your contribution..");
            header("location: /project-colab?p-id=$project_id");
        }else{
            echo("Comment not submitted Try again");
        }

}

if(isset($_POST["update_password"])){
    $user_id = intval($_POST['user_id']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

    $query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $email = $row["email"];
        $hashed_password = $row['password'];
    } else {
        echo "user not found.";
    }

    if (password_verify($current_password, $hashed_password)) {
        $randomNumber = rand(100000, 999999);
        $_SESSION['user']['otp'] = $randomNumber;
        $_SESSION['user']['new_pass'] = $hash_password;
                   
        $message = "your OTP verification code for Project-Colab Password change is :". $randomNumber;
        mailsender($email, $message);
        header("Location: ../client/verifypc.php");
        exit;
    } else {
        echo "Incorrect password. Please try again.";
    }

}

else if(isset($_POST["otp-verpc"])){

    if (!isset($_SESSION['user'])) {
        $_SESSION['message'] = "Session expired. Please try again.";
        header("Location: /PROJECT-COLAB?signup=true");
        exit;
    }

    $c_otp = $_POST['otp'];
    $otp = $_SESSION['user']['otp'];
    $id = $_SESSION['user']['user_id'];
    $hash_password = $_SESSION['user']['new_pass'];
    
    if ($c_otp == $otp) {
        
        $stmt = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash_password, $id);
        $result = $stmt->execute();

        if($result){
            unset($_SESSION['user']['otp']);
            
            $_SESSION['otp_attempts'] = 0;
            
            $_SESSION['message'] = "Password Updated successfully!";
            header("Location: /PROJECT-COLAB");
            exit;
        }else{
            $_SESSION['message'] = "Password Not Updated TRY AGAIN";
            header("Location: /PROJECT-COLAB/index.php?change-password");
            exit;
        }
        
    }
    else {
        $_SESSION['otp_attempts'] = isset($_SESSION['otp_attempts']) ? $_SESSION['otp_attempts'] + 1 : 1;

        if ($count >= 3) {
            $_SESSION['otp_attempts'] = 0;
            $_SESSION['message'] = "OTP incorrect. You have Reached Attempt maximum limit try again";
            header("Location: ../index.php");            
            exit;
        } else {
            $_SESSION['message'] = "OTP incorrect. Attempt {$_SESSION['otp_attempts']} of 3.";
            header("Location: /PROJECT-COLAB/client/verify.php");
            exit;
        }
    }
}

if(isset($_POST["update_forget_password"])){
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

    $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        $randomNumber = rand(100000, 999999);
        $_SESSION['user'] = [
            'otp' => $randomNumber,
            'user_id' => $user['id'],
            'new_pass' => $hash_password,
            'email' => $email
        ];
                   
        $message = "your OTP verification code for Project-Colab Forgot Password is :". $randomNumber;
        mailsender($email, $message);
        header("Location: ../client/verifypc.php");
        exit;
    } else {
        echo "user not found.";
    }

}

else if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

    if (isset($_SESSION['user']['user_id'])) {

        $file = $_FILES['image'];
        $uid = $_SESSION['user']['user_id'];
        $filename = $file['name'];
        $filesize = $file['size'];
        $filetype = $file['type'];

        $allowed_extensions = ['jpg', 'jpeg'];
        $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/pjpeg'];

        $max_filesize = 5 * 1024 * 1024; // 5MB

        $upload_dir = "profile/";
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed_extensions) || !in_array($filetype, $allowed_mime_types)) {
            echo "Error: Only JPG and JPEG image files are allowed.";
        } elseif ($filesize > $max_filesize) {
            echo "Error: File size exceeds the limit of 5MB.";
        } else {
            $new_filename = "user_" . $uid . "." . $extension;
            $target_file = $upload_dir . $new_filename;

            $sql = $conn1->prepare("SELECT * FROM `profileimage` WHERE user_id = ?");
            $sql->bind_param("i", $uid);
            $sql->execute();
            $imagecheck = $sql->get_result();

            if ($imagecheck->num_rows > 0) {
                $row = $imagecheck->fetch_assoc();
                $oldfilename = $row['filename'];
                $oldfilepath = $upload_dir . $oldfilename;

                if (file_exists($oldfilepath)) {
                    unlink($oldfilepath);
                }

                $delete_query = $conn1->prepare("DELETE FROM `profileimage` WHERE user_id = ?");
                $delete_query->bind_param("i", $uid);
                $delete_query->execute();
            }

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $query = $conn1->prepare("INSERT INTO `profileimage`
                    (`id`, `user_id`, `filename`, `upload_date`)
                    VALUES (NULL, ?, ?, NOW())");
                $query->bind_param("is", $uid, $new_filename);

                if ($query->execute()) {
                    $_SESSION['message'] = "Image Uploaded successfully!";
                    header("Location: /PROJECT-COLAB?profile=$uid");
                    exit;
                } else {
                    echo "Error: Could not save file in database.";
                }
            } else {
                echo "Error: Failed to move uploaded file.";
            }
        }
    } else {
        echo "Please login first to upload file.";
    }
}

else if(isset($_POST["createjob"])){
    $title = $_POST["title"];
    $companyname = $_POST["companyname"];
    $description = $_POST["description"];
    $skills = $_POST["skills"];
    $category_id = $_POST["category"];
    $user_id = $_SESSION["user"]["user_id"];

        $project = $conn->prepare("Insert into `jobs`
        (`id`,`title`,`companyname`,`description`,`skills`,`category_id`,`user_id`)
        values(NULL,'$title','$companyname','$description','$skills','$category_id','$user_id');
        ");

        $result = $project->execute();
        if($result){
            echo("Your Job Posted");
            header("location: /PROJECT-COLAB");
        }else{
            echo("Job Not Posted");
        }

}

else {
    //
}

?>