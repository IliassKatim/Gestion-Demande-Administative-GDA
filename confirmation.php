<?php
session_start();
require 'connexion.php';

if (!isset($_SESSION['form_data'])) {
    header("Location: demande_naissance.php");
    exit();
}

$form_data = $_SESSION['form_data'];
$file_info = $_SESSION['file_info'] ?? null;

// Get names of administrative divisions
$region_name = $prefecture_name = $commune_name = $office_name = '';

if (isset($form_data['region_id'])) {
    $res = $conn->query("SELECT nom FROM regions WHERE id = ".intval($form_data['region_id']));
    $region_name = $res->fetch_assoc()['nom'] ?? '';
}

if (isset($form_data['prefecture_province_id'])) {
    $res = $conn->query("SELECT nom FROM prefectures_provinces WHERE id = ".intval($form_data['prefecture_province_id']));
    $prefecture_name = $res->fetch_assoc()['nom'] ?? '';
}

if (isset($form_data['commune_id'])) {
    $res = $conn->query("SELECT nom FROM communes WHERE id = ".intval($form_data['commune_id']));
    $commune_name = $res->fetch_assoc()['nom'] ?? '';
}

if (isset($form_data['civil_status_office_id'])) {
    $res = $conn->query("SELECT nom FROM civil_status_offices WHERE id = ".intval($form_data['civil_status_office_id']));
    $office_name = $res->fetch_assoc()['nom'] ?? '';
}

// Handle confirmation
if (isset($_POST['confirm'])) {
    // Insert into database
    $sql = "INSERT INTO demandes_naissance (
        utilisateur_id, type_demande, langue_document,
        nom_arabe, prenom_arabe, nom_latin, prenom_latin,
        nom_mere_arabe, prenom_mere_arabe, nom_pere_arabe, prenom_pere_arabe,
        date_naissance_complete, ignore_jour_mois, sexe, 
        numero_acte, annee_enregistrement, doc_cin,
        region_id, prefecture_province_id, commune_id, civil_status_office_id
    ) VALUES (
        '{$_SESSION['user_id']}', '{$form_data['type_demande']}', '{$form_data['langue_document']}',
        '{$form_data['nom_arabe']}', '{$form_data['prenom_arabe']}', '{$form_data['nom_latin']}', '{$form_data['prenom_latin']}',
        '{$form_data['nom_mere_arabe']}', '{$form_data['prenom_mere_arabe']}', '{$form_data['nom_pere_arabe']}', '{$form_data['prenom_pere_arabe']}',
        '{$form_data['date_naissance']}', '".(isset($form_data['ignore_jour_mois']) ? 1 : 0)."', '{$form_data['sexe']}',
        '{$form_data['numero_acte']}', '{$form_data['annee_enregistrement']}', '{$file_info['path']}',
        '{$form_data['region_id']}', '{$form_data['prefecture_province_id']}', '{$form_data['commune_id']}', '{$form_data['civil_status_office_id']}'
    )";
    
    if ($conn->query($sql)) {
        unset($_SESSION['form_data']);
        unset($_SESSION['file_info']);
        header("Location: success.php");
        exit();
    } else {
        die("Database error: " . $conn->error);
    }
}

