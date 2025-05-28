<?php
session_start();
require 'connexion.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $result = $conn->query("SELECT * FROM utilisateurs WHERE reset_token='$token' AND code_expiry > NOW()");
    
    if ($result->num_rows == 0) {
        echo "<script>alert('Invalid or expired token!'); window.location.href='login.php';</script>";
        exit();
    }
}

if (isset($_POST['reset'])) {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $conn->query("UPDATE utilisateurs SET mot_de_passe='$password', reset_token=NULL, code_expiry=NULL WHERE reset_token='$token'");
    
    echo "<script>alert('Password reset successfully!'); window.location.href='login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Reset Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="token" value="<?php echo $_GET['token'] ?? ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="reset" class="btn btn-primary">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>