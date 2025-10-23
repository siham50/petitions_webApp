<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

// Rediriger si déjà connecté
if (isset($_SESSION['admin_id'])) {
    header('Location: accueil.php');
    exit;
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            // Vérifier les identifiants dans la base de données
            $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Connexion réussie
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_logged_in'] = true;

                // Rediriger vers la page d'accueil admin
                header('Location: accueil.php');
                exit;
            } else {
                $error = 'Nom d\'utilisateur ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
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
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Gradient Background */
        .bg-gradient {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #3b82f6 0%, #a855f7 50%, #9333ea 100%);
        }

        /* Decorative blobs */
        .blob {
            position: absolute;
            width: 384px;
            height: 384px;
            border-radius: 50%;
            mix-blend-mode: multiply;
            filter: blur(80px);
            opacity: 0.3;
            animation: pulse 3s ease-in-out infinite;
        }

        .blob-1 {
            top: 0;
            left: 0;
            background: #60a5fa;
        }

        .blob-2 {
            bottom: 0;
            right: 0;
            background: #c084fc;
            animation-delay: 1s;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.4;
            }
        }

        /* Content */
        .content {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            padding: 24px;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background: #2563eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo svg {
            width: 24px;
            height: 24px;
            color: white;
        }

        .header-text h1 {
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-container {
            width: 100%;
            max-width: 448px;
        }

        /* Glassmorphism Card */
        .glass-card {
            backdrop-filter: blur(40px);
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .card-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .icon-container {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .icon-container svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .card-header h2 {
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        /* Form */
        form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            color: white;
            font-size: 14px;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 14px;
            backdrop-filter: blur(8px);
            transition: all 0.2s;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        button {
            width: 100%;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: #1d4ed8;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
            backdrop-filter: blur(8px);
        }

        .forgot-password {
            margin-top: 24px;
            text-align: center;
        }

        .forgot-password a {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            text-decoration: underline;
        }

        .forgot-password a:hover {
            color: white;
        }

        .back-link {
            margin-top: 24px;
            text-align: center;
        }

        .back-link p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .back-link a {
            color: white;
            text-decoration: underline;
            margin-left: 4px;
        }

        /* Footer */
        footer {
            padding: 24px;
            text-align: center;
        }

        footer p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .blob {
                width: 256px;
                height: 256px;
            }

            .glass-card {
                padding: 24px;
            }

            header {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Gradient Background -->
        <div class="bg-gradient"></div>
        
        <!-- Decorative blobs -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        
        <!-- Content -->
        <div class="content">
            <!-- Header -->
            <header>
                <div class="header-content">
                    <div class="logo">
                        <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
    <polyline points="14 2 14 8 20 8"/>
    <line x1="16" y1="13" x2="8" y2="13"/>
    <line x1="16" y1="17" x2="8" y2="17"/>
    <line x1="10" y1="9" x2="8" y2="9"/>
</svg>
                    </div>
                    <div class="header-text">
                        <h1>CitoyenVoix</h1>
                        <p>Plateforme de pétitions citoyennes</p>
                    </div>
                </div>
            </header>

            <!-- Login Form -->
            <main>
                <div class="login-container">
                    <!-- Glassmorphism Card -->
                    <div class="glass-card">
                        <div class="card-header">
                            <div class="icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2>Espace Administrateur</h2>
                            <p>Connectez-vous pour gérer les pétitions</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="loginForm">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur</label>
                                <div class="input-wrapper">
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <input 
                                        type="text" 
                                        id="username" 
                                        name="username" 
                                        placeholder="Entrez votre nom d'utilisateur" 
                                        required
                                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password">Mot de passe</label>
                                <div class="input-wrapper">
                                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        placeholder="Entrez votre mot de passe" 
                                        required
                                    >
                                </div>
                            </div>

                            <button type="submit">Se connecter</button>
                        </form>
                    </div>

                    <div class="back-link">
                        <p>
                        <a href="../espace_user/liste_petitions.php">Retour à l'espace utilisateur</a>
                        </p>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer>
                <p>© 2025 CitoyenVoix - Faites entendre votre voix</p>
            </footer>
        </div>
    </div>

    <script>
        // Animation supplémentaire au focus des inputs
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Validation côté client
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
                return false;
            }
        });
    </script>
</body>
</html>