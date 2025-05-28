<?php
session_start();
require 'connexion.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all requests for the current user
$user_id = $_SESSION['user_id'];
$demandes = $conn->query("
    SELECT dn.*, r.nom as region_nom, pp.nom as prefecture_nom, c.nom as commune_nom, cso.nom as bureau_nom
    FROM demandes_naissance dn
    LEFT JOIN regions r ON dn.region_id = r.id
    LEFT JOIN prefectures_provinces pp ON dn.prefecture_province_id = pp.id
    LEFT JOIN communes c ON dn.commune_id = c.id
    LEFT JOIN civil_status_offices cso ON dn.civil_status_office_id = cso.id
    WHERE dn.utilisateur_id = $user_id
    ORDER BY dn.date_demande DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des demandes | Plateforme Administrative</title>
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
        
        .watiqa-container {
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .watiqa-title {
            color: var(--watiqa-red);
            border-bottom: 2px solid var(--watiqa-red);
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
            border-left: 4px solid var(--watiqa-red);
            transition: all 0.3s ease;
        }
        
        .demande-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        footer {
            background-color: rgb(2, 74, 2);
            color: white;
        }
        
        footer a {
            color: #6c757d;
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
                    <span class="me-3">Bienvenue, <?php echo $_SESSION['user_name']?></span>
                    <a href="logout.php" class="btn btn-outline-secondary">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mb-5">
        <div class="watiqa-container">
            <h2 class="watiqa-title">Suivi de vos demandes</h2>
            
            <?php if ($demandes->num_rows === 0): ?>
                <div class="alert alert-info">
                    Vous n'avez aucune demande pour le moment. <a href="demande_naissance.php" class="alert-link">Faire une nouvelle demande</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Localisation</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($demande = $demandes->fetch_assoc()): ?>
                                <tr class="demande-card">
                                    <td>DN-<?= str_pad($demande['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <?= $demande['type_demande'] == 'copie_integrale' ? 
                                            'Copie intégrale' : 'Extrait' ?>
                                        <br>
                                        <small class="text-muted"><?= $demande['langue_document'] ?></small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($demande['date_demande'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($demande['date_demande'])) ?></small>
                                    </td>
                                    <td>
                                        <?= $demande['commune_nom'] ?><br>
                                        <small class="text-muted">
                                            <?= $demande['prefecture_nom'] ?>, <?= $demande['region_nom'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            $status_class = '';
                                            $status_text = '';
                                            switch($demande['statut']) {
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
                                            data-bs-target="#detailsModal<?= $demande['id'] ?>">
                                            <i class="fas fa-eye"></i> Détails
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal<?= $demande['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Détails de la demande DN-<?= str_pad($demande['id'], 6, '0', STR_PAD_LEFT) ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Informations générales</h6>
                                                        <p><strong>Type:</strong> 
                                                            <?= $demande['type_demande'] == 'copie_integrale' ? 'Copie intégrale' : 'Extrait' ?>
                                                        </p>
                                                        <p><strong>Langue:</strong> 
                                                            <?= $demande['langue_document'] == 'arabe' ? 'العربية' : 
                                                               ($demande['langue_document'] == 'francais' ? 'Français' : 'Bilingue') ?>
                                                        </p>
                                                        <p><strong>Date de demande:</strong> 
                                                            <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?>
                                                        </p>
                                                        <p><strong>Statut:</strong> 
                                                            <span class="status-badge <?= $status_class ?>">
                                                                <?= $status_text ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Localisation</h6>
                                                        <p><strong>Bureau d'état civil:</strong> <?= $demande['bureau_nom'] ?></p>
                                                        <p><strong>Commune:</strong> <?= $demande['commune_nom'] ?></p>
                                                        <p><strong>Préfecture/Province:</strong> <?= $demande['prefecture_nom'] ?></p>
                                                        <p><strong>Région:</strong> <?= $demande['region_nom'] ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Informations personnelles</h6>
                                                        <p><strong>Nom:</strong> <?= $demande['nom_arabe'] ?></p>
                                                        <p><strong>Prénom:</strong> <?= $demande['prenom_arabe'] ?></p>
                                                        <?php if ($demande['nom_latin']): ?>
                                                            <p><strong>Nom latin:</strong> <?= $demande['nom_latin'] ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($demande['prenom_latin']): ?>
                                                            <p><strong>Prénom latin:</strong> <?= $demande['prenom_latin'] ?></p>
                                                        <?php endif; ?>
                                                        <p><strong>Date de naissance:</strong> 
                                                            <?= date('d/m/Y', strtotime($demande['date_naissance_complete'])) ?>
                                                            <?= $demande['ignore_jour_mois'] ? '(Jour/mois inconnus)' : '' ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Détails de l'acte</h6>
                                                        <p><strong>Numéro d'acte:</strong> <?= $demande['numero_acte'] ?></p>
                                                        <p><strong>Année d'enregistrement:</strong> <?= $demande['annee_enregistrement'] ?></p>
                                                        <p><strong>Sexe:</strong> <?= $demande['sexe'] == 'male' ? 'ذكر' : 'أنثى' ?></p>
                                                        <h6 class="mt-3">Parents</h6>
                                                        <p><strong>Père:</strong> <?= $demande['nom_pere_arabe'] ?> <?= $demande['prenom_pere_arabe'] ?></p>
                                                        <p><strong>Mère:</strong> <?= $demande['nom_mere_arabe'] ?> <?= $demande['prenom_mere_arabe'] ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Pour toute question concernant votre demande, veuillez contacter notre service client.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                <?php if ($demande['statut'] == 'pret'): ?>
                                                    <button type="button" class="btn btn-primary">
                                                        <i class="fas fa-download me-2"></i>Télécharger le document
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="demande_naissance.php" class="btn watiqa-btn text-white">
                    <i class="fas fa-plus me-2"></i>Nouvelle demande
                </a>
            </div>
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
                        <li class="mb-2"><a href="demande_naissance.php">Nouvelle demande</a></li>
                        <li class="mb-2"><a href="statut_demande.php">Suivi des demandes</a></li>
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