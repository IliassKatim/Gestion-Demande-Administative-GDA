<?php
session_start();
require 'connexion.php';

if (isset($_POST['recover'])) {
    $email = $_POST['email'];
    
    $result = $conn->query("SELECT * FROM utilisateurs WHERE email='$email'");
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] == 0) {
            echo "<script>alert('Please verify your email first!'); window.location.href='login.php';</script>";
            exit();
        }
        
        // Generate token
        $token = bin2hex(random_bytes(50));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $conn->query("UPDATE utilisateurs SET reset_token='$token', code_expiry='$token_expiry' WHERE email='$email'");
        
        // Send reset email using existing PHPMailer
        require 'Mail/phpmailer/PHPMailerAutoload.php';
        $mail = new PHPMailer;
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        
        // Credentials from otp_code.txt
        $mail->Username = 'nonoilyass2021@gmail.com';
        $mail->Password = 'plwc lhcu pyrp shei';
        
        $mail->setFrom('nonoilyass2021@gmail.com', 'Admin');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $reset_link = "http://".$_SERVER['HTTP_HOST']."/HTML/STAGE/reset_psw.php?token=$token";
        $mail->Body = "Click the link to reset your password: <a href='$reset_link'>Reset Password</a>";
        
        if ($mail->send()) {
            echo "<script>alert('Password reset link sent to your email!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error sending email!');</script>";
        }
    } else {
        echo "<script>alert('Email not found!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Password Recovery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Password Recovery</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Enter Your Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <button type="submit" name="recover" class="btn btn-primary">Recover Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>