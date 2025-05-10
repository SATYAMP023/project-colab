<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include("../common/db.php");

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function mailsender($R_email, $message, $subject){
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
        $mail->Subject = $subject;
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
    $phone = trim(preg_replace('/[^0-9]/', '', $_POST['phone']));
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
    $subject = "Registration otp verification";
    mailsender($email, $message, $subject); 

    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $working_message = "Signup attempt initiated from IP: $ip_address,from Email: $email";

    $working_message = (string)$working_message;
    $ip_address = (string)$ip_address;
    $temp_user_id = 0;
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, NOW())");
    $temp_user_id = 0; 
    $log_stmt->bind_param("iss", $temp_user_id, $ip_address, $working_message);
    $log_stmt->execute();

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

            $ip_address = $_SERVER['REMOTE_ADDR'];
            if ($ip_address === '::1') {
                $ip_address = '127.0.0.1';
            }
            $ip_address = (string)$ip_address;
            $working_message = "OTP verified successfully and account created. IP: $ip_address, Email: {$stored['email']}";
            $working_message = (string)$working_message;
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
            $log_stmt->execute();

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

            $ip_address = $_SERVER['REMOTE_ADDR'];
            if ($ip_address === '::1') {
                $ip_address = '127.0.0.1';
            }
            $ip_address = (string)$ip_address;
            $working_message = "OTP verification failed after 3 attempts. IP: $ip_address, Email: {$stored['email']}";
            $working_message = (string)$working_message;
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $temp_user_id = 0;
            $log_stmt->bind_param("iss", $temp_user_id, $ip_address, $working_message);
            $log_stmt->execute();

            session_unset();
            session_destroy();
            $_SESSION['message'] = "OTP incorrect. You have Reached Attempt maximum limit try again";
            header("Location: /PROJECT-COLAB/?signup=true");
            exit;
        } else {

            $ip_address = $_SERVER['REMOTE_ADDR'];
            if ($ip_address === '::1') {
                $ip_address = '127.0.0.1';
            }
            $ip_address = (string)$ip_address;
            $working_message = "OTP verification failed. Attempt {$_SESSION['otp_attempts']} of 3. IP: $ip_address, Email: {$stored['email']}";
            $working_message = (string)$working_message;
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $temp_user_id = 0;
            $log_stmt->bind_param("iss", $temp_user_id, $ip_address, $working_message);
            $log_stmt->execute();
            
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

    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    
    $working_message = "Login attempt initiated from IP: $ip_address, with Email: $email";
    $working_message = (string)$working_message;
    $ip_address = (string)$ip_address;
    $temp_user_id = 0;
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $temp_user_id, $ip_address, $working_message);
    $log_stmt->execute();


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

            $successful_login_message = "Successful login from Email: $email";
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $user_id, $ip_address, $successful_login_message);
            $log_stmt->execute();

            header("location: /PROJECT-COLAB");
            exit;
        } else {

            $working_message = "Failed login attempt - Incorrect password for Email: $email";
            $working_message = (string)$working_message;
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", 0, $ip_address, $working_message);
            $log_stmt->execute();

            $_SESSION['message'] = "Incorrect password. Please try again.";
            header("location: /PROJECT-COLAB/?login=true");
            exit;
        }
    } else {

        $working_message = "Failed login attempt - No user found with Email: $email";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", 0, $ip_address, $working_message);
        $log_stmt->execute();

        $_SESSION['message'] = "No user found with that email. Please try again.";
        header("location: /PROJECT-COLAB/?login=true");
        exit;
    }
}

else if(isset($_POST["create"])){
    $title = $_POST["title"];
    $description = $_POST["description"];
    $category_id = (int) $_POST["category"];
    $user_id = (int) $_SESSION["user"]["user_id"];
    $members = $_POST["members"];

        $project = $conn->prepare("Insert into `projects`
        (`id`,`title`,`description`,`category_id`,`member_number`,`user_id`)
        values(NULL,'$title','$description','$category_id','$members','$user_id');
        ");

        $result = $project->execute();
        if($result){
            $ip_address = $_SERVER['REMOTE_ADDR'];
            if ($ip_address === '::1') {
                $ip_address = '127.0.0.1';
            }
            $log_message = "Project titled '$title' created by user $user_id from IP: $ip_address.";
            $ip_address = (string)$ip_address;
            $log_message = (string)$log_message;
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) 
                                        VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $user_id, $ip_address, $log_message);
            $log_stmt->execute();

            echo("Your Project Created and Posted");
            header("location: /PROJECT-COLAB");
        }else{
            echo("Project Not Posted");
        }

}

else if(isset($_GET["logout"])){

    $user_id = $_SESSION['user']['user_id']; 
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    
    $working_message = "User with ID: $user_id has logged out from IP: $ip_address";
    $working_message = (string)$working_message;  
    $ip_address = (string)$ip_address;

    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
    $log_stmt->execute();

    session_unset();
    session_destroy();
    header("location: /PROJECT-COLAB");
    exit;
}

