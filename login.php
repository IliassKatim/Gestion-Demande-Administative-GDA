<?php
session_start();
require 'connexion.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM utilisateurs WHERE email='$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] == 0) {
            echo "<script>alert('Please verify your email first!');</script>";
        } elseif (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            header("Location: index.html");
            exit();
        } else {
            echo "<script>alert('Invalid email or password!');</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Plateforme Administrative</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #9E6D4E;
            --secondary: #D4A373;
            --accent: #05570f;
            --dark: #3A2D1F;
            --light: #F8F1E5;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('uploads/unnamed_upscayl_2x_realesrgan-x4plus.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background-color: rgba(249, 245, 245, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .login-container {
            background-color: rgba(248, 241, 229, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(58, 45, 31, 0.3);
            padding: 2.5rem;
            max-width: 500px;
            margin: auto;
            border: 1px solid var(--secondary);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header img {
            height: 80px;
            margin-bottom: 1rem;
        }
        
        .login-header h2 {
            color: var(--accent);
            font-weight: 600;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid var(--secondary);
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(5, 87, 15, 0.25);
        }
        
        .btn-login {
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-login:hover {
            background-color: #03420c;
            transform: translateY(-2px);
        }
        
        .btn-outline-accent {
            color: var(--accent);
            border-color: var(--accent);
        }
        
        .btn-outline-accent:hover {
            background-color: var(--accent);
            color: white;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--secondary);
        }
        
        .divider-text {
            padding: 0 1rem;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--dark);
        }
        
        footer {
            background-color: rgba(2, 74, 2, 0.9);
            color: white;
            margin-top: auto;
            padding: 1rem 0;
        }
        
        .form-floating label {
            color: var(--dark);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="uploads/Coat_of_arms_of_Morocco.png" alt="Logo Ministère" height="50">
            </a>
            <div class="d-flex">
                <a href="register.php" class="btn btn-outline-accent me-2">S'inscrire</a>
            </div>
        </div>
    </nav>

    <div class="container my-auto py-5">
        <div class="login-container">
            <div class="login-header">
                <img src="uploads/Coat_of_arms_of_Morocco.png" alt="Logo" width="80">
                <h2>Connexion à votre compte</h2>
                <p class="text-muted">Accédez à vos services administratifs</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-floating mb-3 position-relative">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Adresse Email</label>
                    <i class="fas fa-envelope text-muted position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%);"></i>
                </div>
                
                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Mot de passe</label>
                    <i class="fas fa-eye-slash password-toggle" id="togglePassword"></i>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="recover_psw.php" class="text-accent">Mot de passe oublié?</a>
                </div>
                
                <button type="submit" name="login" class="btn btn-login mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
                
                <div class="divider">
                    <span class="divider-text">OU</span>
                </div>
                
                <div class="text-center">
                    <p class="mb-3">Vous n'avez pas de compte?</p>
                    <a href="register.php" class="btn btn-outline-accent">
                        <i class="fas fa-user-plus me-2"></i>Créer un compte
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="mb-0">© 2025 Plateforme de Demandes Administratives. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });
    </script>
</body>
</html>