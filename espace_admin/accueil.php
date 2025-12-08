<?php
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once 'auth.php';

// Vérification que l'utilisateur est bien un administrateur connecté
requireAdminAuth();

// Connexion à la base de données
require_once __DIR__ . '/../includes/database.php';

// Récupérer les informations de l'admin connecté depuis la session
$adminInfo = getAdminInfo();

// Récupérer les pétitions depuis la base de données avec gestion d'erreurs
try {
    // Requête pour récupérer toutes les pétitions triées par date d'ajout (plus récentes en premier)
    $stmt = $pdo->query("SELECT * FROM petition ORDER BY dateAjoutP DESC");
    $petitions = $stmt->fetchAll();

    // Séparer les pétitions actives et fermées dans des tableaux distincts
    $activePetitions = [];  // Pétitions dont la date de fin n'est pas encore passée
    $closedPetitions = [];  // Pétitions dont la date de fin est dépassée

    foreach ($petitions as $petition) {
        // Vérifier si la pétition est encore active (date de fin > date actuelle)
        $isActive = strtotime($petition['dateFinP']) > time();
        if ($isActive) {
            $activePetitions[] = $petition;  // Ajouter aux pétitions actives
        } else {
            $closedPetitions[] = $petition;  // Ajouter aux pétitions fermées
        }
    }
    
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    $error = "Erreur lors de la récupération des pétitions: " . $e->getMessage();
    $activePetitions = [];
    $closedPetitions = [];
}