else if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {

    if (isset($_SESSION['user']['user_id'])) {
        
        $file = $_FILES['file'];
        $pid = intval($_POST["project_id"]);
        $uid = intval($_SESSION['user']['user_id']);
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
                    $_SESSION['message'] = "File uploaded successfully!";

                    $ip_address = $_SERVER['REMOTE_ADDR']; 
                    if ($ip_address === '::1') {
                        $ip_address = '127.0.0.1';
                    }
                    $working_message = "File '$filename' uploaded to project ID: $pid by User ID: $uid from IP: $ip_address";
                    $working_message = (string)$working_message;
                    $ip_address = (string)$ip_address;

                    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
                    $log_stmt->bind_param("iss", $uid, $ip_address, $working_message);
                    $log_stmt->execute();

                    header("location: /PROJECT-COLAB?p-id=$pid");
                }else{
                    $_SESSION['message'] = "Error uploading file.";
                }
            } else {
                $_SESSION['message'] = "Error uploading file.";
            }
        }
    }
    else {
        $_SESSION['message'] = "please Login first to upload file";
    }
}

else if(isset($_GET["delete"])){
    echo $pid = intval($_GET["delete"]);

    $uid = $_SESSION['user']['user_id']; 
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }

    $query = $conn->prepare("delete from projects where id = $pid");
    $result = $query->execute();
    if($result){

        $working_message = "Project ID: $pid deleted by User ID: $uid from IP: $ip_address";
        $working_message = (string)$working_message;
        $ip_address = (string)$ip_address;

        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $uid, $ip_address, $working_message);
        $log_stmt->execute();

        $_SESSION['message'] = "Project Deleted";
        header("location: /PROJECT-COLAB");
    }else{
        echo("Project not Deleted error occured");
    }
}

else if(isset($_GET["deletecomment"])){
    $comment_id = intval($_GET["deletecomment"]);
    $uid = $_SESSION['user']['user_id']; 
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }

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

            $working_message = "Comment ID: $comment_id deleted from Project ID: $project_id by User ID: $uid from IP: $ip_address";
            $working_message = (string)$working_message;
            $ip_address = (string)$ip_address;

            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $uid, $ip_address, $working_message);
            $log_stmt->execute();

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
    echo $fid = intval($_GET["deletefile"]);

    $uid = $_SESSION['user']['user_id']; 
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }

    $query = $conn1->prepare("delete from documents where id = $fid");
    $result = $query->execute();
        if($result){

            $working_message = "File ID: $fid deleted from Project ID: $project_id by User ID: $uid from IP: $ip_address";
            $working_message = (string)$working_message;
            $ip_address = (string)$ip_address;

            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $uid, $ip_address, $working_message);
            $log_stmt->execute();

            echo("File Deleted");
            header("location: /PROJECT-COLAB");
        }else{
            echo("File not Deleted error occured");
        }
}

else if(isset($_POST["comment"])){
    $comment = htmlspecialchars(trim($_POST["comment"]));
    $project_id = $_POST["project_id"];
    $user_id = $_SESSION["user"]["user_id"];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }

    echo ($comment);
    echo ($project_id);
    echo ($user_id);

        $query = $conn->prepare("Insert into `comments`
        (`id`,`comment`,`project_id`,`user_id`)
        values(NULL,'$comment','$project_id','$user_id');
        ");

        $result = $query->execute();

        $working_message = "User ID: $user_id posted a comment on Project ID: $project_id. Comment: '$comment' from IP: $ip_address";
        $working_message = (string)$working_message;
        $ip_address = (string)$ip_address;

        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();

        if($result){
            echo("Your Comment Posted Thank you for your contribution..");
            header("location: /project-colab?p-id=$project_id");
        }else{
            echo("Comment not submitted Try again");
        }

}

else if(isset($_POST["update_password"])){
    $user_id = intval($_POST['user_id']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $working_message = "User ID: $user_id is attempting to update password. From IP: $ip_address";
    $working_message = (string)$working_message;
    $ip_address = (string)$ip_address;
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
    $log_stmt->execute();

    $query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $email = $row["email"];
        $hashed_password = $row['password'];

        $working_message = "User ID: $user_id found, initiating password verification.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();
    } else {
        $working_message = "User ID: $user_id not found during password update attempt.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();
        echo "user not found.";
    }

    if (password_verify($current_password, $hashed_password)) {
        $randomNumber = rand(100000, 999999);
        $_SESSION['user']['otp'] = $randomNumber;
        $_SESSION['user']['new_pass'] = $hash_password;

        $working_message = "OTP generated for User ID: $user_id for password update.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();
                   
        $message = "your OTP verification code for Project-Colab Password change is :". $randomNumber;
        $subject = "Update Password Verification";
        mailsender($email, $message, $subject);

        $working_message = "OTP sent to $email for User ID: $user_id password update.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();

        header("Location: ../client/verifypc.php");
        exit;
    } else {

        $working_message = "Failed password verification for User ID: $user_id. Incorrect current password.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();

        echo "Incorrect password. Please try again.";
    }

}

