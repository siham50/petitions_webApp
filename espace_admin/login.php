<?php
// Démarrage de la session pour gérer l'état de connexion
session_start();

// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . '/../includes/database.php';

// Rediriger l'utilisateur vers l'accueil s'il est déjà connecté
if (isset($_SESSION['admin_id'])) {
    header('Location: accueil.php');
    exit;
}

// Variable pour stocker les messages d'erreur
$error = '';

// TRAITEMENT DU FORMULAIRE DE CONNEXION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation des champs obligatoires
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            // Préparation de la requête pour vérifier les identifiants
            $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            // Vérification du mot de passe avec password_verify (sécurisé)
            if ($admin && password_verify($password, $admin['password'])) {
                // CONNEXION RÉUSSIE - Création des variables de session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_logged_in'] = true;

                // Redirection vers la page d'accueil de l'administration
                header('Location: accueil.php');
                exit;
            } else {
                // Message d'erreur générique pour éviter de révéler trop d'informations
                $error = 'Nom d\'utilisateur ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
            // Gestion des erreurs de base de données
            $error = 'Erreur de connexion à la base de données';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CitoyenVoix - Connexion Admin</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <!-- Arrière-plan avec effet de dégradé -->
        <div class="bg-gradient"></div>
        
        <!-- Éléments décoratifs (blobs) pour l'arrière-plan -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        
        <!-- Contenu principal -->
        <div class="content">
            <!-- En-tête avec logo et titre -->
            <header>
                <div class="header-content">
                    <div class="logo">
                        <!-- Icône SVG représentant un document (symbole des pétitions) -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="header-text">
                        <h1>CitoyenVoix</h1>
                        <p>Plateforme de pétitions citoyennes</p>
                    </div>
                </div>
            </header>

            <!-- Contenu principal avec le formulaire de connexion -->
            <main>
                <div class="login-container">
                    <!-- Carte avec effet "glassmorphism" (transparence et flou) -->
                    <div class="glass-card">
                        <div class="card-header">
                            <!-- Icône de sécurité pour renforcer la confiance -->
                            <div class="icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2>Espace Administrateur</h2>
                            <p>Connectez-vous pour gérer les pétitions</p>
                        </div>

                        <!-- Affichage des messages d'erreur PHP -->
                        <?php if (!empty($error)): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulaire de connexion -->
                        <form method="POST" id="loginForm">
                            <!-- Champ : Nom d'utilisateur -->
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur</label>
                                <div class="input-wrapper">
                                    <!-- Icône utilisateur -->
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <input 
                                        type="text" 
                                        id="username" 
                                        name="username" 
                                        placeholder="Entrez votre nom d'utilisateur" 
                                        required
                                        // Conservation de la valeur en cas d'erreur (UX)
                                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                    >
                                </div>
                            </div>

                            <!-- Champ : Mot de passe -->
                            <div class="form-group">
                                <label for="password">Mot de passe</label>
                                <div class="input-wrapper">
                                    <!-- Icône de cadenas pour le mot de passe -->
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        placeholder="Entrez votre mot de passe" 
                                        required
                                        // Pas de conservation du mot de passe pour la sécurité
                                    >
                                </div>
                            </div>

                            <!-- Bouton de soumission -->
                            <button type="submit">Se connecter</button>
                        </form>
                    </div>

                    <!-- Lien de retour vers l'espace utilisateur -->
                    <div class="back-link">
                        <p>
                        <a href="../espace_user/liste_petitions.php">Retour à l'espace utilisateur</a>
                        </p>
                    </div>
                </div>
            </main>

            <!-- Pied de page -->
            <footer>
                <p>© 2025 CitoyenVoix - Faites entendre votre voix</p>
            </footer>
        </div>
    </div>

    <script>
        // ANIMATIONS ET INTERACTIONS COTÉ CLIENT

        // Animation au focus des champs de formulaire
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            // Effet de zoom léger quand le champ est focus
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            // Retour à la taille normale quand le focus est perdu
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Validation côté client pour améliorer l'expérience utilisateur
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // Récupération et nettoyage des valeurs
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Validation des champs obligatoires
            if (!username || !password) {
                e.preventDefault(); // Empêche l'envoi du formulaire
                alert('Veuillez remplir tous les champs');
                return false;
            }
        });
    </script>
</body>
</html>