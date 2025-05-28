<?php
session_start();
require 'connexion.php';

if (isset($_POST['register'])) {
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    $cin = $conn->real_escape_string($_POST['cin']);
    $adresse = $conn->real_escape_string($_POST['adresse']);

    // fi 7alat la kan deja email
    $check_query = $conn->query("SELECT * FROM utilisateurs WHERE email='$email'");
    if ($check_query->num_rows > 0) {
        echo "<script>alert('Email already exists!');</script>";
    } else {
        // generation dyal nombre random
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Insertion avec otp
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, cin, adresse_livraison, verification_code, code_expiry) 
                VALUES ('$nom', '$prenom', '$email', '$password', '$telephone', '$cin', '$adresse', '$otp', '$otp_expiry')";

        if ($conn->query($sql)) {
            // enovoyer otp
            require 'Mail/phpmailer/PHPMailerAutoload.php';
            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            
            
            $mail->Username = 'nonoilyass2021@gmail.com';
            $mail->Password = 'plwc lhcu pyrp shei';

            $mail->setFrom('nonoilyass2021@gmail.com', 'Admin');
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body = "Dear $prenom,<br><br>Your verification code is: <b>$otp</b><br><br>This code will expire in 15 minutes.";

            if ($mail->send()) {
                $_SESSION['verify_email'] = $email;
                header("Location: verification.php");
                exit();
            } else {
                echo "<script>alert('Error sending OTP!');</script>";
            }
        } else {
            echo "<script>alert('Registration failed!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <center><title>Inscription v</title></center>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                       <center> <h3>Inscription</h3></center>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="nom" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="prenom" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CIN</label>
                                <input type="text" name="cin" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" name="telephone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <textarea name="adresse" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary">S'inscrire</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>