else if(isset($_POST["otp-verpc"])){

    if (!isset($_SESSION['user'])) {
        $_SESSION['message'] = "Session expired. Please try again.";
        header("Location: /PROJECT-COLAB?signup=true");
        exit;
    }

    $c_otp = trim($_POST['otp']);
    $otp = $_SESSION['user']['otp'];
    $id = $_SESSION['user']['user_id'];
    $hash_password = $_SESSION['user']['new_pass'];

    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $working_message = "User ID: $id attempted OTP verification from IP: $ip_address.";
    $working_message = (string)$working_message;
    $ip_address = (string)$ip_address;
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $id, $ip_address, $working_message);
    $log_stmt->execute();
    
    if ($c_otp == $otp) {

        $working_message = "OTP verified successfully for User ID: $id.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $id, $ip_address, $working_message);
        $log_stmt->execute();
        
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

            $working_message = "Failed to update password for User ID: $id.";
            $working_message = (string)$working_message;
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $id, $ip_address, $working_message);
            $log_stmt->execute();

            $_SESSION['message'] = "Password Not Updated TRY AGAIN";
            header("Location: /PROJECT-COLAB/index.php?change-password");
            exit;
        }
        
    }
    else {

        $working_message = "Incorrect OTP for User ID: $id.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $id, $ip_address, $working_message);
        $log_stmt->execute();

        $_SESSION['otp_attempts'] = isset($_SESSION['otp_attempts']) ? $_SESSION['otp_attempts'] + 1 : 1;

        $working_message = "User ID: $id has attempted OTP verification {$_SESSION['otp_attempts']} time(s).";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $id, $ip_address, $working_message);
        $log_stmt->execute();

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

else if(isset($_POST["update_forget_password"])){
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

    $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $working_message = "User requested password reset for Email: $email from IP: $ip_address.";
    $working_message = (string)$working_message;
    $ip_address = (string)$ip_address;
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", 0, $ip_address, $working_message);
    $log_stmt->execute();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        $randomNumber = rand(100000, 999999);
        $_SESSION['user'] = [
            'otp' => $randomNumber,
            'user_id' => $user['id'],
            'new_pass' => $hash_password,
            'email' => $email
        ];

        $working_message = "OTP generated for password reset and email sent to: $email.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", 0, $ip_address, $working_message);
        $log_stmt->execute();
                   
        $message = "your OTP verification code for Project-Colab Forgot Password is :". $randomNumber;
        $subject = "Forget Password Verification";
        mailsender($email, $message, $subject);
        header("Location: ../client/verifypc.php");
        exit;
    } else {

        $working_message = "No user found with Email: $email for password reset.";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", 0, $ip_address, $working_message);
        $log_stmt->execute();

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
        $ip_address = $_SERVER['REMOTE_ADDR'];
        if ($ip_address === '::1') {
            $ip_address = '127.0.0.1';
        }
        $ip_address = (string)$ip_address;

        function logAction($conn1, $user_id, $ip, $action) {
            $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $user_id, $ip, $action);
            $log_stmt->execute();
        }

        $allowed_extensions = ['jpg', 'jpeg'];
        $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/pjpeg'];

        $max_filesize = 5 * 1024 * 1024; // 5MB

        $upload_dir = "profile/";
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed_extensions) || !in_array($filetype, $allowed_mime_types)) {
            echo "Error: Only JPG and JPEG image files are allowed.";
            logAction($conn1, $uid, $ip_address, "Image upload failed: Invalid file type by user ID: $uid");

        } elseif ($filesize > $max_filesize) {
            echo "Error: File size exceeds the limit of 5MB.";
            logAction($conn1, $uid, $ip_address, "Image upload failed: File too large by user ID: $uid");

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
                    logAction($conn1, $uid, $ip_address, "Old profile image deleted for user ID: $uid");

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
                    logAction($conn1, $uid, $ip_address, "Profile image uploaded successfully by user ID: $uid");
                    $_SESSION['message'] = "Image Uploaded successfully!";
                    header("Location: /PROJECT-COLAB?profile=$uid");
                    exit;
                } else {
                    echo "Error: Could not save file in database.";
                    logAction($conn1, $uid, $ip_address, "Database insert failed after image upload for user ID: $uid");

                }
            } else {
                echo "Error: Failed to move uploaded file.";
                logAction($conn1, $uid, $ip_address, "Failed to move uploaded file for user ID: $uid");

            }
        }
    } else {
        echo "Please login first to upload file.";
        logAction($conn1, 0, $ip_address, "Unauthorized image upload attempt");

    }
}

