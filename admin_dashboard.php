<?php
session_start();
require 'connexion.php';

// chof ila kand admin mconicter
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get admin information
$admin_id = $_SESSION['admin_id'];
$admin_query = $conn->query("SELECT * FROM admins WHERE id = $admin_id");
$admin = $admin_query->fetch_assoc();

// Get all pending requests
$pending_requests = $conn->query("
    SELECT dn.*, u.nom as user_nom, u.prenom as user_prenom, u.email as user_email,
           r.nom as region_nom, pp.nom as prefecture_nom, c.nom as commune_nom, cso.nom as bureau_nom
    FROM demandes_naissance dn
    JOIN utilisateurs u ON dn.utilisateur_id = u.id
    LEFT JOIN regions r ON dn.region_id = r.id
    LEFT JOIN prefectures_provinces pp ON dn.prefecture_province_id = pp.id
    LEFT JOIN communes c ON dn.commune_id = c.id
    LEFT JOIN civil_status_offices cso ON dn.civil_status_office_id = cso.id
    WHERE dn.statut IN ('en_attente', 'en_traitement')
    ORDER BY dn.date_demande ASC
");

// Get all users
$users = $conn->query("SELECT * FROM utilisateurs ORDER BY nom ASC");

// Handle status updates
if (isset($_POST['update_status'])) {
    $demande_id = $_POST['demande_id'];
    $new_status = $_POST['new_status'];

    $conn->query("UPDATE demandes_naissance SET statut = '$new_status' WHERE id = $demande_id");

    header("Location: admin_dashboard.php");
    exit();
}



?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin | Plateforme Administrative</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --watiqa-red: #d82a2a;
            --watiqa-light: #f8f8f8;
            --watiqa-dark: #333;
            --admin-blue: #2a5d82;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--watiqa-light);
            color: var(--watiqa-dark);
        }
        
        .admin-header {
            background-color: var(--admin-blue);
            color: white;
        }
        
        .sidebar {
            background-color: #2E2E2E  ;
            color: white;
            min-height: 100vh;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 15px;
            margin-bottom: 5px;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-container {
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 2rem;
        }
        
        .admin-title {
            color: var(--admin-blue);
            border-bottom: 2px solid var(--admin-blue);
            padding-bottom: 10px;
            margin-bottom: 1.5rem;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .status-en_attente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-en_traitement {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-pret {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-livre {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .demande-card {
            border-left: 4px solid var(--admin-blue);
            transition: all 0.3s ease;
        }
        
        .demande-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--admin-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .document-preview {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar  collapse show">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="uploads/Untitled-2.png" alt="Logo" height="70">
                        <h5 class="mt-2">Administration Dashboard</h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#demandes">
                                <i class="fas fa-file-alt"></i> Demandes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#utilisateurs">
                                <i class="fas fa-users"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#documents">
                                <i class="fas fa-file-upload"></i> Documents
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#statistiques">
                                <i class="fas fa-chart-bar"></i> Statistiques
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="admin_logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tableau de bord Administrateur</h1>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2">
                            <?= substr($admin['nom'], 0, 1) . substr($admin['prenom'], 0, 1) ?>
                        </div>
                        <div>
                            <div class="fw-bold"><?= $admin['prenom'] . ' ' . $admin['nom'] ?></div>
                            <small class="text-muted"><?= $admin['email'] ?></small>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Demandes</h5>
                                        <h2 class="mb-0">
                                            <?php 
                                                $count = $conn->query("SELECT COUNT(*) FROM demandes_naissance")->fetch_row()[0];
                                                echo $count;
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="fas fa-file-alt fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">En attente</h5>
                                        <h2 class="mb-0">
                                            <?php 
                                                $count = $conn->query("SELECT COUNT(*) FROM demandes_naissance WHERE statut = 'en_attente'")->fetch_row()[0];
                                                echo $count;
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="fas fa-clock fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">En traitement</h5>
                                        <h2 class="mb-0">
                                            <?php 
                                                $count = $conn->query("SELECT COUNT(*) FROM demandes_naissance WHERE statut = 'en_traitement'")->fetch_row()[0];
                                                echo $count;
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="fas fa-cog fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Utilisateurs</h5>
                                        <h2 class="mb-0">
                                            <?php 
                                                $count = $conn->query("SELECT COUNT(*) FROM utilisateurs")->fetch_row()[0];
                                                echo $count;
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demandes Section -->
                <div id="demandes" class="admin-container mb-4">
                    <h2 class="admin-title">
                        <i class="fas fa-file-alt me-2"></i>Gestion des demandes
                    </h2>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Référence</th>
                                    <th>Utilisateur</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Localisation</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($request = $pending_requests->fetch_assoc()): ?>
                                    <tr class="demande-card">
                                        <td>DN-<?= str_pad($request['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <?= $request['user_prenom'] . ' ' . $request['user_nom'] ?>
                                            <br>
                                            <small class="text-muted"><?= $request['user_email'] ?></small>
                                        </td>
                                        <td>
                                            <?= $request['type_demande'] == 'copie_integrale' ? 'Copie intégrale' : 'Extrait' ?>
                                            <br>
                                            <small class="text-muted"><?= $request['langue_document'] ?></small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($request['date_demande'])) ?>
                                            <br>
                                            <small class="text-muted"><?= date('H:i', strtotime($request['date_demande'])) ?></small>
                                        </td>
                                        <td>
                                            <?= $request['commune_nom'] ?><br>
                                            <small class="text-muted">
                                                <?= $request['prefecture_nom'] ?>, <?= $request['region_nom'] ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php 
                                                $status_class = '';
                                                $status_text = '';
                                                switch($request['statut']) {
                                                    case 'en_attente':
                                                        $status_class = 'status-en_attente';
                                                        $status_text = 'En attente';
                                                        break;
                                                    case 'en_traitement':
                                                        $status_class = 'status-en_traitement';
                                                        $status_text = 'En traitement';
                                                        break;
                                                    case 'pret':
                                                        $status_class = 'status-pret';
                                                        $status_text = 'Prêt';
                                                        break;
                                                    case 'livre':
                                                        $status_class = 'status-livre';
                                                        $status_text = 'Livré';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge <?= $status_class ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#requestModal<?= $request['id'] ?>">
                                                <i class="fas fa-edit"></i> Gérer
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Request Management Modal -->
                                    <div class="modal fade" id="requestModal<?= $request['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Gestion de la demande DN-<?= str_pad($request['id'], 6, '0', STR_PAD_LEFT) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-4">
                                                        <div class="col-md-6">
                                                            <h6>Informations générales</h6>
                                                            <p><strong>Utilisateur:</strong> 
                                                                <?= $request['user_prenom'] . ' ' . $request['user_nom'] ?>
                                                                (<?= $request['user_email'] ?>)
                                                            </p>
                                                            <p><strong>Type:</strong> 
                                                                <?= $request['type_demande'] == 'copie_integrale' ? 'Copie intégrale' : 'Extrait' ?>
                                                            </p>
                                                            <p><strong>Langue:</strong> 
                                                                <?= $request['langue_document'] == 'arabe' ? 'العربية' : 
                                                                   ($request['langue_document'] == 'francais' ? 'Français' : 'Bilingue') ?>
                                                            </p>
                                                            <p><strong>Date de demande:</strong> 
                                                                <?= date('d/m/Y H:i', strtotime($request['date_demande'])) ?>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Localisation</h6>
                                                            <p><strong>Bureau d'état civil:</strong> <?= $request['bureau_nom'] ?></p>
                                                            <p><strong>Commune:</strong> <?= $request['commune_nom'] ?></p>
                                                            <p><strong>Préfecture/Province:</strong> <?= $request['prefecture_nom'] ?></p>
                                                            <p><strong>Région:</strong> <?= $request['region_nom'] ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-4">
                                                        <div class="col-md-6">
                                                            <h6>Informations personnelles</h6>
                                                            <p><strong>Nom:</strong> <?= $request['nom_arabe'] ?></p>
                                                            <p><strong>Prénom:</strong> <?= $request['prenom_arabe'] ?></p>
                                                            <?php if ($request['nom_latin']): ?>
                                                                <p><strong>Nom latin:</strong> <?= $request['nom_latin'] ?></p>
                                                            <?php endif; ?>
                                                            <?php if ($request['prenom_latin']): ?>
                                                                <p><strong>Prénom latin:</strong> <?= $request['prenom_latin'] ?></p>
                                                            <?php endif; ?>
                                                            <p><strong>Date de naissance:</strong> 
                                                                <?= date('d/m/Y', strtotime($request['date_naissance_complete'])) ?>
                                                                <?= $request['ignore_jour_mois'] ? '(Jour/mois inconnus)' : '' ?>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Détails de l'acte</h6>
                                                            <p><strong>Numéro d'acte:</strong> <?= $request['numero_acte'] ?></p>
                                                            <p><strong>Année d'enregistrement:</strong> <?= $request['annee_enregistrement'] ?></p>
                                                            <p><strong>Sexe:</strong> <?= $request['sexe'] == 'male' ? 'ذكر' : 'أنثى' ?></p>
                                                            <h6 class="mt-3">Parents</h6>
                                                            <p><strong>Père:</strong> <?= $request['nom_pere_arabe'] ?> <?= $request['prenom_pere_arabe'] ?></p>
                                                            <p><strong>Mère:</strong> <?= $request['nom_mere_arabe'] ?> <?= $request['prenom_mere_arabe'] ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-4">
                                                        <div class="col-md-6">
                                                            <h6>Document joint</h6>
                                                            <?php if ($request['doc_cin']): ?>
                                                                <img src="<?= $request['doc_cin'] ?>" alt="Document CIN" class="document-preview mb-2">
                                                                <a href="<?= $request['doc_cin'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-download"></i> Télécharger
                                                                </a>
                                                            <?php else: ?>
                                                                <p class="text-muted">Aucun document joint</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Mettre à jour le statut</h6>
                                                            <form method="post">
                                                                <input type="hidden" name="demande_id" value="<?= $request['id'] ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <select class="form-select" name="new_status" required>
                                                                        <option value="en_attente" <?= $request['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                                                        <option value="en_traitement" <?= $request['statut'] == 'en_traitement' ? 'selected' : '' ?>>En traitement</option>
                                                                        <option value="pret" <?= $request['statut'] == 'pret' ? 'selected' : '' ?>>Prêt</option>
                                                                        <option value="livre" <?= $request['statut'] == 'livre' ? 'selected' : '' ?>>Livré</option>
                                                                    </select>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="notes" class="form-label">Notes (optionnel)</label>
                                                                    <textarea class="form-control" name="notes" rows="3" placeholder="Ajouter des notes ou commentaires..."></textarea>
                                                                </div>
                                                                
                                                                <div class="d-grid">
                                                                    <button type="submit" name="update_status" class="btn btn-primary">
                                                                        <i class="fas fa-save"></i> Enregistrer les modifications
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

               

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activate tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>