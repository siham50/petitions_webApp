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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #2563eb;
            --primary-indigo: #4f46e5;
            --primary-purple: #7c3aed;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.2);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: var(--text-primary);
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 50%, #ede9fe 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            width: 100%;
        }

        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 0;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            width: 3rem;
            height: 3rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-indigo));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 0;
        }

        .logo-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0;
        }

        .nav-menu {
            display: flex;
            gap: 1.5rem;
        }

        .nav-link {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
        }

        /* Signature Container */
        .signature-container {
            min-height: calc(100vh - 80px);
            padding: 2rem 0;
        }

        .back-button {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(4px);
            border: 1px solid var(--border-color);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.6);
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            max-width: 64rem;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .signature-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card, .form-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-xl);
        }

        .info-card h3, .form-card h3 {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .info-section {
            margin-bottom: 1.5rem;
        }

        .info-section h4 {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .info-section p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .info-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 1.5rem;
        }

        .info-progress {
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
        }

        .info-progress-label {
            color: var(--text-muted);
        }

        .info-bar {
            height: 0.5rem;
            background: rgba(255, 255, 255, 0.4);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .info-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-indigo));
        }

        .info-callout {
            background: rgba(219, 234, 254, 0.6);
            border: 1px solid rgba(37, 99, 235, 0.3);
            border-radius: var(--radius-xl);
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .info-callout p {
            font-size: 0.875rem;
            color: #1e40af;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(4px);
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
        }

        .form-footer p {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 1rem;
        }

        .btn {
            padding: 0.625rem 1rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-indigo));
            color: white;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #1d4ed8, #4338ca);
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.875rem;
        }

        /* Success Message */
        .success-message {
            background: rgba(187, 247, 208, 0.6);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: var(--radius-xl);
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .success-message p {
            color: #065f46;
            font-weight: 500;
        }

        /* Error Message */
        .error-message {
            background: rgba(254, 226, 226, 0.6);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: var(--radius-xl);
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .error-message p {
            color: #991b1b;
            font-weight: 500;
        }

        /* Section des dernières signatures */
        .recent-signatures-section {
            padding: 2rem 0;
            max-width: 64rem;
            margin: 0 auto;
        }

        .recent-signatures-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .recent-signatures-card::before {
            content: 'Temps réel';
            position: absolute;
            top: -10px;
            right: 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 12px;
            animation: gentlePulse 2s infinite;
        }

        @keyframes gentlePulse {
            0% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
            100% { opacity: 0.8; transform: scale(1); }
        }

        @media (max-width: 768px) {
            .recent-signatures-card::before {
                position: static;
                display: block;
                margin-bottom: 1rem;
                text-align: center;
            }
        }

        .recent-signatures-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
        }

        .recent-signatures-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .recent-signatures-list {
            min-height: 200px;
        }

        .signature-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.3s ease-out;
        }

        .signature-item:last-child {
            border-bottom: none;
        }

        .signature-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .signature-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .signature-details {
            display: flex;
            flex-direction: column;
        }

        .signature-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        .signature-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            gap: 0.5rem;
        }

        .signature-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .loading-signatures {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        .loading-spinner-small {
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid var(--border-color);
            border-top: 2px solid var(--primary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        .recent-signatures-update {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .refresh-btn {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--border-color);
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s;
        }

        .refresh-btn:hover {
            background: rgba(255, 255, 255, 0.7);
        }

        .refresh-btn:active {
            transform: scale(0.95);
        }

        .refresh-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .refresh-btn.loading svg {
            animation: spin 1s linear infinite;
        }

        .no-signatures {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        .signature-item.new-signature {
            background: rgba(34, 197, 94, 0.15);
            border-radius: var(--radius-md);
            margin: 0.25rem 0;
            padding: 0.75rem 1rem;
            animation: highlightPulse 1.5s ease-out;
            border-left: 3px solid #10b981;
        }

        .signature-country {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .signature-country::before {
            content: "•";
            color: var(--text-muted);
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #10b981;
            font-weight: 600;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: livePulse 1.5s infinite;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes highlightPulse {
            0% {
                background: rgba(34, 197, 94, 0.3);
                transform: translateX(-10px);
            }
            60% {
                background: rgba(34, 197, 94, 0.15);
                transform: translateX(0);
            }
            100% {
                background: rgba(34, 197, 94, 0.15);
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes livePulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 768px) {
            .recent-signatures-card {
                padding: 1.5rem;
            }
            
            .signature-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .signature-time {
                align-self: flex-end;
            }
        }

        /* Footer */
        .footer {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            margin-top: 4rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 2rem;
            padding: 3rem 0;
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        .footer-about h3 {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .footer-about p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .footer-links h4, .footer-social h4 {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--text-primary);
        }

        .social-icons {
            display: flex;
            gap: 0.75rem;
        }

        .social-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(4px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s;
        }

        .social-icon:hover {
            background: rgba(255, 255, 255, 0.7);
        }

        .footer-bottom {
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            text-align: center;
        }

        .footer-bottom p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
    </style>
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
                                    showNewSignatureNotification(response.signatures[0]);
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

        function showNewSignatureNotification(newSignature) {
            // Créer une notification toast plus visible
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-xl);
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
                font-size: 0.875rem;
                font-weight: 500;
                max-width: 300px;
                border-left: 4px solid #047857;
            `;
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span style="font-size: 1.2em;"></span>
                    <strong>Nouvelle signature !</strong>
                </div>
                <div>${newSignature.prenom} ${newSignature.nom} vient de signer</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 4000);
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

        // Styles d'animation pour les notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>