else if (isset($_POST["createjob"])) {
    $title = $_POST["title"];
    $companyname = $_POST["companyname"];
    $description = $_POST["description"];
    $skills = $_POST["skills"];
    $category_id = $_POST["category"];
    $user_id = $_SESSION["user"]["user_id"];

    $upload_dir = "job-description/";
    $hasFile = isset($_FILES["job-description"]) && $_FILES["job-description"]["error"] == 0;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logJobAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    // Insert job first
    $project = $conn->prepare("INSERT INTO `jobs` (`id`, `title`, `companyname`, `description`, `skills`, `category_id`, `user_id`) VALUES (NULL, ?, ?, ?, ?, ?, ?)");
    $project->bind_param("ssssii", $title, $companyname, $description, $skills, $category_id, $user_id);
    $jobInserted = $project->execute();

    if ($jobInserted) {
        $job_id = $project->insert_id;
        logJobAction($conn1, $user_id, $ip_address, "Job created successfully. Job ID: $job_id by User ID: $user_id");

        if ($hasFile) {
            $file = $_FILES['job-description'];
            $filename = $file['name'];
            $filesize = $file['size'];
            $filetype = $file['type'];

            $allowed_extensions = ['pdf', 'jpeg', 'jpg'];
            $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/pjpeg'];

            $max_filesize = 5 * 1024 * 1024; // 5MB
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowed_extensions) || !in_array($filetype, $allowed_mime_types)) {
                echo "Error: Only PDF, JPG, and JPEG files are allowed.";
                logJobAction($conn1, $user_id, $ip_address, "Job description upload failed due to invalid file type. Job ID: $job_id");
                exit;
            }

            if ($filesize > $max_filesize) {
                echo "Error: File size exceeds the 5MB limit.";
                logJobAction($conn1, $user_id, $ip_address, "Job description upload failed due to file size exceeding limit. Job ID: $job_id");
                exit;
            }

            $new_filename = "job_" . $job_id . "." . $extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                // Save to job-description table
                $query = $conn1->prepare("INSERT INTO `job-description` (`id`, `job_id`, `user_id`, `filename`, `upload_date`) VALUES (NULL, ?, ?, ?, NOW())");
                $query->bind_param("iis", $job_id, $user_id, $new_filename);
                if ($query->execute()) {
                    logJobAction($conn1, $user_id, $ip_address, "Job description file uploaded successfully. Job ID: $job_id");
                } else {
                    logJobAction($conn1, $user_id, $ip_address, "Failed to insert job description file info into database. Job ID: $job_id");
                    echo "Error: Could not save job description file in database.";
                    exit;
                }
            } else {
                logJobAction($conn1, $user_id, $ip_address, "Failed to move uploaded job description file. Job ID: $job_id");
                echo "Error: Failed to move uploaded file.";
                exit;
            }
        }

        $_SESSION['message'] = "Job" . ($hasFile ? " and description" : "") . " uploaded successfully!";
        logJobAction($conn1, $user_id, $ip_address, "Job creation process completed successfully. Job ID: $job_id");
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit;
    } else {
        echo "Error: Job not posted.";
        logJobAction($conn1, $user_id, $ip_address, "Failed to create job by User ID: $user_id");
    }
}

else if (isset($_FILES['job_description_file']) && isset($_POST['user_id'])) {
    $job_id = intval($_POST['job_id']);
    $user_id = intval($_POST['user_id']);
    $file = $_FILES['job_description_file'];

    $filename = $file['name'];
    $filesize = $file['size'];
    $filetype = $file['type'];

    $upload_dir = "job-description/";
    $allowed_extensions = ['pdf', 'jpeg', 'jpg'];
    $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/pjpeg'];

    $max_filesize = 5 * 1024 * 1024; // 5MB
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logJDAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    if (!in_array($extension, $allowed_extensions) || !in_array($filetype, $allowed_mime_types)) {
        echo "Error: Only PDF, JPG, and JPEG files are allowed.";
        logJDAction($conn1, $user_id, $ip_address, "JD upload failed: Invalid file type for Job ID: $job_id");
        exit;
    }

    if ($filesize > $max_filesize) {
        echo "Error: File size exceeds the 5MB limit.";
        logJDAction($conn1, $user_id, $ip_address, "JD upload failed: File size exceeds limit for Job ID: $job_id");
        exit;
    }

    $new_filename = "job_" . $job_id . "." . $extension;
    $target_file = $upload_dir . $new_filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
    // Save to job-description table
    $query = $conn1->prepare("INSERT INTO `job-description` (`id`, `job_id`, `user_id`, `filename`, `upload_date`) VALUES (NULL, ?, ?, ?, NOW())");
    $query->bind_param("iis", $job_id, $user_id, $new_filename);
    if ($query->execute()) {
        logJDAction($conn1, $user_id, $ip_address, "JD file uploaded and database updated successfully for Job ID: $job_id");
    } else {
        logJDAction($conn1, $user_id, $ip_address, "JD file uploaded but failed to insert into database for Job ID: $job_id");
        echo "Error: Could not save JD file info in database.";
        exit;
    }
    } else {
        echo "Error: Failed to move uploaded file.";
        logJDAction($conn1, $user_id, $ip_address, "Failed to move uploaded JD file for Job ID: $job_id");
        exit;
    }
    $_SESSION['message'] = "JD file uploaded successfully!";
    logJDAction($conn1, $user_id, $ip_address, "JD file upload process completed successfully for Job ID: $job_id");
    header("Location: /PROJECT-COLAB/?alljob=true");
    exit();
}

