<?php
require_once 'auth.php';

// Vérifier que l'admin est connecté
requireAdminAuth();

// Connexion à la base de données
require_once __DIR__ . '/../includes/database.php';

// Récupérer les informations de l'admin connecté
$adminInfo = getAdminInfo();

// Récupérer les pétitions depuis la base de données
try {
    $stmt = $pdo->query("SELECT * FROM petition ORDER BY dateAjoutP DESC");
    $petitions = $stmt->fetchAll();

    // Séparer les pétitions actives et fermées
    $activePetitions = [];
    $closedPetitions = [];

    foreach ($petitions as $petition) {
        $isActive = strtotime($petition['dateFinP']) > time();
        if ($isActive) {
            $activePetitions[] = $petition;
        } else {
            $closedPetitions[] = $petition;
        }
    }
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des pétitions: " . $e->getMessage();
    $activePetitions = [];
    $closedPetitions = [];
}

// Traitement de la déconnexion
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
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <div class="logo-section">
                <div class="logo-icon">
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
            <div class="header-right">
                <div class="user-info">
                    <p class="user-greeting">Bonjour, <?php echo htmlspecialchars($adminInfo['username'] ?? 'Admin'); ?></p>
                    <p class="user-role">Administrateur</p>
                </div>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Admin Title -->
            <div class="admin-title">
                <h1>Espace Admin</h1>
            </div>

            <!-- Add Petition Section -->
            <div class="add-petition-section">
                <div class="add-petition-content">
                    <div class="add-petition-text">
                        <h2>Ajouter une nouvelle pétition</h2>
                        <p>Créez une nouvelle pétition pour permettre aux citoyens de faire entendre leur voix</p>
                    </div>
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

            <!-- Petitions List -->
            <div class="petitions-section">
                <!-- Pétitions Actives -->
                <?php if (count($activePetitions) > 0): ?>
                <div class="petitions-category">
                    <h2 class="category-title active-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Pétitions Actives (<?php echo count($activePetitions); ?>)
                    </h2>
                    <div class="petitions-grid">
                        <?php foreach ($activePetitions as $petition): ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ?");
                            $stmt->execute([$petition['idP']]);
                            $signatureCount = $stmt->fetch()['count'];
                            ?>
                            
                            <div class="petition-card">
                                <div class="card-header">
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
                                
                                <div class="petition-stats">
                                    <div class="stat-item">
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
                                
                                <div class="petition-actions">
                                    <button class="btn-details" onclick="showDetails(<?php echo $petition['idP']; ?>)">Voir les détails</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Pétitions Fermées -->
                <?php if (count($closedPetitions) > 0): ?>
                <div class="petitions-category">
                    <h2 class="category-title closed-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        Pétitions Fermées (<?php echo count($closedPetitions); ?>)
                    </h2>
                    <div class="petitions-grid">
                        <?php foreach ($closedPetitions as $petition): ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ?");
                            $stmt->execute([$petition['idP']]);
                            $signatureCount = $stmt->fetch()['count'];
                            ?>
                            
                            <div class="petition-card">
                                <div class="card-header">
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
                                
                                <div class="petition-stats">
                                    <div class="stat-item">
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
                                
                                <div class="petition-actions">
                                    <button class="btn-details" onclick="showDetails(<?php echo $petition['idP']; ?>)">Voir les détails</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Message si aucune pétition -->
                <?php if (count($activePetitions) === 0 && count($closedPetitions) === 0): ?>
                    <div class="empty-state">
                        <h3>Aucune pétition</h3>
                        <p>Il n'y a actuellement aucune pétition à afficher.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
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
                <p class="footer-description">
                    Une plateforme démocratique permettant aux citoyens de signer des pétitions pour faire entendre leur voix sur les sujets qui leur tiennent à cœur.
                </p>
            </div>
            <div class="footer-copyright">
                <p>© 2025 CitoyenVoix. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <button class="modal-close" onclick="closeModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
                <h2 class="modal-title" id="modalTitle">Titre de la pétition</h2>
                <p class="modal-subtitle" id="modalSubtitle">Sous-titre de la pétition</p>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h3 class="modal-section-title">Description complète</h3>
                    <p class="modal-section-text" id="modalDescription">
                        Description de la pétition...
                    </p>
                </div>
                <div class="modal-section">
                    <h3 class="modal-section-title">Informations</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <p class="info-label">Porteur de la pétition</p>
                            <p class="info-value link" id="modalOrganizer">Nom du porteur</p>
                        </div>
                        <div class="info-card">
                            <p class="info-label">Email</p>
                            <p class="info-value" id="modalEmail">email@exemple.com</p>
                        </div>
                        <div class="info-card">
                            <p class="info-label">Date d'ajout</p>
                            <p class="info-value" id="modalStartDate">01/01/2025</p>
                        </div>
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
        // Données des pétitions depuis PHP (méthode sécurisée)
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
            echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        ?>;

        // Fonction pour afficher la modale avec les détails de la pétition
        function showDetails(petitionId) {
            const petition = petitionsData[petitionId];
            if (!petition) return;

            document.getElementById('modalTitle').textContent = petition.title;
            document.getElementById('modalSubtitle').textContent = petition.subtitle;
            document.getElementById('modalDescription').textContent = petition.description;
            document.getElementById('modalOrganizer').textContent = petition.organizer;
            document.getElementById('modalEmail').textContent = petition.email;
            document.getElementById('modalStartDate').textContent = petition.startDate;
            document.getElementById('modalEndDate').textContent = petition.endDate;

            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Fonction pour fermer la modale
        function closeModal(event) {
            if (!event || event.target.id === 'modalOverlay' || !event) {
                document.getElementById('modalOverlay').classList.remove('active');
                document.body.style.overflow = 'auto';
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