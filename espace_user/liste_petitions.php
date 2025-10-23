<?php
require_once __DIR__ . '/../includes/database.php';


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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CitoyenVoix - Plateforme de pétitions citoyennes</title>
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

        /* Hero Section */
        .hero-section {
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-indigo), var(--primary-purple));
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(2px);
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .hero-description {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 42rem;
            margin: 0 auto;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        /* Petitions Section */
        .petitions-section {
            padding: 3rem 0;
        }

       
        .petitions-category {
            margin-bottom: 3rem;
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid;
        }

        .category-title svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .active-title {
            color: #000000ff;
            border-color: #000000ff;
        }

        .closed-title {
            color: #dc2626;
            border-color: #dc2626;
        }

        /* Petitions Grid */
        .petitions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .petitions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Petition Card */
        .petition-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
        }

        .petition-card:hover {
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 10px 10px -5px rgba(37, 99, 235, 0.04);
            transform: translateY(-2px);
        }

        /* Amélioration de l'espacement dans les cartes */
        .card-header {
            padding: 1.5rem 1.5rem 0.5rem 1.5rem;
        }

        .card-content {
            padding: 0.5rem 1.5rem 1rem 1.5rem;
        }

        .card-footer {
            padding: 0 1.5rem 1.5rem;
        }

        .card-title-row {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 500;
            flex: 1;
        }

        /* Nouveaux styles pour les badges améliorés */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-lg);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .status-badge.active {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border-color: #16a34a;
        }

        .status-badge.closed {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border-color: #dc2626;
        }

        .status-badge svg {
            width: 14px;
            height: 14px;
        }

        .card-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .progress-section {
            margin-bottom: 1rem;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .progress-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .progress-text svg {
            width: 1rem;
            height: 1rem;
            color: var(--text-muted);
        }

        .progress-count {
            color: var(--primary-blue);
        }

        .progress-bar {
            width: 100%;
            height: 0.5rem;
            background: rgba(0, 0, 0, 0.1);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-indigo));
            transition: width 0.3s ease;
        }

        .card-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .meta-item svg {
            width: 1rem;
            height: 1rem;
        }

        .trending {
            color: #16a34a;
        }

        /* Buttons */
        .btn {
            padding: 0.625rem 1rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
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

        .btn-outline {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(4px);
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.7);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            max-width: 42rem;
            max-height: 80vh;
            overflow-y: auto;
            margin: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            z-index: 101;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid var(--border-color);
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
            transition: all 0.2s;
            z-index: 102;
        }

        .modal-close:hover {
            background: white;
        }

        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-section {
            margin-bottom: 1.5rem;
        }

        .modal-section:last-child {
            margin-bottom: 0;
        }

        .modal-section h4 {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .modal-section p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .modal-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(4px);
            padding: 1rem;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-color);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .stat-value.highlight {
            color: var(--primary-blue);
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

        /* No Petitions Message */
        .no-petitions {
            text-align: center;
            padding: 3rem 0;
        }

        .no-petitions p {
            color: var(--text-secondary);
        }

        /* Utility Classes */
        .hidden {
            display: none;
        }

        /* Section Pétition Populaire */
.popular-petition-section {
    padding: 2rem 0;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(79, 70, 229, 0.1) 50%, rgba(124, 58, 237, 0.1) 100%);
    border-bottom: 1px solid var(--border-color);
}

.popular-petition-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border: 2px solid;
    border-image: linear-gradient(135deg, var(--primary-blue), var(--primary-purple)) 1;
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-xl);
    position: relative;
    overflow: hidden;
}

.popular-petition-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-blue), var(--primary-purple));
}

.popular-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    box-shadow: var(--shadow-md);
}

.popular-badge svg {
    width: 1rem;
    height: 1rem;
}

.popular-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    align-items: center;
}

@media (max-width: 768px) {
    .popular-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.popular-info h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.popular-info p {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.popular-stats {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.popular-signature-count {
    text-align: center;
}

.popular-count {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-blue);
    line-height: 1;
}

.popular-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.popular-actions {
    display: flex;
    gap: 1rem;
}

.popular-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.popular-btn-primary {
    background: linear-gradient(135deg, var(--primary-blue), var(--primary-indigo));
    color: white;
    box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.3);
}

.popular-btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #4338ca);
    transform: translateY(-2px);
}