else if(isset($_GET["apply"])){
    echo $job_id = intval($_GET["apply"]);
    $user_id = $_SESSION['user']['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logApplicationAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    $check = $conn->prepare("SELECT * FROM `apply-status` WHERE job_id = ? AND user_id = ?");
    $check->bind_param("ii", $job_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['message'] = "You’ve already applied to this job.";
        logApplicationAction($conn1, $user_id, $ip_address, "Attempted to re-apply for Job ID: $job_id");
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit();
    }

    $sender_email = htmlspecialchars($_SESSION['user']['email']); // Sender email
    $sender_name = htmlspecialchars($_SESSION['user']['username']); //user name

    $query = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
    $query->bind_param("i", $job_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $job_name = htmlspecialchars($row["title"]);  //job name
        $job_user_id = $row["user_id"];

        $query1 = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $query1->bind_param("i", $job_user_id);
        $query1->execute();
        $result1 = $query1->get_result();

        if ($result1->num_rows === 1) {
            $row1 = $result1->fetch_assoc();
            $reciever_email = htmlspecialchars($row1["email"]); //reciever mail
        }
        else {
            $_SESSION['message'] = "Recruiter not found.";
            logApplicationAction($conn1, $user_id, $ip_address, "Recruiter not found while applying to Job ID: $job_id");
            header("Location: /PROJECT-COLAB/?alljob=true");
            exit();
        }
    } else {
        $_SESSION['message'] = "Job not available now!";
        logApplicationAction($conn1, $user_id, $ip_address, "Job not available during apply attempt for Job ID: $job_id");
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit();
    }

    $query3 = $conn->prepare("SELECT phone FROM users WHERE id = ?");
    $query3->bind_param("i", $user_id);
    $query3->execute();
    $result3 = $query3->get_result();
    if ($result3->num_rows === 1) {
        $row3 = $result3->fetch_assoc();
        $sender_phone = htmlspecialchars($row3["phone"]); //sender phone
    }

    $query2 = $conn->prepare("SELECT title, description FROM projects WHERE user_id = ?");
    $query2->bind_param("i", $user_id);
    $query2->execute();
    $result2 = $query2->get_result();
    
    $projects = []; // To store all projects
    
    if ($result2->num_rows > 0) {
        while ($row2 = $result2->fetch_assoc()) {
            $projects[] = [
                'title' => htmlspecialchars($row2['title']),
                'description' => nl2br(htmlspecialchars($row2['description']))
            ];
        }
    }    

    $message = "
    <h2>Job Application for: <strong>$job_name</strong></h2>

    <p>Dear Recruiter,</p>

    <p>My name is <strong>$sender_name</strong>, and I'm very interested in your job posting. 
    I believe my skills and experience make me a great fit for this role. Please find below a list of my recent projects that showcase my capabilities and enthusiasm.</p>

    <p>You can reach me at: <strong>$sender_email</strong> or <strong>$sender_phone</strong></p>

    <hr>

    <h3>Applicant's Projects:</h3>
    ";

    if (!empty($projects)) {
        $message .= "<ul>";
        foreach ($projects as $proj) {
            $projectName = htmlspecialchars($proj['title']);
            $projectDesc = nl2br(htmlspecialchars($proj['description']));
            $message .= "<li><strong>$projectName</strong><br><small>$projectDesc</small></li><br>";
        }
        $message .= "</ul>";
    } else {
        $message .= "<p><em>No projects submitted yet.</em></p>";
    }

    $message .= "
        <hr>
        <p>Thank you for your time and consideration. I look forward to the possibility of working together.</p>
        <p>Best regards,<br>$sender_name</p>

        <hr>
        <small>This message was automatically generated by <strong>Project Colab</strong>.</small>
    ";
    $subject = "New Application for $job_name from $sender_name";
    mailsender($reciever_email, $message, $subject); 
    logApplicationAction($conn1, $user_id, $ip_address, "Application email sent for Job ID: $job_id");

    $status = 1;
    $applystatus = $conn->prepare("INSERT INTO `apply-status` (`id`, `job_id`, `status`, `user_id`) VALUES (NULL, ?, ?, ?)");
    $applystatus->bind_param("iii", $job_id, $status, $user_id);
    $statusupdate = $applystatus->execute();

    if ($statusupdate) {
        
        $_SESSION['message'] = "You have applied successfully, Wait for response on your registered email!";
        logApplicationAction($conn1, $user_id, $ip_address, "Applied successfully for Job ID: $job_id");
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit();
    }
    else {
    $_SESSION['message'] = "Failed to apply for the job.";
    logApplicationAction($conn1, $user_id, $ip_address, "Failed to apply for Job ID: $job_id");
    header("Location: /PROJECT-COLAB/?alljob=true");
    exit();
    }
}

else if(isset($_GET["offer"])){
    echo $reciever_user_id = $_GET["offer"]; // reciever user id
    $user_id = $_SESSION['user']['user_id']; // sender user id
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logOfferAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    $check = $conn->prepare("SELECT * FROM `offer-status` WHERE user_id = ? AND recruiter_id = ?");
    $check->bind_param("ii", $reciever_user_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['message'] = "You’ve already offer this user.";
        logOfferAction($conn1, $user_id, $ip_address, "Attempted to re-offer User ID: $reciever_user_id");
        header("Location: /PROJECT-COLAB/?user=true");
        exit();
    }

    $sender_email = $_SESSION['user']['email']; //sender mail
    $sender_name = $_SESSION['user']['username']; //user name

    $query1 = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $query1->bind_param("i", $reciever_user_id);
    $query1->execute();
    $result1 = $query1->get_result();

    if ($result1->num_rows === 1) {
        $row1 = $result1->fetch_assoc();
        $reciever_email = $row1["email"]; //reciever mail
    } else {
        $_SESSION['message'] = "user not available now!";
        logOfferAction($conn1, $user_id, $ip_address, "Receiver user not available for offer. User ID: $reciever_user_id");
        header("Location: /PROJECT-COLAB/?user=true");
        exit();
    }

    $query3 = $conn->prepare("SELECT phone FROM users WHERE id = ?");
    $query3->bind_param("i", $user_id);
    $query3->execute();
    $result3 = $query3->get_result();
    if ($result3->num_rows === 1) {
        $row3 = $result3->fetch_assoc();
        $sender_phone = $row3["phone"]; //sender phone
    } 

    $message = "
    <h2>You've Received a Job Offer on <strong>Project Colab</strong></h2>

    <p>Dear Candidate,</p>

    <p>My name is <strong>$sender_name</strong>, and I came across your profile on Project Colab. I'm currently working on a project and would like to offer you an opportunity to collaborate.</p>

    <p>If you're interested in working together, please feel free to reach out to me at <strong>$sender_email</strong> or <strong>$sender_phone</strong>. I'd be happy to discuss more details about the project and your potential role.</p>

    <hr>

    <p>Looking forward to hearing from you!</p>

    <p>Best regards,<br>$sender_name</p>

    <hr>
    <small>This message was automatically sent via <strong>Project Colab</strong>'s job offer system.</small>
    ";

    $subject = "Job Offer from $sender_name via Project Colab";

    mailsender($reciever_email, $message, $subject); 

    logOfferAction($conn1, $user_id, $ip_address, "Offer email sent to User ID: $reciever_user_id");

    $status = 1;
    $offerstatus = $conn->prepare("INSERT INTO `offer-status` (`id`, `user_id`, `recruiter_id`, `status`) VALUES (NULL, ?, ?, ?)");
    $offerstatus->bind_param("iii", $reciever_user_id, $user_id, $status);
    $statusupdate = $offerstatus->execute();

    if ($statusupdate) {
        
        $_SESSION['message'] = "Project Offer sent successfully, Wait for response on your registered email!";
        logOfferAction($conn1, $user_id, $ip_address, "Offer sent successfully to User ID: $reciever_user_id");
        header("Location: /PROJECT-COLAB/?user=true");
        exit();
    }else {
        $_SESSION['message'] = "Failed to send offer.";
        logOfferAction($conn1, $user_id, $ip_address, "Offer failed to send to User ID: $reciever_user_id");
        header("Location: /PROJECT-COLAB/?user=true");
        exit();
    }
}

else if (isset($_POST['add_skill'])) {
    $user_id = $_POST['user_id'];
    $new_skill = trim($_POST['new_skill']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logSkillAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    if (!empty($new_skill)) {
        $checkQuery = $conn->prepare("SELECT skill FROM skills WHERE user_id = ?");
        $checkQuery->bind_param("i", $user_id);
        $checkQuery->execute();
        $result = $checkQuery->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $existing_skills = $row['skill'];
            $updated_skills = $existing_skills . ', ' . $new_skill;

            $update = $conn->prepare("UPDATE skills SET skill = ? WHERE user_id = ?");
            $update->bind_param("si", $updated_skills, $user_id);
            $update_success = $update->execute();

            if ($update_success) {
                logSkillAction($conn1, $user_id, $ip_address, "Updated skills: added '$new_skill'");
            } else {
                logSkillAction($conn1, $user_id, $ip_address, "Failed to update skills with '$new_skill'");
            }
        } else {
            $insert = $conn->prepare("INSERT INTO skills (user_id, skill) VALUES (?, ?)");
            $insert->bind_param("is", $user_id, $new_skill);
            $insert->execute();

            if ($insert_success) {
                logSkillAction($conn1, $user_id, $ip_address, "Inserted new skill: '$new_skill'");
            } else {
                logSkillAction($conn1, $user_id, $ip_address, "Failed to insert new skill '$new_skill'");
            }
        }
    }
     else {
        logSkillAction($conn1, $user_id, $ip_address, "Attempted to add empty skill");
    }

    $_SESSION['message'] = "Skill added successfully!";
    header("Location: /PROJECT-COLAB/?profile=true");
    exit();
}

else if (isset($_POST['upload_resume'])) {
    $user_id = (int) $_POST['user_id'];
    $upload_dir = 'Resume/';
    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg'];
    $allowed_mime_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg'
    ];
    $max_filesize = 5 * 1024 * 1024; // 5MB
    $ip_address = $_SERVER['REMOTE_ADDR']; 
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logResumeAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === 0) {
        $file = $_FILES['resume'];
        $filename = $file['name'];
        $filesize = $file['size'];
        $filetype = mime_content_type($file['tmp_name']);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        logResumeAction($conn1, $user_id, $ip_address, "Received file: $filename, Size: $filesize bytes, Type: $filetype");

        if (!in_array($extension, $allowed_extensions) || !in_array($filetype, $allowed_mime_types)) {
            $_SESSION['message'] = "Only PDF, DOC, DOCX, JPG, and JPEG files are allowed.";
            logResumeAction($conn1, $user_id, $ip_address, "Invalid file type or extension for file: $filename");
        } elseif ($filesize > $max_filesize) {
            $_SESSION['message'] = "File size exceeds the 5MB limit.";
            logResumeAction($conn1, $user_id, $ip_address, "File size exceeds 5MB for file: $filename");
        } else {
            $new_filename = "user_" . $user_id . "." . $extension;
            $target_file = $upload_dir . $new_filename;

            // Check for existing resume
            $sql = $conn1->prepare("SELECT filename FROM resumes WHERE user_id = ?");
            $sql->bind_param("i", $user_id);
            $sql->execute();
            $result = $sql->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $oldfilepath = $upload_dir . $row['filename'];
                if (file_exists($oldfilepath)) {
                    unlink($oldfilepath);
                    logResumeAction($conn1, $user_id, $ip_address, "Deleted old resume: $oldfilepath");
                }
                $delete = $conn1->prepare("DELETE FROM resumes WHERE user_id = ?");
                $delete->bind_param("i", $user_id);
                $delete->execute();
            }

            // Save new file
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $insert = $conn1->prepare("INSERT INTO resumes (user_id, filename, upload_date) VALUES (?, ?, NOW())");
                $insert->bind_param("is", $user_id, $new_filename);
                if ($insert->execute()) {
                    $_SESSION['message'] = "Resume uploaded successfully!";
                    logResumeAction($conn1, $user_id, $ip_address, "Successfully uploaded new resume: $new_filename");
                } else {
                    $_SESSION['message'] = "Database error while saving resume.";
                    logResumeAction($conn1, $user_id, $ip_address, "Failed to insert resume record into database.");
                }
            } else {
                $_SESSION['message'] = "Failed to upload resume file.";
                logResumeAction($conn1, $user_id, $ip_address, "Failed to move uploaded resume file: $filename");
            }
        }
    } else {
        $_SESSION['message'] = "No file uploaded or file error.";
        logResumeAction($conn1, $user_id, $ip_address, "No file uploaded or file error occurred.");
    }

    logResumeAction($conn1, $user_id, $ip_address, "Redirecting to profile page.");

    header("Location: /PROJECT-COLAB?profile=$user_id");
    exit();
}

