<?php
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Récupérer la pétition avec le PLUS de signatures
    // Si égalité, prendre celle qui a atteint ce nombre EN DERNIER
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
        WHERE p.dateFinP > NOW()
        GROUP BY p.idP 
        HAVING signature_count = (
            SELECT MAX(signature_count) 
            FROM (
                SELECT COUNT(s2.idS) as signature_count
                FROM petition p2 
                LEFT JOIN signature s2 ON p2.idP = s2.idP 
                WHERE p2.dateFinP > NOW()
                GROUP BY p2.idP
            ) as counts
        )
        ORDER BY last_signature_datetime DESC 
        LIMIT 1
    ";
    
    $stmt = $pdo->query($query);
    $popularData = $stmt->fetch();

    if ($popularData) {
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
        // Si aucune pétition active, prendre la plus populaire toutes catégories avec même logique
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
        
        $stmt = $pdo->query($query);
        $popularData = $stmt->fetch();
        
        if ($popularData) {
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
            echo json_encode([
                'success' => false,
                'message' => 'Aucune pétition trouvée'
            ]);
        }
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}
?>