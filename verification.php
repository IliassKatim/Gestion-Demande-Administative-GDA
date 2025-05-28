<?php
session_start();
require 'connexion.php';

if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

if (isset($_POST['verify'])) {
    $otp_code = $_POST['otp_code'];
    $email = $_SESSION['verify_email'];

    $result = $conn->query("SELECT * FROM utilisateurs WHERE email='$email' AND verification_code='$otp_code' AND code_expiry > NOW()");
    
    if ($result->num_rows > 0) {
        $conn->query("UPDATE utilisateurs SET status=1, verification_code=NULL, code_expiry=NULL WHERE email='$email'");
        unset($_SESSION['verify_email']);
        echo "<script>alert('Account verified successfully! You can now login.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Invalid OTP or OTP expired!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Verification OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Verification OTP</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Enter OTP Code</label>
                                <input type="text" name="otp_code" class="form-control" required>
                            </div>
                            <button type="submit" name="verify" class="btn btn-primary">Verify</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>