else if(isset($_GET["join"])){
    echo $pid = intval($_GET["join"]);
    $user_id = intval($_SESSION['user']['user_id']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logJoinAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }

    $query = $conn->prepare("select * from `projects` where id = ?");
    $query->bind_param("i", $pid);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['member_number'] === 0){
            $_SESSION['message'] = "members full try another projects";
            logJoinAction($conn1, $user_id, $ip_address, "Attempted to join project $pid, but members were full.");
            header("Location: /PROJECT-COLAB/");
            exit();
        }
        else{

            $query = $conn->prepare("SELECT member_ids FROM `projects` WHERE id = ?");
            $query->bind_param("i", $pid);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
    
                $member_ids_string = $row['member_ids'];

                // Convert member_ids to array
                $member_ids = array_filter(explode(',', $member_ids_string));

                if (in_array($user_id, $member_ids)) {
                    $_SESSION['message'] = "You already joined this project.";
                    logJoinAction($conn1, $user_id, $ip_address, "Attempted to join project $pid, but user is already a member.");
                    header("Location: /PROJECT-COLAB/");
                    exit();
                }

                // Add user to group
                $member_ids[] = $user_id; // add this user ID
                $new_member_ids_string = implode(',', $member_ids);

                $update = $conn->prepare("UPDATE `projects` SET member_number = member_number - 1, member_ids = ? WHERE id = ?");
                $update->bind_param("si", $new_member_ids_string, $pid);
                $update->execute();

                $_SESSION['message'] = "Successfully joined the project!";
                logJoinAction($conn1, $user_id, $ip_address, "User successfully joined project $pid.");
                header("Location: /PROJECT-COLAB/");
                exit();

            } else {
                $_SESSION['message'] = "Group not found.";
                logJoinAction($conn1, $user_id, $ip_address, "Attempted to join project $pid, but project not found.");
                header("Location: /PROJECT-COLAB/");
                exit();
            }
        }
    }else {
        $_SESSION['message'] = "Project not found.";
        logJoinAction($conn1, $user_id, $ip_address, "Attempted to join non-existing project $pid.");
        header("Location: /PROJECT-COLAB/");
        exit();
    }
}