.popular-btn-outline {
    background: rgba(255, 255, 255, 0.6);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.popular-btn-outline:hover {
    background: rgba(255, 255, 255, 0.8);
}

/* Animation de mise à jour */
.popular-updating {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Loading State */
.popular-loading {
    text-align: center;
    padding: 2rem;
}

.loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 2px solid var(--border-color);
    border-top: 2px solid var(--primary-blue);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Transition pour le changement de pétition */
.popular-transition-enter {
    opacity: 0;
    transform: translateY(-20px);
}

.popular-transition-enter-active {
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.popular-transition-exit {
    opacity: 1;
}

.popular-transition-exit-active {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

/* Animation pour les compteurs mis à jour */
.count-updated {
    animation: countPulse 1s ease-in-out;
    color: var(--primary-blue);
    font-weight: 600;
}

@keyframes countPulse {
    0% {
        transform: scale(1);
        color: var(--primary-blue);
    }
    50% {
        transform: scale(1.1);
        color: #16a34a;
    }
    100% {
        transform: scale(1);
        color: var(--primary-blue);
    }
}

/* Style pour les éléments en cours de mise à jour */
.progress-count.updating {
    opacity: 0.7;
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
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-overlay"></div>
            <div class="container hero-content">
                <h1 class="hero-title">Faites entendre votre voix</h1>
                <p class="hero-description">
                    Découvrez les pétitions en cours et soutenez les causes qui vous tiennent à cœur. 
                    Ensemble, créons le changement.
                </p>
            </div>
        </div>

        <!-- Section Pétition Populaire -->
<div class="popular-petition-section">
    <div class="container">
        <div class="popular-petition-card" id="popularPetitionCard">
            <!-- Le contenu sera chargé dynamiquement -->
            <div class="popular-loading">
                <div class="loading-spinner"></div>
                <p>Chargement de la pétition populaire...</p>
            </div>
        </div>
    </div>
</div>

        <!-- Petitions Section -->
        <div class="container petitions-section">
            
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
                            
                            <div class="card-content">
                                <div class="progress-section">
    <div class="progress-header">
        <div class="progress-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>
                <span class="progress-count" id="signature-count-<?php echo $petition['idP']; ?>">
                    <?php echo $signatureCount; ?>
                </span>
                signatures
            </span>
        </div>
    </div>
    <div class="progress-bar">
        <div class="progress-fill" style="width: 100%"></div>
    </div>
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
                            
                            <div class="card-footer">
                                <a href="signature.php?id=<?php echo $petition['idP']; ?>" class="btn btn-primary">
                                    Signer la pétition
                                </a>
                                <button class="btn btn-outline" onclick="showPetitionDetails(<?php echo htmlspecialchars(json_encode($petition)); ?>)">
                                    Voir les détails
                                </button>
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
                            
                            <div class="card-content">
                                <div class="progress-section">
    <div class="progress-header">
        <div class="progress-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>
                <span class="progress-count" id="signature-count-<?php echo $petition['idP']; ?>">
                    <?php echo $signatureCount; ?>
                </span>
                signatures
            </span>
        </div>
    </div>
    <div class="progress-bar">
        <div class="progress-fill" style="width: 100%"></div>
    </div>
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
                            
                            <div class="card-footer">
                                <button class="btn btn-primary" style="opacity: 0.5; cursor: not-allowed;">
                                    Pétition fermée
                                </button>
                                <button class="btn btn-outline" onclick="showPetitionDetails(<?php echo htmlspecialchars(json_encode($petition)); ?>)">
                                    Voir les détails
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Message si aucune pétition -->
            <?php if (count($activePetitions) === 0 && count($closedPetitions) === 0): ?>
                <div class="no-petitions">
                    <p>Aucune pétition disponible pour le moment.</p>
                </div>
            <?php endif; ?>
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

    <!-- Modal for Petition Details -->
    <div id="petitionModal" class="modal">
        <div class="modal-overlay" id="modalOverlay"></div>
        <div class="modal-content">
            <button class="modal-close" id="modalClose">&times;</button>
            <div id="modalBody">
                <!-- Content will be inserted by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        
        const modal = document.getElementById('petitionModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const modalClose = document.getElementById('modalClose');
        const modalBody = document.getElementById('modalBody');

        
        document.addEventListener('DOMContentLoaded', () => {
            setupEventListeners();
        });

      
        function setupEventListeners() {
            
            modalClose.addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', closeModal);
        }

       
        function showPetitionDetails(petition) {
            modalBody.innerHTML = `
                <div class="modal-header">
                    <h2 class="modal-title">${petition.titreP}</h2>
                    <p class="modal-subtitle">Pétition créée par ${petition.nomPorteurP}</p>
                </div>
                <div class="modal-body">
                    <div class="modal-section">
                        <h4>Description complète</h4>
                        <p>${petition.descriptionP}</p>
                    </div>
                    
                    <div class="modal-section modal-divider">
                        <h4>Informations</h4>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <p class="stat-label">Porteur de la pétition</p>
                                <p class="stat-value highlight">${petition.nomPorteurP}</p>
                            </div>
                            <div class="stat-card">
                                <p class="stat-label">Email</p>
                                <p class="stat-value">${petition.email}</p>
                            </div>
                            <div class="stat-card">
                                <p class="stat-label">Date d'ajout</p>
                                <p class="stat-value">${new Date(petition.dateAjoutP).toLocaleDateString('fr-FR')}</p>
                            </div>
                            <div class="stat-card">
                                <p class="stat-label">Date de fin</p>
                                <p class="stat-value">${new Date(petition.dateFinP).toLocaleDateString('fr-FR')}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        
        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    </script>

<script>
    // Gestion de la pétition populaire en temps réel avec XMLHttpRequest
    let currentPopularPetitionId = null;

    function loadPopularPetition() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_popular_data.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.timeout = 5000; // Timeout de 5 secondes
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            updatePopularPetitionDisplay(data);
                        } else {
                            showNoPopularPetition();
                        }
                    } catch (error) {
                        console.error('Erreur parsing JSON:', error);
                        showNoPopularPetition();
                    }
                } else {
                    console.error('Erreur HTTP:', xhr.status);
                    showNoPopularPetition();
                }
            }
        };
        
        xhr.ontimeout = function() {
            console.error('Timeout lors du chargement de la pétition populaire');
            showNoPopularPetition();
        };
        
        xhr.onerror = function() {
            console.error('Erreur réseau lors du chargement de la pétition populaire');
            showNoPopularPetition();
        };
        
        xhr.send();
    }

    function updatePopularPetitionDisplay(data) {
        const popularCard = document.getElementById('popularPetitionCard');
        
        // Vérifier si la pétition a changé
        if (currentPopularPetitionId !== data.petition_id) {
            // Animation de transition
            popularCard.classList.add('popular-updating');
            setTimeout(() => {
                popularCard.innerHTML = createPopularPetitionHTML(data);
                popularCard.classList.remove('popular-updating');
                currentPopularPetitionId = data.petition_id;
            }, 500);
        } else {
            // Mise à jour simple des compteurs
            updatePopularPetitionCounters(data);
        }
    }

    function createPopularPetitionHTML(data) {
        const isActive = new Date(data.dateFinP) > new Date();
        const statusText = isActive ? 'Active' : 'Fermée';
        const statusClass = isActive ? 'active' : 'closed';
        
        return `
            <div class="popular-transition-enter-active">
                <div class="popular-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    PÉTITION LA PLUS POPULAIRE
                </div>
                
                <div class="status-badge ${statusClass}" style="margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        ${isActive ? 
                            '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>' :
                            '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'
                        }
                    </svg>
                    ${statusText}
                </div>
                
                <div class="popular-content">
                    <div class="popular-info">
                        <h3>${data.titreP}</h3>
                        <p>${data.descriptionP}</p>
                        <div class="popular-actions">
                            ${isActive ? 
                                `<a href="signature.php?id=${data.petition_id}" class="popular-btn popular-btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="8.5" cy="7" r="4"/>
                                        <line x1="20" y1="8" x2="20" y2="14"/>
                                        <line x1="23" y1="11" x2="17" y2="11"/>
                                    </svg>
                                    Signer cette pétition
                                </a>` :
                                `<button class="popular-btn" style="opacity: 0.5; cursor: not-allowed; background: #ccc;">
                                    Pétition fermée
                                </button>`
                            }
                            <button class="popular-btn popular-btn-outline" onclick="showPetitionDetails(${JSON.stringify(data).replace(/"/g, '&quot;')})">
                                Voir les détails
                            </button>
                        </div>
                    </div>
                    
                    <div class="popular-stats">
                        <div class="popular-signature-count">
                            <div class="popular-count" id="popularSignatureCount">${data.signature_count}</div>
                            <div class="popular-label">Signatures</div>
                        </div>
                        <div class="popular-meta">
                            <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                Fin: ${new Date(data.dateFinP).toLocaleDateString('fr-FR')}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                              Mise à jour en temps réel
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function updatePopularPetitionCounters(data) {
        const countElement = document.getElementById('popularSignatureCount');
        if (countElement) {
            // Animation du compteur
            const currentCount = parseInt(countElement.textContent);
            const targetCount = data.signature_count;
            
            if (currentCount !== targetCount) {
                animateCounter(countElement, currentCount, targetCount, 1000);
                
                // Animation visuelle pour indiquer la mise à jour
                countElement.classList.add('count-updated');
                setTimeout(() => {
                    countElement.classList.remove('count-updated');
                }, 1000);
            }
        }
    }

    function animateCounter(element, start, end, duration) {
        const startTime = performance.now();
        
        const step = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function pour une animation plus naturelle
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.floor(start + (end - start) * easeOutQuart);
            
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = end.toLocaleString();
            }
        };
        
        requestAnimationFrame(step);
    }

    function showNoPopularPetition(message = 'Aucune pétition populaire à afficher') {
        const popularCard = document.getElementById('popularPetitionCard');
        popularCard.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    ${message}
                </p>
                <button class="popular-btn popular-btn-primary" onclick="loadPopularPetition()">
                    Réessayer
                </button>
            </div>
        `;
    }

    // Charger la pétition populaire au démarrage
    document.addEventListener('DOMContentLoaded', function() {
        loadPopularPetition();
        
        // Mettre à jour toutes les 3 secondes
        setInterval(loadPopularPetition, 3000);
    });
</script>


    <script>
class SignatureUpdater {
    constructor() {
        this.updateInterval = 3000; // 3 secondes
        this.isUpdating = false;
        this.petitionIds = [];
        this.init();
    }

    init() {
        this.collectPetitionIds();
        this.startAutoUpdate();
        this.setupEventListeners();
    }

    collectPetitionIds() {
        const signatureElements = document.querySelectorAll('[id^="signature-count-"]');
        this.petitionIds = Array.from(signatureElements).map(element => 
            element.id.replace('signature-count-', '')
        );
    }

    updateAllCounts() {
        if (this.isUpdating) return;
        
        this.isUpdating = true;
        
        // Utiliser XMLHttpRequest au lieu de fetch
        const promises = this.petitionIds.map(petitionId => 
            this.updateSingleCount(petitionId)
        );
        
        Promise.allSettled(promises)
            .finally(() => {
                this.isUpdating = false;
            });
    }

    updateSingleCount(petitionId) {
        return new Promise((resolve) => {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_signatures.php?petition_id=${petitionId}`, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.timeout = 3000; // Timeout de 3 secondes
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            
                            if (data.success) {
                                const element = document.getElementById(`signature-count-${petitionId}`);
                                if (element) {
                                    const currentCount = parseInt(element.textContent);
                                    const newCount = data.signature_count;
                                    
                                    if (currentCount !== newCount) {
                                        this.animateCounter(element, currentCount, newCount, 500);
                                    }
                                }
                            }
                        } catch (error) {
                            console.error(`Erreur parsing JSON pour ${petitionId}:`, error);
                        }
                    } else {
                        console.error(`Erreur HTTP ${xhr.status} pour ${petitionId}`);
                    }
                    resolve();
                }
            }.bind(this); // Important: lier le contexte
            
            xhr.ontimeout = function() {
                console.error(`Timeout pour la pétition ${petitionId}`);
                resolve();
            };
            
            xhr.onerror = function() {
                console.error(`Erreur réseau pour ${petitionId}`);
                resolve();
            };
            
            xhr.send();
        });
    }

    animateCounter(element, start, end, duration) {
        return new Promise(resolve => {
            const startTime = performance.now();
            
            const step = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const currentValue = Math.floor(start + (end - start) * easeOutQuart);
                
                element.textContent = currentValue.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    element.textContent = end.toLocaleString();
                    element.classList.add('count-updated');
                    
                    setTimeout(() => {
                        element.classList.remove('count-updated');
                    }, 1000);
                    
                    resolve();
                }
            };
            
            requestAnimationFrame(step);
        });
    }

    startAutoUpdate() {
        // Première mise à jour après 2 secondes
        setTimeout(() => this.updateAllCounts(), 2000);
        
        // Mises à jour régulières toutes les 3 secondes
        setInterval(() => this.updateAllCounts(), this.updateInterval);
    }

    setupEventListeners() {
        // Mettre à jour quand la page redevient visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateAllCounts();
            }
        });

        // Mettre à jour quand la fenêtre reprend le focus
        window.addEventListener('focus', () => {
            this.updateAllCounts();
        });
    }

    // Méthode pour forcer une mise à jour manuelle
    forceUpdate() {
        this.updateAllCounts();
    }
}