// Traitement de la déconnexion si le paramètre logout est présent dans l'URL
if (isset($_GET['logout'])) {
    header('Location: logout.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CitoyenVoix - Espace Admin</title>
    <link rel="stylesheet" href="accueil.css">
</head>
<body>
    <!-- En-tête de l'interface d'administration -->
    <header class="header">
        <div class="container header-content">
            <!-- Section du logo -->
            <div class="logo-section">
                <div class="logo-icon">
                    <!-- Icône SVG représentant un document (symbole des pétitions) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <line x1="10" y1="9" x2="8" y2="9"/>
                        </svg>
                </div>
                <div class="logo-text">
                    <h3>CitoyenVoix</h3>
                    <p>Plateforme de pétitions citoyennes</p>
                </div>
            </div>
            <!-- Section utilisateur avec informations et bouton de déconnexion -->
            <div class="header-right">
                <div class="user-info">
                    <p class="user-greeting">Bonjour, <?php echo htmlspecialchars($adminInfo['username'] ?? 'Admin'); ?></p>
                    <p class="user-role">Administrateur</p>
                </div>
                <!-- Bouton de déconnexion avec confirmation -->
                <a href="?logout=true" class="btn-logout" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Déconnexion
                </a>
            </div>
        </div>
    </header>

    <!-- Contenu principal de l'interface d'administration -->
    <main class="main-content">
        <div class="container">
            <!-- Titre de la page d'administration -->
            <div class="admin-title">
                <h1>Espace Admin</h1>
            </div>

            <!-- Section pour ajouter une nouvelle pétition -->
            <div class="add-petition-section">
                <div class="add-petition-content">
                    <div class="add-petition-text">
                        <h2>Ajouter une nouvelle pétition</h2>
                        <p>Créez une nouvelle pétition pour permettre aux citoyens de faire entendre leur voix</p>
                    </div>
                    <!-- Bouton pour accéder au formulaire d'ajout de pétition -->
                    <a href="ajouter_petition.php" class="btn-add">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                        Ajouter
                    </a>
                </div>
            </div>

            <!-- Section principale affichant la liste des pétitions -->
            <div class="petitions-section">
                <!-- Section des pétitions actives -->
                <?php if (count($activePetitions) > 0): ?>
                <div class="petitions-category">
                    <h2 class="category-title active-title">
                        <!-- Icône de validation pour les pétitions actives -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Pétitions Actives (<?php echo count($activePetitions); ?>)
                    </h2>
                    <div class="petitions-grid">
                        <!-- Boucle pour afficher chaque pétition active -->
                        <?php foreach ($activePetitions as $petition): ?>
                            <?php
                            // Compter le nombre de signatures pour cette pétition
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ?");
                            $stmt->execute([$petition['idP']]);
                            $signatureCount = $stmt->fetch()['count'];
                            ?>
                            
                            <!-- Carte individuelle pour chaque pétition -->
                            <div class="petition-card">
                                <div class="card-header">
                                    <!-- Badge indiquant le statut "Active" -->
                                    <div class="status-badge active">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                            <polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                        Active
                                    </div>
                                    <div class="card-title-row">
                                        <h3 class="card-title"><?php echo htmlspecialchars($petition['titreP']); ?></h3>
                                    </div>
                                    <p class="card-description"><?php echo htmlspecialchars($petition['descriptionP']); ?></p>
                                </div>
                                
                                <!-- Section des statistiques de la pétition -->
                                <div class="petition-stats">
                                    <div class="stat-item">
                                        <!-- Icône de personnes pour représenter les signatures -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                        <span><?php echo $signatureCount; ?> signatures</span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <!-- Icône de calendrier pour la date de fin -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            <span>Fin: <?php echo date('d/m/Y', strtotime($petition['dateFinP'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions disponibles pour cette pétition -->
                                <div class="petition-actions">
                                    <button class="btn-details" onclick="showDetails(<?php echo $petition['idP']; ?>)">Voir les détails</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section des pétitions fermées -->
                <?php if (count($closedPetitions) > 0): ?>
                <div class="petitions-category">
                    <h2 class="category-title closed-title">
                        <!-- Icône de croix pour les pétitions fermées -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        Pétitions Fermées (<?php echo count($closedPetitions); ?>)
                    </h2>
                    <div class="petitions-grid">
                        <!-- Boucle pour afficher chaque pétition fermée -->
                        <?php foreach ($closedPetitions as $petition): ?>
                            <?php
                            // Compter le nombre de signatures pour cette pétition fermée
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ?");
                            $stmt->execute([$petition['idP']]);
                            $signatureCount = $stmt->fetch()['count'];
                            ?>
                            
                            <!-- Carte individuelle pour chaque pétition fermée -->
                            <div class="petition-card">
                                <div class="card-header">
                                    <!-- Badge indiquant le statut "Fermée" -->
                                    <div class="status-badge closed">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                        Fermée
                                    </div>
                                    <div class="card-title-row">
                                        <h3 class="card-title"><?php echo htmlspecialchars($petition['titreP']); ?></h3>
                                    </div>
                                    <p class="card-description"><?php echo htmlspecialchars($petition['descriptionP']); ?></p>
                                </div>
                                
                                <!-- Section des statistiques de la pétition fermée -->
                                <div class="petition-stats">
                                    <div class="stat-item">
                                        <!-- Icône de personnes pour représenter les signatures -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                        <span><?php echo $signatureCount; ?> signatures</span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <!-- Icône de calendrier pour la date de fin -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            <span>Fin: <?php echo date('d/m/Y', strtotime($petition['dateFinP'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions disponibles pour cette pétition fermée -->
                                <div class="petition-actions">
                                    <button class="btn-details" onclick="showDetails(<?php echo $petition['idP']; ?>)">Voir les détails</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Message affiché quand il n'y a aucune pétition -->
                <?php if (count($activePetitions) === 0 && count($closedPetitions) === 0): ?>
                    <div class="empty-state">
                        <h3>Aucune pétition</h3>
                        <p>Il n'y a actuellement aucune pétition à afficher.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Pied de page de l'interface d'administration -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Logo dans le footer -->
                <div class="footer-logo">
                    <div class="logo-icon">
                       <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <line x1="10" y1="9" x2="8" y2="9"/>
                        </svg>
                    </div>
                    <h3>CitoyenVoix</h3>
                </div>
                <!-- Description de la plateforme -->
                <p class="footer-description">
                    Une plateforme démocratique permettant aux citoyens de signer des pétitions pour faire entendre leur voix sur les sujets qui leur tiennent à cœur.
                </p>
            </div>
            <!-- Copyright -->
            <div class="footer-copyright">
                <p>© 2025 CitoyenVoix. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Modal pour afficher les détails d'une pétition -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <!-- En-tête du modal avec bouton de fermeture -->
            <div class="modal-header">
                <button class="modal-close" onclick="closeModal()">
                    <!-- Icône de croix pour fermer le modal -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
                <h2 class="modal-title" id="modalTitle">Titre de la pétition</h2>
                <p class="modal-subtitle" id="modalSubtitle">Sous-titre de la pétition</p>
            </div>
            <!-- Corps du modal avec les informations détaillées -->
            <div class="modal-body">
                <!-- Section description -->
                <div class="modal-section">
                    <h3 class="modal-section-title">Description complète</h3>
                    <p class="modal-section-text" id="modalDescription">
                        Description de la pétition...
                    </p>
                </div>
                <!-- Section informations avec grille de détails -->
                <div class="modal-section">
                    <h3 class="modal-section-title">Informations</h3>
                    <div class="info-grid">
                        <!-- Carte d'information pour le porteur de la pétition -->
                        <div class="info-card">
                            <p class="info-label">Porteur de la pétition</p>
                            <p class="info-value link" id="modalOrganizer">Nom du porteur</p>
                        </div>
                        <!-- Carte d'information pour l'email -->
                        <div class="info-card">
                            <p class="info-label">Email</p>
                            <p class="info-value" id="modalEmail">email@exemple.com</p>
                        </div>
                        <!-- Carte d'information pour la date d'ajout -->
                        <div class="info-card">
                            <p class="info-label">Date d'ajout</p>
                            <p class="info-value" id="modalStartDate">01/01/2025</p>
                        </div>
                        <!-- Carte d'information pour la date de fin -->
                        <div class="info-card">
                            <p class="info-label">Date de fin</p>
                            <p class="info-value" id="modalEndDate">31/12/2025</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // DONNÉES ET FONCTIONNALITÉS DU MODAL

        // Données des pétitions transmises sécuritairement depuis PHP
        const petitionsData = <?php 
            $data = [];
            foreach ($petitions as $petition) {
                $data[$petition['idP']] = [
                    'title' => $petition['titreP'] ?? '',
                    'subtitle' => 'Pétition créée par ' . ($petition['nomPorteurP'] ?? 'Anonyme'),
                    'description' => $petition['descriptionP'] ?? '',
                    'organizer' => $petition['nomPorteurP'] ?? 'Anonyme',
                    'email' => $petition['email'] ?? '',
                    'startDate' => isset($petition['dateAjoutP']) ? date('d/m/Y', strtotime($petition['dateAjoutP'])) : '',
                    'endDate' => isset($petition['dateFinP']) ? date('d/m/Y', strtotime($petition['dateFinP'])) : ''
                ];
            }
            // Encodage JSON sécurisé pour éviter les injections XSS
            echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        ?>;

        // Fonction pour afficher la modale avec les détails d'une pétition spécifique
        function showDetails(petitionId) {
            // Récupération des données de la pétition depuis l'objet JavaScript
            const petition = petitionsData[petitionId];
            if (!petition) return; // Arrêt si la pétition n'existe pas

            // Mise à jour du contenu du modal avec les données de la pétition
            document.getElementById('modalTitle').textContent = petition.title;
            document.getElementById('modalSubtitle').textContent = petition.subtitle;
            document.getElementById('modalDescription').textContent = petition.description;
            document.getElementById('modalOrganizer').textContent = petition.organizer;
            document.getElementById('modalEmail').textContent = petition.email;
            document.getElementById('modalStartDate').textContent = petition.startDate;
            document.getElementById('modalEndDate').textContent = petition.endDate;

            // Affichage du modal et désactivation du défilement de la page
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Fonction pour fermer la modale
        function closeModal(event) {
            // Vérification que le clic est bien sur l'overlay (et non sur le contenu)
            if (!event || event.target.id === 'modalOverlay' || !event) {
                document.getElementById('modalOverlay').classList.remove('active');
                document.body.style.overflow = 'auto'; // Rétablissement du défilement
            }
        }

        // Fermer la modale avec la touche Échap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Page Admin chargée avec succès");
        });
    </script>
</body>
</html>