else if (isset($_POST['send_chat'])){
    $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $user_id = $_SESSION['user']['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    $ip_address = (string)$ip_address;

    function logChatAction($conn1, $user_id, $ip, $action) {
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip, $action);
        $log_stmt->execute();
    }


    if ($project_id > 0 && !empty($message)) {

        $stmt = $conn->prepare("INSERT INTO chat (project_id, user_id, message, chat_time) VALUES (?, ?, ?, NOW())");

        if ($stmt) {
            $stmt->bind_param('iis', $project_id, $user_id, $message);
            
            if ($stmt->execute()) {
                logChatAction($conn1, $user_id, $ip_address, "Successfully sent message to project $project_id.");
                header("Location: /PROJECT-COLAB/?project-id=$project_id");
                exit();
            } else {
                $_SESSION['message'] = "Error: Could not send message.";
                logChatAction($conn1, $user_id, $ip_address, "Failed to send message to project $project_id.");
                header("Location: /PROJECT-COLAB/?project-id=$project_id");
                exit();
            }
            
            $stmt->close();
        } else {
            $_SESSION['message'] = "Error in preparing statement.";
            logChatAction($conn1, $user_id, $ip_address, "Error in preparing SQL statement for project $project_id.");
            header("Location: /PROJECT-COLAB/?project-id=$project_id");
            exit();
        }

    } else {
        $_SESSION['message'] = "Project ID or message is invalid.";
        logChatAction($conn1, $user_id, $ip_address, "Attempted to send invalid message to project $project_id.");
        header("Location: /PROJECT-COLAB/?project-id=$project_id");
        exit();
    }
}

