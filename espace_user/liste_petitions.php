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
                                                <span class="progress-count"><?php echo $signatureCount; ?></span>
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
                                                <span class="progress-count"><?php echo $signatureCount; ?></span>
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
</body>
</html>