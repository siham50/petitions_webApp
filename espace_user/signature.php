<?php
require_once __DIR__ . '/../includes/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: liste_petitions.php');
    exit;
}

$petitionId = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM petition WHERE idP = ?");
$stmt->execute([$petitionId]);
$petition = $stmt->fetch();

if (!$petition) {
    header('Location: liste_petitions.php');
    exit;
}

$isActive = strtotime($petition['dateFinP']) > time();
if (!$isActive) {
    header('Location: liste_petitions.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ?");
$stmt->execute([$petitionId]);
$signatureCount = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signer la pétition - CitoyenVoix</title>
    <link rel="stylesheet" href="signature.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
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
                        <h1 class="logo-title">CitoyenVoix</h1>
                        <p class="logo-subtitle">Plateforme de pétitions citoyennes</p>
                    </div>
                </div>
                <nav class="nav-menu">
                    <a href="#" class="nav-link">À propos</a>
                    <a href="#" class="nav-link">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="signature-container">
            <div class="container">
                <a href="liste_petitions.php" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Retour aux pétitions
                </a>

                <!-- Message de succès -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="success-message">
                        <p> Merci ! Votre signature a été enregistrée avec succès.</p>
                    </div>
                <?php endif; ?>

                <!-- Message d'erreur -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <p> <?php echo htmlspecialchars($_GET['error']); ?></p>
                    </div>
                <?php endif; ?>

                <div class="signature-grid">
                    <!-- Carte d'information -->
                    <div class="info-card">
                        <h3>À propos de cette pétition</h3>
                        <div class="info-section">
                            <h4><?php echo htmlspecialchars($petition['titreP']); ?></h4>
                            <p><?php echo htmlspecialchars($petition['descriptionP']); ?></p>
                        </div>
                        
                        <div class="info-section info-divider">
                            <div class="info-progress">
                                <span class="info-progress-label">Signatures collectées</span>
                                <span><?php echo $signatureCount; ?> signatures</span>
                            </div>
                            <div class="info-bar">
                                <div class="info-bar-fill" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="info-callout">
                            <p><strong>Pourquoi signer ?</strong> Chaque signature rapproche cette pétition de son objectif et augmente son impact auprès des décideurs.</p>
                        </div>
                    </div>
                    
                    <!-- Formulaire de signature -->
                    <div class="form-card">
                        <h3>Signez cette pétition</h3>
                        <p class="card-subtitle">Vos informations seront utilisées uniquement pour cette pétition</p>
                        
                        <form method="POST" action="ajouter_signature.php" id="signatureForm">
                            <!-- Champ caché pour l'ID de la pétition -->
                            <input type="hidden" name="idP" value="<?php echo $petitionId; ?>">
                            
                            <!-- Titre de la pétition (lecture seule) -->
                            <div class="form-group">
                                <label class="form-label" for="titre">Titre de la pétition</label>
                                <input type="text" id="titre" class="form-input" value="<?php echo htmlspecialchars($petition['titreP']); ?>" readonly>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="prenom">Prénom *</label>
                                    <input type="text" id="prenom" name="prenom" class="form-input" required 
                                           value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="nom">Nom *</label>
                                    <input type="text" id="nom" name="nom" class="form-input" required
                                           value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-input" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="pays">Pays</label>
                                <input type="text" id="pays" name="pays" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['pays'] ?? ''); ?>"
                                       placeholder="Entrez votre pays">
                            </div>
                            
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary btn-submit">Envoyer ma signature</button>
                                <p>En signant, vous acceptez nos conditions d'utilisation et notre politique de confidentialité</p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Section des dernières signatures -->
                <div class="recent-signatures-section">
                    <div class="container">
                        <div class="recent-signatures-card">
                            <h3 class="recent-signatures-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                Dernières signatures 
                                <span class="live-indicator">
                                    <span class="live-dot"></span>
                                    EN DIRECT
                                </span>
                            </h3>
                            <p class="recent-signatures-subtitle">Les 5 dernières personnes ayant signé cette pétition - Mise à jour instantanée</p>
                            
                            <div id="recentSignaturesList" class="recent-signatures-list">
                                <!-- Les signatures seront chargées ici dynamiquement -->
                                <div class="loading-signatures">
                                    <div class="loading-spinner-small"></div>
                                    <p>Chargement des signatures en temps réel...</p>
                                </div>
                            </div>
                            
                            <div class="recent-signatures-update">
                                <span id="lastUpdateTime"></span>
                                <button onclick="loadRecentSignatures(true)" class="refresh-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 4v6h-6"/>
                                        <path d="M1 20v-6h6"/>
                                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                                    </svg>
                                    Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <h3>CitoyenVoix</h3>
                    <p>Une plateforme démocratique permettant aux citoyens de signer des pétitions pour faire entendre leur voix sur les sujets qui leur tiennent à cœur.</p>
                </div>
                <div class="footer-links">
                    <h4>Liens rapides</h4>
                    <ul>
                        <li><a href="#">Conditions d'utilisation</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="../espace_admin/login.php">Administration</a></li>
                    </ul>
                </div>
                <div class="footer-social">
                    <h4>Suivez-nous</h4>
                    <div class="social-icons">
                        <a href="#" class="social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 CitoyenVoix. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Validation du formulaire
        document.getElementById('signatureForm').addEventListener('submit', function(e) {
            const prenom = document.getElementById('prenom').value.trim();
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!prenom || !nom || !email) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires (*)');
                return false;
            }
            
            // Validation basique de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide');
                return false;
            }
        });

        // Gestion des signatures récentes en temps réel
        let lastSignaturesHash = '';
        let autoRefreshInterval = null;
        let retryCount = 0;
        const MAX_RETRIES = 5;

        function loadRecentSignatures(showLoading = false) {
            const petitionId = <?php echo $petitionId; ?>;
            const refreshBtn = document.querySelector('.refresh-btn');
            const signaturesList = document.getElementById('recentSignaturesList');
            
            if (showLoading && refreshBtn) {
                refreshBtn.classList.add('loading');
            }
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_recent_signatures.php?petition_id=${petitionId}&t=${new Date().getTime()}`, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.timeout = 3000; // Timeout court pour réactivité
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (refreshBtn) refreshBtn.classList.remove('loading');
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                updateSignaturesDisplay(response.signatures);
                                updateLastUpdateTime(response.timestamp);
                                
                                // Vérifier si de nouvelles signatures sont arrivées
                                const currentHash = generateSignaturesHash(response.signatures);
                                if (currentHash !== lastSignaturesHash && lastSignaturesHash !== '') {
                                    // Nouvelle signature détectée - pas de notification
                                }
                                lastSignaturesHash = currentHash;
                                retryCount = 0; // Réinitialiser le compteur d'erreurs
                                
                            } else {
                                showSignaturesError(response.message);
                            }
                        } catch (e) {
                            showSignaturesError('Erreur lors du traitement des données');
                        }
                    } else {
                        handleConnectionError();
                    }
                }
            };
            
            xhr.ontimeout = function() {
                handleConnectionError();
            };
            
            xhr.onerror = function() {
                handleConnectionError();
            };
            
            xhr.send();
        }

        function handleConnectionError() {
            retryCount++;
            if (retryCount <= MAX_RETRIES) {
                // Réessayer rapidement avec un backoff exponentiel
                const delay = Math.min(1000 * Math.pow(1.5, retryCount), 5000);
                setTimeout(() => loadRecentSignatures(), delay);
                showSignaturesError(`Connexion perdue - nouvelle tentative dans ${delay/1000}s...`);
            } else {
                showSignaturesError('Connexion interrompue - réessayez plus tard');
            }
        }

        function generateSignaturesHash(signatures) {
            return btoa(JSON.stringify(signatures.map(s => s.prenom + s.nom + s.date + s.heure)));
        }

        function updateSignaturesDisplay(signatures) {
            const signaturesList = document.getElementById('recentSignaturesList');
            
            if (signatures.length === 0) {
                signaturesList.innerHTML = `
                    <div class="no-signatures">
                        <p>Aucune signature pour le moment</p>
                        <p style="font-size: 0.75rem; margin-top: 0.5rem;">Soyez le premier à signer !</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            signatures.forEach((signature, index) => {
                // Marquer seulement la toute dernière signature comme nouvelle
                const isNew = index === 0 && signaturesList.children.length > 0;
                html += `
                    <div class="signature-item ${isNew ? 'new-signature' : ''}">
                        <div class="signature-info">
                            <div class="signature-avatar">
                                ${signature.initials}
                            </div>
                            <div class="signature-details">
                                <div class="signature-name">
                                    ${signature.prenom} ${signature.nom}
                                </div>
                                <div class="signature-meta">
                                    <span>${signature.display_date}</span>
                                    ${signature.pays ? `<span class="signature-country">${signature.pays}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="signature-time">
                            ${signature.display_time}
                        </div>
                    </div>
                `;
            });
            
            signaturesList.innerHTML = html;
        }

        function updateLastUpdateTime(timestamp) {
            const updateElement = document.getElementById('lastUpdateTime');
            if (updateElement) {
                const now = new Date();
                updateElement.textContent = `Dernière mise à jour: ${now.toLocaleTimeString('fr-FR')}`;
            }
        }

        function showSignaturesError(message) {
            const signaturesList = document.getElementById('recentSignaturesList');
            if (signaturesList) {
                signaturesList.innerHTML = `
                    <div class="no-signatures">
                        <p style="color: #dc2626;"> ${message}</p>
                        <button onclick="loadRecentSignatures(true)" class="refresh-btn" style="margin-top: 0.5rem;">
                            Réessayer maintenant
                        </button>
                    </div>
                `;
            }
        }

        // Configuration du rafraîchissement en temps réel
        function startRealTimeUpdates() {
            // Rafraîchissement très rapide - toutes les 2 secondes
            autoRefreshInterval = setInterval(() => {
                loadRecentSignatures();
            }, 2000);
        }

        function stopRealTimeUpdates() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }

        // Gestion de la visibilité de la page pour optimiser les performances
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopRealTimeUpdates();
            } else {
                startRealTimeUpdates();
                // Rafraîchir immédiatement quand la page redevient visible
                loadRecentSignatures(true);
            }
        });

        // Détection de la connexion réseau
        window.addEventListener('online', function() {
            startRealTimeUpdates();
            loadRecentSignatures(true);
        });

        window.addEventListener('offline', function() {
            stopRealTimeUpdates();
            showSignaturesError('Connexion perdue - vérifiez votre connexion internet');
        });

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Premier chargement immédiat
            loadRecentSignatures(true);
            
            // Démarrer les mises à jour en temps réel
            startRealTimeUpdates();
            
            // Rafraîchir aussi quand le formulaire est soumis
            const signatureForm = document.getElementById('signatureForm');
            if (signatureForm) {
                signatureForm.addEventListener('submit', function() {
                    // Rafraîchir immédiatement après la soumission
                    setTimeout(() => {
                        loadRecentSignatures(true);
                    }, 1000);
                });
            }
        });

        // Nettoyage
        window.addEventListener('beforeunload', function() {
            stopRealTimeUpdates();
        });
    </script>
</body>
</html>