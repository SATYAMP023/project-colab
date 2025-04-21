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
    $subject = "Registration otp verification";
    mailsender($email, $message, $subject); 

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
            $_SESSION['message'] = "Incorrect password. Please try again.";
            header("location: /PROJECT-COLAB/?login=true");
            exit;
        }
    } else {
        $_SESSION['message'] = "No user found with that email. Please try again.";
        header("location: /PROJECT-COLAB/?login=true");
        exit;
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
        $subject = "Update Password Verification";
        mailsender($email, $message, $subject);
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
        $subject = "Forget Password Verification";
        mailsender($email, $message, $subject);
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

else if (isset($_POST["createjob"])) {
    $title = $_POST["title"];
    $companyname = $_POST["companyname"];
    $description = $_POST["description"];
    $skills = $_POST["skills"];
    $category_id = $_POST["category"];
    $user_id = $_SESSION["user"]["user_id"];

    $upload_dir = "job-description/";
    $hasFile = isset($_FILES["job-description"]) && $_FILES["job-description"]["error"] == 0;

    // Insert job first
    $project = $conn->prepare("INSERT INTO `jobs` (`id`, `title`, `companyname`, `description`, `skills`, `category_id`, `user_id`) VALUES (NULL, ?, ?, ?, ?, ?, ?)");
    $project->bind_param("ssssii", $title, $companyname, $description, $skills, $category_id, $user_id);
    $jobInserted = $project->execute();

    if ($jobInserted) {
        $job_id = $project->insert_id;

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
                exit;
            }

            if ($filesize > $max_filesize) {
                echo "Error: File size exceeds the 5MB limit.";
                exit;
            }

            $new_filename = "job_" . $job_id . "." . $extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                // Save to job-description table
                $query = $conn1->prepare("INSERT INTO `job-description` (`id`, `job_id`, `user_id`, `filename`, `upload_date`) VALUES (NULL, ?, ?, ?, NOW())");
                $query->bind_param("iis", $job_id, $user_id, $new_filename);
                $query->execute();
            } else {
                echo "Error: Failed to move uploaded file.";
                exit;
            }
        }

        $_SESSION['message'] = "Job" . ($hasFile ? " and description" : "") . " uploaded successfully!";
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit;
    } else {
        echo "Error: Job not posted.";
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

    if (!in_array($extension, $allowed_extensions) || !in_array($filetype, $allowed_mime_types)) {
        echo "Error: Only PDF, JPG, and JPEG files are allowed.";
        exit;
    }

    if ($filesize > $max_filesize) {
        echo "Error: File size exceeds the 5MB limit.";
        exit;
    }

    $new_filename = "job_" . $job_id . "." . $extension;
    $target_file = $upload_dir . $new_filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
    // Save to job-description table
    $query = $conn1->prepare("INSERT INTO `job-description` (`id`, `job_id`, `user_id`, `filename`, `upload_date`) VALUES (NULL, ?, ?, ?, NOW())");
    $query->bind_param("iis", $job_id, $user_id, $new_filename);
    $query->execute();
    } else {
        echo "Error: Failed to move uploaded file.";
        exit;
    }
    $_SESSION['message'] = "JD file uploaded successfully!";
    header("Location: /PROJECT-COLAB/?alljob=true");
    exit();
}

else if(isset($_GET["apply"])){
    echo $job_id = $_GET["apply"];
    $user_id = $_SESSION['user']['user_id'];

    $check = $conn->prepare("SELECT * FROM `apply-status` WHERE job_id = ? AND user_id = ?");
    $check->bind_param("ii", $job_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['message'] = "You’ve already applied to this job.";
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit();
    }

    $sender_email = $_SESSION['user']['email']; //sender mail
    $sender_name = $_SESSION['user']['username']; //user name

    $query = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
    $query->bind_param("i", $job_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $job_name = $row["title"];  //job name
        $job_user_id = $row["user_id"];

        $query1 = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $query1->bind_param("i", $job_user_id);
        $query1->execute();
        $result1 = $query1->get_result();

        if ($result1->num_rows === 1) {
            $row1 = $result1->fetch_assoc();
            $reciever_email = $row1["email"]; //reciever mail
        }
    } else {
        $_SESSION['message'] = "Job not available now!";
        header("Location: /PROJECT-COLAB/?alljob=true");
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

    $query2 = $conn->prepare("SELECT title, description FROM projects WHERE user_id = ?");
    $query2->bind_param("i", $user_id);
    $query2->execute();
    $result2 = $query2->get_result();
    
    $projects = []; // To store all projects
    
    if ($result2->num_rows > 0) {
        while ($row2 = $result2->fetch_assoc()) {
            $projects[] = [
                'title' => $row2['title'],
                'description' => $row2['description']
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

    $status = 1;
    $applystatus = $conn->prepare("INSERT INTO `apply-status` (`id`, `job_id`, `status`, `user_id`) VALUES (NULL, ?, ?, ?)");
    $applystatus->bind_param("iii", $job_id, $status, $user_id);
    $statusupdate = $applystatus->execute();

    if ($statusupdate) {
        
        $_SESSION['message'] = "You have applied successfully, Wait for response on your registered email!";
        header("Location: /PROJECT-COLAB/?alljob=true");
        exit();
    }
}

else if(isset($_GET["offer"])){
    echo $reciever_user_id = $_GET["offer"]; // reciever user id
    $user_id = $_SESSION['user']['user_id']; // sender user id

    $check = $conn->prepare("SELECT * FROM `offer-status` WHERE user_id = ? AND recruiter_id = ?");
    $check->bind_param("ii", $reciever_user_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['message'] = "You’ve already offer this user.";
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

    $status = 1;
    $offerstatus = $conn->prepare("INSERT INTO `offer-status` (`id`, `user_id`, `recruiter_id`, `status`) VALUES (NULL, ?, ?, ?)");
    $offerstatus->bind_param("iii", $reciever_user_id, $user_id, $status);
    $statusupdate = $offerstatus->execute();

    if ($statusupdate) {
        
        $_SESSION['message'] = "Project Offer sent successfully, Wait for response on your registered email!";
        header("Location: /PROJECT-COLAB/?user=true");
        exit();
    }
}

else if (isset($_POST['add_skill'])) {
    $user_id = $_POST['user_id'];
    $new_skill = trim($_POST['new_skill']);

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
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO skills (user_id, skill) VALUES (?, ?)");
            $insert->bind_param("is", $user_id, $new_skill);
            $insert->execute();
        }
    }

    $_SESSION['message'] = "Skill added successfully!";
    header("Location: /PROJECT-COLAB/?profile=true");
    exit();
}


else {
    //
}

?>