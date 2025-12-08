<?php
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once 'auth.php';

// Vérification que l'utilisateur est bien un administrateur connecté
requireAdminAuth();

// Connexion à la base de données
require_once __DIR__ . '/../includes/database.php';

// Récupérer les informations de l'admin connecté depuis la session
$adminInfo = getAdminInfo();

// Variables pour stocker les messages de feedback et les données du formulaire
$success = '';  // Message de succès après ajout réussi
$error = '';    // Message d'erreur en cas de problème
$formData = [
    'titre' => '',          // Titre de la pétition
    'description' => '',    // Description détaillée
    'dateFin' => '',        // Date de fin de la pétition
    'porteur' => '',        // Nom du porteur de la pétition
    'email' => ''           // Email du porteur
];

// TRAITEMENT DU FORMULAIRE LORS DE LA SOUMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire
    $formData['titre'] = trim($_POST['titre'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['dateFin'] = $_POST['dateFin'] ?? '';
    $formData['porteur'] = trim($_POST['porteur'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');

    // VALIDATION DES DONNÉES
    $errors = [];  // Tableau pour collecter les erreurs de validation

    // Validation du titre
    if (empty($formData['titre'])) {
        $errors[] = "Le titre de la pétition est obligatoire.";
    }

    // Validation de la description
    if (empty($formData['description'])) {
        $errors[] = "La description de la pétition est obligatoire.";
    }

    // Validation de la date de fin
    if (empty($formData['dateFin'])) {
        $errors[] = "La date de fin est obligatoire.";
    } elseif (strtotime($formData['dateFin']) <= time()) {
        $errors[] = "La date de fin doit être dans le futur.";
    }

    // Validation du nom du porteur
    if (empty($formData['porteur'])) {
        $errors[] = "Le nom du porteur est obligatoire.";
    }

    // Validation de l'email
    if (empty($formData['email'])) {
        $errors[] = "L'email du porteur est obligatoire.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // INSERTION DANS LA BASE DE DONNÉES SI AUCUNE ERREUR
    if (empty($errors)) {
        try {
            // Préparation de la requête d'insertion avec des paramètres sécurisés
            $stmt = $pdo->prepare("
                INSERT INTO petition (titreP, descriptionP, dateAjoutP, dateFinP, nomPorteurP, email) 
                VALUES (?, ?, NOW(), ?, ?, ?)
            ");

            // Exécution de la requête avec les données nettoyées
            $stmt->execute([
                $formData['titre'],
                $formData['description'],
                $formData['dateFin'],
                $formData['porteur'],
                $formData['email']
            ]);

            // Message de succès pour l'utilisateur
            $success = "La pétition a été ajoutée avec succès !";

            // SYSTÈME DE SIGNAL POUR LES NOUVELLES PÉTITIONS
            try {
                // Récupération de l'ID de la pétition nouvellement créée
                $lastId = $pdo->lastInsertId();
                
                // Chemin vers le fichier de signal dans le dossier includes
                $signalFile = __DIR__ . '/../includes/new_petition_signal.json';
                
                // Préparation des données du signal
                $signalData = [
                    'type' => 'new_petition',           // Type de signal
                    'petition_id' => $lastId,           // ID de la nouvelle pétition
                    'timestamp' => time(),              // Horodatage du signal
                    'title' => $formData['titre'],      // Titre pour l'affichage
                    'admin' => $adminInfo['username'] ?? 'Admin'  // Admin responsable
                ];
                
                // Écriture du fichier de signal
                $result = file_put_contents($signalFile, json_encode($signalData));
                
                // Log en cas d'échec d'écriture
                if ($result === false) {
                    error_log("Impossible d'écrire le fichier de signal dans: " . $signalFile);
                } else {
                    error_log("Signal créé avec succès: " . $signalFile);
                }
                
            } catch (Exception $e) {
                // Ne pas interrompre le processus principal si le signal échoue
                error_log("Erreur signal nouvelle pétition: " . $e->getMessage());
            }

            // RÉINITIALISATION DU FORMULAIRE APRÈS SUCCÈS
            $formData = [
                'titre' => '',
                'description' => '',
                'dateFin' => '',
                'porteur' => '',
                'email' => ''
            ];

        } catch (PDOException $e) {
            // Gestion des erreurs de base de données
            $error = "Erreur lors de l'ajout de la pétition : " . $e->getMessage();
        }
    } else {
        // Affichage des erreurs de validation
        $error = implode("<br>", $errors);
    }
}

// TRAITEMENT DE LA DÉCONNEXION
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
    <title>CitoyenVoix - Ajouter une Pétition</title>
    <link rel="stylesheet" href="ajouter_petition.css">
</head>
<body>
    <!-- En-tête de l'interface d'administration -->
    <header>
        <div class="header-left">
            <!-- Logo de l'application -->
            <div class="logo-box">
                <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <line x1="10" y1="9" x2="8" y2="9"/>
                </svg>
            </div>
            <div class="header-title">
                <h1>CitoyenVoix</h1>
                <p>Plateforme de pétitions citoyennes</p>
            </div>
        </div>
        <div class="header-right">
            <!-- Informations de l'utilisateur connecté -->
            <div class="user-info">
                <p>Bonjour, <?php echo htmlspecialchars($adminInfo['username'] ?? 'Admin'); ?></p>
                <p>Administrateur</p>
            </div>
            <!-- Bouton de déconnexion avec confirmation -->
            <a href="?logout=true" class="logout-btn" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                <svg class="logout-icon" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                Déconnexion
            </a>
        </div>
    </header>

    <!-- Contenu principal du formulaire -->
    <div class="main-content">
        <div class="container">
            <h2 class="page-title">Espace Admin</h2>

            <!-- Carte contenant le formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <h3>Ajouter une nouvelle pétition</h3>
                    <p>Créez une nouvelle pétition pour permettre aux citoyens de faire entendre leur voix</p>
                </div>

                <!-- Messages de feedback pour l'utilisateur -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'ajout de pétition -->
                <form id="petitionForm" method="POST" action="">
                    <!-- Champ : Titre de la pétition -->
                    <div class="form-group">
                        <label for="titre">Titre de la pétition</label>
                        <input 
                            type="text" 
                            id="titre" 
                            name="titre" 
                            placeholder="Ex: Pour le développement des énergies renouvelables"
                            value="<?php echo htmlspecialchars($formData['titre']); ?>"
                            required
                        >
                    </div>

                    <!-- Champ : Description détaillée -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            placeholder="Décrivez les objectifs et motivations de la pétition..."
                            required
                        ><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    </div>

                    <!-- Champ : Date de fin avec validation côté client -->
                    <div class="form-group">
                        <label for="dateFin">Date de fin</label>
                        <input 
                            type="date" 
                            id="dateFin" 
                            name="dateFin" 
                            value="<?php echo htmlspecialchars($formData['dateFin']); ?>"
                            required
                        >
                    </div>

                    <!-- Champ : Nom du porteur de la pétition -->
                    <div class="form-group">
                        <label for="porteur">Porteur de la pétition</label>
                        <input 
                            type="text" 
                            id="porteur" 
                            name="porteur" 
                            placeholder="Nom du porteur de la pétition"
                            value="<?php echo htmlspecialchars($formData['porteur']); ?>"
                            required
                        >
                    </div>

                    <!-- Champ : Email du porteur -->
                    <div class="form-group">
                        <label for="email">Email du porteur</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="exemple@email.com"
                            value="<?php echo htmlspecialchars($formData['email']); ?>"
                            required
                        >
                    </div>

                    <!-- Bouton de soumission du formulaire -->
                    <div class="button-container">
                        <button type="submit" class="submit-btn">
                            <svg class="plus-icon" viewBox="0 0 24 24">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Ajouter la nouvelle pétition
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <footer>
        <div class="footer-content">
            <div class="footer-header">
                <div class="footer-logo">
                    <svg class="footer-logo-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <p class="footer-copyright">
                © 2025 CitoyenVoix. Tous droits réservés.
            </p>
        </div>
    </footer>

    <script>
    // VALIDATION COTÉ CLIENT POUR LA DATE DE FIN
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('dateFin');
        const today = new Date();
        const tomorrow = new Date(today);
        
        // Définir la date minimale comme demain (au moins 1 jour dans le futur)
        tomorrow.setDate(today.getDate() + 1);
        
        // Formater la date pour l'attribut min (format YYYY-MM-DD)
        const minDate = tomorrow.toISOString().split('T')[0];
        dateInput.setAttribute('min', minDate);
        
        // Si une date existante est antérieure à demain, la réinitialiser
        if (dateInput.value && dateInput.value < minDate) {
            dateInput.value = minDate;
        }
    });
    </script>
</body>
</html>