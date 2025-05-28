<?php
session_start();
require 'connexion.php';
// kaychof en cas utilisateur kan da5el
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = '';
$regions = $conn->query("SELECT id, nom FROM regions");

// Handle dropdown selections
$region_id = $_POST['region_id'] ?? '';
$prefecture_province_id = $_POST['prefecture_province_id'] ?? '';
$commune_id = $_POST['commune_id'] ?? '';
$civil_status_office_id = $_POST['civil_status_office_id'] ?? '';

if ($region_id) {
    $prefectures = $conn->query("SELECT id, nom, type FROM prefectures_provinces WHERE region_id = $region_id");
}

if ($prefecture_province_id) {
    $communes = $conn->query("SELECT id, nom, type FROM communes WHERE prefecture_province_id = $prefecture_province_id");
}

if ($commune_id) {
    $civil_status_offices = $conn->query("SELECT id, nom FROM civil_status_offices WHERE commune_id = $commune_id");
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Validate and upload file
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileExt = pathinfo($_FILES['doc_cin']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExt;
    $targetFile = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['doc_cin']['tmp_name'], $targetFile)) {
        // Store file info and form data in session
        $_SESSION['file_info'] = [
            'name' => $fileName,
            'path' => $targetFile
        ];
        $_SESSION['form_data'] = $_POST;
        header("Location: confirmation.php");
        exit();
    } else {
        $error = "Erreur lors du téléchargement du fichier.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'acte de naissance | Plateforme Administrative</title>
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
        
        .watiqa-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .watiqa-form-container {
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 2rem;
        }
        
        .watiqa-form-title {
            color: var(--watiqa-red);
            border-bottom: 2px solid var(--watiqa-red);
            padding-bottom: 10px;
            margin-bottom: 1.5rem;
        }
        
        .watiqa-btn {
            background-color: var(--watiqa-red);
            border: none;
            padding: 10px 25px;
            font-weight: 500;
        }
        
        .watiqa-btn:hover {
            background-color: #b32020;
        }
        
        .required-field::after {
            content: " *";
            color: var(--watiqa-red);
        }
        
        .step-indicator {
            width: 40px;
            height: 40px;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .location-select {
            margin-bottom: 15px;
        }
        
        footer {
            background-color: rgb(2, 74, 2);
            color: white;
        }
        
        footer a {
            color: var(--secondary);
            text-decoration: none;
        }
        
        footer a:hover {
            color: white;
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

    <div class="container mb-5">
        <div class="watiqa-form-container">
            <h2 class="watiqa-form-title">Demande d'acte de naissance</h2>
            
            <div class="d-flex justify-content-between mb-5 text-center">
                <div>
                    <div class="rounded-circle bg-danger text-white step-indicator d-inline-flex align-items-center justify-content-center">1</div>
                    <p class="mt-2 mb-0 fw-medium">Localisation</p>
                </div>
                <div>
                    <div class="rounded-circle bg-secondary text-white step-indicator d-inline-flex align-items-center justify-content-center">2</div>
                    <p class="mt-2 mb-0 text-muted">Confirmation</p>
                </div>
                <div>
                    <div class="rounded-circle bg-secondary text-white step-indicator d-inline-flex align-items-center justify-content-center">3</div>
                    <p class="mt-2 mb-0 text-muted">Adresse et paiement</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <!-- Administrative divisions section at the top -->
                <div class="form-section">
                    <h5 class="mb-3">Localisation <span class="required-field"></span></h5>
                    
                    <div class="location-select">
                        <label for="region_id" class="form-label required-field">Région</label>
                        <select class="form-select" name="region_id" onchange="this.form.submit()" required>
                            <option value="">Sélectionnez une région</option>
                            <?php while ($region = $regions->fetch_assoc()): ?>
                                <option value="<?= $region['id'] ?>" <?= $region_id == $region['id'] ? 'selected' : '' ?>>
                                    <?= $region['nom'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <?php if ($region_id): ?>
                    <div class="location-select">
                        <label for="prefecture_province_id" class="form-label required-field">Préfecture/Province</label>
                        <select class="form-select" name="prefecture_province_id" onchange="this.form.submit()" required>
                            <option value="">Sélectionnez une préfecture/province</option>
                            <?php while ($pref = $prefectures->fetch_assoc()): ?>
                                <option value="<?= $pref['id'] ?>" <?= $prefecture_province_id == $pref['id'] ? 'selected' : '' ?>>
                                    <?= $pref['nom'] ?> (<?= $pref['type'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($prefecture_province_id): ?>
                    <div class="location-select">
                        <label for="commune_id" class="form-label required-field">Commune</label>
                        <select class="form-select" name="commune_id" onchange="this.form.submit()" required>
                            <option value="">Sélectionnez une commune</option>
                            <?php while ($commune = $communes->fetch_assoc()): ?>
                                <option value="<?= $commune['id'] ?>" <?= $commune_id == $commune['id'] ? 'selected' : '' ?>>
                                    <?= $commune['nom'] ?> (<?= $commune['type'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($commune_id): ?>
                    <div class="location-select">
                        <label for="civil_status_office_id" class="form-label required-field">Bureau d'état civil</label>
                        <select class="form-select" name="civil_status_office_id" required>
                            <option value="">Sélectionnez un bureau</option>
                            <?php while ($office = $civil_status_offices->fetch_assoc()): ?>
                                <option value="<?= $office['id'] ?>" <?= $civil_status_office_id == $office['id'] ? 'selected' : '' ?>>
                                    <?= $office['nom'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Document Type Section -->
                <div class="form-section">
                    <h5 class="mb-3">Type de document <span class="required-field"></span></h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="type_demande" id="copie_integrale" value="copie_integrale" checked>
                        <label class="form-check-label" for="copie_integrale">
                            Copie intégrale de l'acte de naissance
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="type_demande" id="extrait" value="extrait">
                        <label class="form-check-label" for="extrait">
                            Extrait de l'acte de naissance
                        </label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="langue_document" class="form-label required-field">Langue du document</label>
                        <select class="form-select" name="langue_document" required>
                            <option value="arabe">العربية</option>
                            <option value="francais">Français</option>
                            <option value="bilingue">Bilingue (العربية/Français)</option>
                        </select>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h5 class="mb-3">Informations personnelles</h5>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nom_arabe" class="form-label required-field">الاسم العائلي بالعربية</label>
                            <input type="text" class="form-control" name="nom_arabe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom_arabe" class="form-label required-field">الاسم الشخصي بالعربية</label>
                            <input type="text" class="form-control" name="prenom_arabe" required>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nom_latin" class="form-label">Nom en latin (optionnel)</label>
                            <input type="text" class="form-control" name="nom_latin">
                        </div>
                        <div class="col-md-6">
                            <label for="prenom_latin" class="form-label">Prénom en latin (optionnel)</label>
                            <input type="text" class="form-control" name="prenom_latin">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="date_naissance" class="form-label required-field">Date de naissance</label>
                            <input type="date" class="form-control" name="date_naissance" required>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="ignore_jour_mois" id="ignore_jour_mois">
                                <label class="form-check-label" for="ignore_jour_mois">
                                    Je ne connais pas le jour/mois de naissance
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Sexe</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sexe" id="male" value="male" checked>
                                    <label class="form-check-label" for="male">ذكر</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sexe" id="female" value="female">
                                    <label class="form-check-label" for="female">أنثى</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="form-section">
                    <h5 class="mb-3">Détails du document</h5>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="numero_acte" class="form-label required-field">رقم العقد</label>
                            <input type="text" class="form-control" name="numero_acte" required>
                            <small class="text-muted"><a href="">أين أجد هذه المعلومة ؟</a></small>
                        </div>
                        <div class="col-md-6">
                            <label for="annee_enregistrement" class="form-label required-field">سنة تسجيل العقد</label>
                            <input type="number" class="form-control" name="annee_enregistrement" min="1900" max="<?= date('Y') ?>" required>
                            <small class="text-muted"><a href="">أين أجد هذه المعلومة ؟</a></small>
                        </div>
                    </div>
                </div>

                <!-- Parents Information -->
                <div class="form-section">
                    <h5 class="mb-3">Informations des parents</h5>
                    
                    <h6 class="mt-4 mb-3">الأب</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nom_pere_arabe" class="form-label required-field">الاسم العائلي</label>
                            <input type="text" class="form-control" name="nom_pere_arabe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom_pere_arabe" class="form-label required-field">الاسم الشخصي</label>
                            <input type="text" class="form-control" name="prenom_pere_arabe" required>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3">الأم</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom_mere_arabe" class="form-label required-field">الاسم العائلي</label>
                            <input type="text" class="form-control" name="nom_mere_arabe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom_mere_arabe" class="form-label required-field">الاسم الشخصي</label>
                            <input type="text" class="form-control" name="prenom_mere_arabe" required>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="form-section">
                    <h5 class="mb-3">Pièces jointes <span class="required-field"></span></h5>
                    <div class="mb-3">
                        <label for="doc_cin" class="form-label required-field">Copie de la CIN</label>
                        <input class="form-control" type="file" name="doc_cin" required>
                        <small class="text-muted">Format: PDF, JPG ou PNG</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-outline-secondary px-4">Retour</a>
                    <?php if ($commune_id): ?>
                        <button type="submit" name="submit" class="btn watiqa-btn text-white px-4">Envoyer la demande</button>
                    <?php endif; ?>
                </div>
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