// Initialiser le système de mise à jour
document.addEventListener('DOMContentLoaded', function() {
    window.signatureUpdater = new SignatureUpdater();
});
</script>

<script>
class PetitionNotification {
    constructor() {
        this.checkInterval = 5000; // Vérifier toutes les 5 secondes
        this.lastCheckTime = 0;
        this.isChecking = false;
        this.notificationContainer = null;
        this.init();
    }

    init() {
        this.createNotificationContainer();
        this.startPolling();
        this.setupEventListeners();
    }

    createNotificationContainer() {
        this.notificationContainer = document.createElement('div');
        this.notificationContainer.id = 'petition-notifications';
        this.notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        `;
        document.body.appendChild(this.notificationContainer);
    }

    startPolling() {
        // Première vérification après 2 secondes
        setTimeout(() => this.checkForNewPetitions(), 2000);
        
        // Vérifications régulières
        setInterval(() => this.checkForNewPetitions(), this.checkInterval);
    }

    checkForNewPetitions() {
        if (this.isChecking) return;
        
        this.isChecking = true;
        
        const xhr = new XMLHttpRequest();
        // Chemin corrigé vers le fichier dans le dossier includes en racine
        xhr.open('GET', '../includes/check_new_petitions.php?t=' + Date.now(), true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                this.isChecking = false;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        this.handleResponse(response);
                    } catch (error) {
                        console.error('Erreur parsing JSON:', error);
                    }
                }
            }
        };
        
        xhr.onerror = () => {
            this.isChecking = false;
            console.error('Erreur réseau lors de la vérification des nouvelles pétitions');
        };
        
        xhr.send();
    }

    handleResponse(response) {
        if (response.success && response.has_new_petition && response.petition) {
            this.showNewPetitionNotification(response.petition, response.message);
        }
    }

    showNewPetitionNotification(petition, message) {
        // Vérifier si une notification pour cette pétition existe déjà
        const existingNotifications = this.notificationContainer.querySelectorAll('.new-petition-notification');
        for (let notif of existingNotifications) {
            if (notif.dataset.petitionId === petition.idP.toString()) {
                return; // Ne pas afficher de doublon
            }
        }

        const notification = document.createElement('div');
        notification.className = 'new-petition-notification';
        notification.dataset.petitionId = petition.idP;
        notification.style.cssText = `
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            border-left: 4px solid #047857;
            animation: slideInRight 0.5s ease-out;
            cursor: pointer;
            position: relative;
            backdrop-filter: blur(10px);
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                <div style="flex-shrink: 0;">
                    <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600;">
                        Nouvelle Pétition !
                    </h4>
                    <p style="margin: 0 0 0.25rem 0; font-size: 0.875rem; opacity: 0.9;">
                        <strong>${petition.titreP}</strong>
                    </p>
                    <p style="margin: 0; font-size: 0.75rem; opacity: 0.8;">
                        ${message}
                    </p>
                </div>
                <button class="notification-close" style="background: none; border: none; color: white; cursor: pointer; padding: 0.25rem; opacity: 0.7; transition: opacity 0.2s;">
                    <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;

        // Ajouter au conteneur
        this.notificationContainer.insertBefore(notification, this.notificationContainer.firstChild);

        // Fermer au clic sur la croix
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeNotification(notification);
        });

        // Rediriger vers la pétition au clic
        notification.addEventListener('click', () => {
            window.location.href = `signature.php?id=${petition.idP}`;
        });

        // Fermer automatiquement après 8 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                this.removeNotification(notification);
            }
        }, 8000);
    }

    removeNotification(notification) {
        notification.style.animation = 'slideOutRight 0.5s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }

    setupEventListeners() {
        // Recharger la page quand elle redevient visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkForNewPetitions();
            }
        });
    }
}

// CSS pour les animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .new-petition-notification:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
    }
    
    .notification-close:hover {
        opacity: 1 !important;
    }
`;
document.head.appendChild(style);

// Initialiser le système de notification
document.addEventListener('DOMContentLoaded', function() {
    window.petitionNotifier = new PetitionNotification();
});
</script>

</body>
</html>