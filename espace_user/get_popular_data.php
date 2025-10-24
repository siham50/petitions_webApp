<?php
// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . '/../includes/database.php';

// Définition des en-têtes HTTP pour une API JSON
header('Content-Type: application/json');  // Indique que la réponse est au format JSON
header('Access-Control-Allow-Origin: *');  // Autorise les requêtes cross-origin (CORS)

try {
    // PREMIÈRE TENTATIVE : Récupérer la pétition active la plus populaire
    // Logique : pétition avec le PLUS de signatures parmi les pétitions ACTIVES
    // En cas d'égalité : prendre celle qui a atteint ce nombre EN DERNIER
    $query = "
        SELECT 
            p.idP as petition_id,                    -- ID de la pétition
            p.titreP,                                -- Titre de la pétition
            p.descriptionP,                          -- Description détaillée
            p.dateFinP,                              -- Date de fin de la pétition
            p.nomPorteurP,                           -- Nom du créateur de la pétition
            p.email,                                 -- Email du créateur
            p.dateAjoutP,                            -- Date de création de la pétition
            COUNT(s.idS) as signature_count,         -- Nombre total de signatures
            MAX(s.dateS) as last_signature_date,     -- Date de la dernière signature
            MAX(CONCAT(s.dateS, ' ', s.heureS)) as last_signature_datetime  -- Timestamp complet de la dernière signature
        FROM petition p 
        LEFT JOIN signature s ON p.idP = s.idP       -- Jointure pour compter les signatures
        WHERE p.dateFinP > NOW()                     -- Filtre : seulement les pétitions actives (date fin > maintenant)
        GROUP BY p.idP                               -- Regroupement par pétition pour les agrégations
        HAVING signature_count = (                   -- Filtre HAVING : seulement les pétitions avec le nombre MAX de signatures
            SELECT MAX(signature_count)              -- Sous-requête : trouve le nombre maximum de signatures
            FROM (
                SELECT COUNT(s2.idS) as signature_count  -- Compte les signatures pour chaque pétition
                FROM petition p2 
                LEFT JOIN signature s2 ON p2.idP = s2.idP 
                WHERE p2.dateFinP > NOW()            -- Seulement les pétitions actives
                GROUP BY p2.idP                      -- Groupe par pétition
            ) as counts                              -- Alias de la sous-requête
        )
        ORDER BY last_signature_datetime DESC        -- En cas d'égalité, tri par date de dernière signature (plus récente d'abord)
        LIMIT 1                                      -- On ne garde qu'une seule pétition (la plus populaire)
    ";
    
    // Exécution de la requête
    $stmt = $pdo->query($query);
    $popularData = $stmt->fetch();  // Récupération du premier résultat

    // Vérification si une pétition active populaire a été trouvée
    if ($popularData) {
        // SUCCÈS : Envoi des données au format JSON
        echo json_encode([
            'success' => true,                                   // Flag de succès
            'petition_id' => (int)$popularData['petition_id'],   // ID converti en entier
            'titreP' => htmlspecialchars($popularData['titreP']), // Titre sécurisé contre XSS
            'descriptionP' => htmlspecialchars($popularData['descriptionP']), // Description sécurisée
            'dateFinP' => $popularData['dateFinP'],              // Date de fin (non modifiée)
            'nomPorteurP' => htmlspecialchars($popularData['nomPorteurP']), // Nom sécurisé
            'email' => htmlspecialchars($popularData['email']),  // Email sécurisé
            'dateAjoutP' => $popularData['dateAjoutP'],          // Date d'ajout
            'signature_count' => (int)$popularData['signature_count'], // Nombre de signatures en entier
            'last_signature_date' => $popularData['last_signature_date'] // Date dernière signature
        ]);
    } else {
        // DEUXIÈME TENTATIVE : Si aucune pétition active trouvée, prendre la plus populaire TOUTES CATÉGORIES
        // Même logique mais sans le filtre de date (pétitions fermées incluses)
        $query = "
            SELECT 
                p.idP as petition_id,
                p.titreP,
                p.descriptionP,
                p.dateFinP,
                p.nomPorteurP,
                p.email,
                p.dateAjoutP,
                COUNT(s.idS) as signature_count,
                MAX(s.dateS) as last_signature_date,
                MAX(CONCAT(s.dateS, ' ', s.heureS)) as last_signature_datetime  
            FROM petition p 
            LEFT JOIN signature s ON p.idP = s.idP 
            GROUP BY p.idP 
            HAVING signature_count = (
                SELECT MAX(signature_count) 
                FROM (
                    SELECT COUNT(s2.idS) as signature_count
                    FROM petition p2 
                    LEFT JOIN signature s2 ON p2.idP = s2.idP 
                    GROUP BY p2.idP
                ) as counts
            )
            ORDER BY last_signature_datetime DESC 
            LIMIT 1
        ";
        
        // Exécution de la requête alternative
        $stmt = $pdo->query($query);  // Exécute la requête SQL contenue dans $query
        $popularData = $stmt->fetch();  // Récupère une seule ligne de résultats
        
        // Vérification si une pétition (active ou fermée) a été trouvée
        if ($popularData) {
            // SUCCÈS avec pétition toutes catégories
            echo json_encode([
                'success' => true,
                'petition_id' => (int)$popularData['petition_id'],
                'titreP' => htmlspecialchars($popularData['titreP']),
                'descriptionP' => htmlspecialchars($popularData['descriptionP']),
                'dateFinP' => $popularData['dateFinP'],
                'nomPorteurP' => htmlspecialchars($popularData['nomPorteurP']),
                'email' => htmlspecialchars($popularData['email']),
                'dateAjoutP' => $popularData['dateAjoutP'],
                'signature_count' => (int)$popularData['signature_count'],
                'last_signature_date' => $popularData['last_signature_date']
            ]);
        } else {
            // ÉCHEC : Aucune pétition trouvée dans la base de données
            echo json_encode([
                'success' => false,
                'message' => 'Aucune pétition trouvée'
            ]);
        }
    }
} catch (PDOException $e) {
    // GESTION DES ERREURS DE BASE DE DONNÉES
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()  // Message d'erreur technique
    ]);
}
?>