if (isset($_POST['cancel'])) {
    // Delete uploaded file if exists
    if ($file_info && file_exists($file_info['path'])) {
        unlink($file_info['path']);
    }
    unset($_SESSION['form_data']);
    unset($_SESSION['file_info']);
    header("Location: demande_naissance.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation | Plateforme Administrative</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --watiqa-red: #d82a2a;
            --watiqa-light: #f8f8f8;
            --watiqa-dark: #333;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--watiqa-light);
            color: var(--watiqa-dark);
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        .confirmation-title {
            color: var(--watiqa-red);
            border-bottom: 2px solid var(--watiqa-red);
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        
        .summary-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-item {
            margin-bottom: 10px;
        }
        
        .btn-confirm {
            background-color: var(--watiqa-red);
            color: white;
            padding: 10px 25px;
            border: none;
            margin-right: 10px;
        }
        
        .btn-confirm:hover {
            background-color: #b32020;
            color: white;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 10px 25px;
            border: none;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
            color: white;
        }
        
        footer {
            background-color: rgb(2, 74, 2);
            color: white;
            padding: 30px 0;
        }
    </style>
</head>
<body>
    <header class="watiqa-header py-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <img src="uploads/Coat_of_arms_of_Morocco.png" alt="Logo" height="50" width="70">
                <div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="me-3">Bienvenue, <?php echo $_SESSION['user_name']?></span>
                        <a href="logout.php" class="btn btn-outline-secondary">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php" class="btn btn-outline-secondary me-2">Connexion</a>
                        <a href="register.php" class="btn watiqa-btn text-white">S'inscrire</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="confirmation-container">
        <h2 class="confirmation-title">Confirmation de votre demande</h2>
        
        <div class="summary-section">
            <h4>Localisation:</h4>
            <div class="summary-item"><strong>Région:</strong> <?= $region_name ?></div>
            <div class="summary-item"><strong>Préfecture/Province:</strong> <?= $prefecture_name ?></div>
            <div class="summary-item"><strong>Commune:</strong> <?= $commune_name ?></div>
            <div class="summary-item"><strong>Bureau d'état civil:</strong> <?= $office_name ?></div>
        </div>

        <div class="summary-section">
            <h4>Informations personnelles:</h4>
            <div class="summary-item"><strong>Type de document:</strong> 
                <?= $form_data['type_demande'] == 'copie_integrale' ? 'Copie intégrale' : 'Extrait' ?>
            </div>
            <div class="summary-item"><strong>Langue du document:</strong> 
                <?= $form_data['langue_document'] == 'arabe' ? 'العربية' : 
                   ($form_data['langue_document'] == 'francais' ? 'Français' : 'Bilingue') ?>
            </div>
            <div class="summary-item"><strong>Nom en arabe:</strong> <?= $form_data['nom_arabe'] ?></div>
            <div class="summary-item"><strong>Prénom en arabe:</strong> <?= $form_data['prenom_arabe'] ?></div>
            <?php if (!empty($form_data['nom_latin'])): ?>
                <div class="summary-item"><strong>Nom en latin:</strong> <?= $form_data['nom_latin'] ?></div>
            <?php endif; ?>
            <?php if (!empty($form_data['prenom_latin'])): ?>
                <div class="summary-item"><strong>Prénom en latin:</strong> <?= $form_data['prenom_latin'] ?></div>
            <?php endif; ?>
            <div class="summary-item"><strong>Date de naissance:</strong> <?= $form_data['date_naissance'] ?></div>
            <div class="summary-item"><strong>Sexe:</strong> <?= $form_data['sexe'] == 'male' ? 'ذكر' : 'أنثى' ?></div>
            <div class="summary-item"><strong>Numéro d'acte:</strong> <?= $form_data['numero_acte'] ?></div>
            <div class="summary-item"><strong>Année d'enregistrement:</strong> <?= $form_data['annee_enregistrement'] ?></div>
        </div>

        <div class="summary-section">
            <h4>Informations des parents:</h4>
            <div class="summary-item"><strong>Nom du père:</strong> <?= $form_data['nom_pere_arabe'] ?></div>
            <div class="summary-item"><strong>Prénom du père:</strong> <?= $form_data['prenom_pere_arabe'] ?></div>
            <div class="summary-item"><strong>Nom de la mère:</strong> <?= $form_data['nom_mere_arabe'] ?></div>
            <div class="summary-item"><strong>Prénom de la mère:</strong> <?= $form_data['prenom_mere_arabe'] ?></div>
        </div>

        <div class="summary-section">
            <h4>Fichier joint:</h4>
            <div class="summary-item"><strong>Copie de la CIN:</strong> <?= $file_info['name'] ?? 'Aucun fichier' ?></div>
        </div>

        <div class="text-end mt-4">
            <form method="post">
                <button type="submit" name="confirm" class="btn-confirm">Confirmer</button>
                <button type="submit" name="cancel" class="btn-cancel">Annuler</button>
            </form>
        </div>
    </div>

    <footer id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <img src="uploads/Coat_of_arms_of_Morocco.png" alt="Logo" height="150">
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#services">Services</a></li>
                        <li class="mb-2"><a href="#how-it-works">Comment ça marche</a></li>
                        <li class="mb-2"><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact</h5>
                    <p><i class="fas fa-phone me-2"></i> +212 5 20 20 20 20</p>
                    <p><i class="fas fa-envelope me-2"></i> contact@demandes.ma</p>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">© 2025 Plateforme de Demandes Administratives. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>