else if(isset($_POST["sent"])){
    $keyword = $_POST["keyword"];
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    
    $user_id = 0;
    $working_message = "Admin Login attempt initiated from IP: $ip_address";
    $working_message = (string)$working_message;
    $ip_address = (string)$ip_address;
    $temp_user_id = 0;
    $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $temp_user_id, $ip_address, $working_message);
    $log_stmt->execute();
    
    if($keyword === "SatyamPrasadCuraj@123"){
        $email = "satyamprasad710@gmail.com";

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $Adminusername = '';
        $length = 8;
        for ($i = 0; $i < $length; $i++) {
            $Adminusername .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $Adminpassword = '';
        $password_length = 12; // Length of the password
        for ($i = 0; $i < $password_length; $i++) {
            $Adminpassword .= $characters[rand(0, strlen($characters) - 1)];
        }

        $_SESSION['Admin_credential'] = [
            'username' => $Adminusername,
            'password' => $Adminpassword,
        ];

        $message = "Your User Credential for Project-Colab Admin-Login is: Username: " . $Adminusername . " Password: " . $Adminpassword;
        $subject = "Project Colab Admin Login Credential";
        mailsender($email, $message, $subject);
        header("Location: ../client/admin-verify-credential.php");
        exit;
    }
    else{
        $_SESSION['message'] = "Keyword is Incorrect Try Again";
        header("Location: /PROJECT-COLAB/index.php?admin-login");
        exit;
    }
}

else if (isset($_POST["cred-ver"])) {

    if (!isset($_SESSION['Admin_credential'])) {
        $_SESSION['message'] = "Session expired. Please try again.";
        header("Location: /PROJECT-COLAB/index.php?admin-login");
        exit;
    }

    $username = $_POST['adminusername'];
    $password = $_POST['adminpassword'];
    $stored = $_SESSION['Admin_credential'];

    if ($username == $stored['username'] and $password == $stored['password']) {

        $_SESSION['user'] = [
            'username' => "Admin",
            'email' => "satyamprasad710@gmail.com",
            'user_id' => 55555,
            'user_type' => "Admin"
        ];
        $_SESSION['user_status']['status'] = '1';
        $_SESSION['message'] = "Login successful.";

        $user_id = 55555;
        $ip_address = $_SERVER['REMOTE_ADDR'];
        if ($ip_address === '::1') {
            $ip_address = '127.0.0.1';
        }
        $ip_address = (string)$ip_address;
        $working_message = "credential verified successfully and admin login with IP: $ip_address";
        $working_message = (string)$working_message;
        $log_stmt = $conn1->prepare("INSERT INTO `logging` (`user_id`, `ip_address`, `working`, `time`) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $user_id, $ip_address, $working_message);
        $log_stmt->execute();

        unset($_SESSION['Admin_credential']); // Clear session

        header("Location: /PROJECT-COLAB");
        exit;
    } else {
        $_SESSION['message'] = "Error in Login. Try again.";
        header("Location: /PROJECT-COLAB/index.php?admin-login");
        exit;
    }  
}

else {
    //
}

?>