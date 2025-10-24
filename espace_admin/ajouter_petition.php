<?php
require_once 'auth.php';

// Vérifier que l'admin est connecté
requireAdminAuth();

// Connexion à la base de données
require_once __DIR__ . '/../includes/database.php';

// Récupérer les informations de l'admin connecté
$adminInfo = getAdminInfo();

// Variables pour stocker les messages et les données du formulaire
$success = '';
$error = '';
$formData = [
    'titre' => '',
    'description' => '',
    'dateFin' => '',
    'porteur' => '',
    'email' => ''
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $formData['titre'] = trim($_POST['titre'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['dateFin'] = $_POST['dateFin'] ?? '';
    $formData['porteur'] = trim($_POST['porteur'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');

    // Validation des données
    $errors = [];

    if (empty($formData['titre'])) {
        $errors[] = "Le titre de la pétition est obligatoire.";
    }

    if (empty($formData['description'])) {
        $errors[] = "La description de la pétition est obligatoire.";
    }

    if (empty($formData['dateFin'])) {
        $errors[] = "La date de fin est obligatoire.";
    } elseif (strtotime($formData['dateFin']) <= time()) {
        $errors[] = "La date de fin doit être dans le futur.";
    }

    if (empty($formData['porteur'])) {
        $errors[] = "Le nom du porteur est obligatoire.";
    }

    if (empty($formData['email'])) {
        $errors[] = "L'email du porteur est obligatoire.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // Si aucune erreur, insertion dans la base de données
    if (empty($errors)) {
        try {
            // Préparation de la requête d'insertion
            $stmt = $pdo->prepare("
                INSERT INTO petition (titreP, descriptionP, dateAjoutP, dateFinP, nomPorteurP, email) 
                VALUES (?, ?, NOW(), ?, ?, ?)
            ");

            // Exécution de la requête
            $stmt->execute([
                $formData['titre'],
                $formData['description'],
                $formData['dateFin'],
                $formData['porteur'],
                $formData['email']
            ]);

            // Message de succès
            $success = "La pétition a été ajoutée avec succès !";

        // Émettre un signal pour la nouvelle pétition
try {
    $lastId = $pdo->lastInsertId();
    
    // Utiliser le dossier includes existant à la racine
    $signalFile = __DIR__ . '/../includes/new_petition_signal.json';
    
    // Créer un fichier de signal
    $signalData = [
        'type' => 'new_petition',
        'petition_id' => $lastId,
        'timestamp' => time(),
        'title' => $formData['titre'],
        'admin' => $adminInfo['username'] ?? 'Admin'
    ];
    
    $result = file_put_contents($signalFile, json_encode($signalData));
    
    if ($result === false) {
        error_log("Impossible d'écrire le fichier de signal dans: " . $signalFile);
    } else {
        error_log("Signal créé avec succès: " . $signalFile);
    }
    
} catch (Exception $e) {
    // Ne pas interrompre le processus si le signal échoue
    error_log("Erreur signal nouvelle pétition: " . $e->getMessage());
}

// Réinitialisation du formulaire
$formData = [
    'titre' => '',
    'description' => '',
    'dateFin' => '',
    'porteur' => '',
    'email' => ''
];       
            // Réinitialisation du formulaire
            $formData = [
                'titre' => '',
                'description' => '',
                'dateFin' => '',
                'porteur' => '',
                'email' => ''
            ];

        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de la pétition : " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
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
    <title>CitoyenVoix - Ajouter une Pétition</title>
    <link rel="stylesheet" href="ajouter_petition.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-left">
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
            <div class="user-info">
                <p>Bonjour, <?php echo htmlspecialchars($adminInfo['username'] ?? 'Admin'); ?></p>
                <p>Administrateur</p>
            </div>
            <a href="?logout=true" class="logout-btn" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                <svg class="logout-icon" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                Déconnexion
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h2 class="page-title">Espace Admin</h2>

            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <h3>Ajouter une nouvelle pétition</h3>
                    <p>Créez une nouvelle pétition pour permettre aux citoyens de faire entendre leur voix</p>
                </div>

                <!-- Messages d'alerte -->
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

                <form id="petitionForm" method="POST" action="">
                    <!-- Titre de la pétition -->
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

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            placeholder="Décrivez les objectifs et motivations de la pétition..."
                            required
                        ><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    </div>

                    <!-- Date de fin -->
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

                    <!-- Porteur de la pétition -->
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

                    <!-- Email du porteur -->
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

                    <!-- Bouton d'ajout -->
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

    <!-- Footer -->
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
    // Définir la date minimale comme aujourd'hui + 1 jour
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('dateFin');
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        
        // Formater la date pour l'attribut min (YYYY-MM-DD)
        const minDate = tomorrow.toISOString().split('T')[0];
        dateInput.setAttribute('min', minDate);
    });
</script>
</body>
</html>