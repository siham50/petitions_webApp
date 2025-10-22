<?php
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Récupérer la pétition active avec le plus de signatures
    $query = "
        SELECT 
            p.idP as petition_id,
            p.titreP,
            p.descriptionP,
            p.dateFinP,
            p.nomPorteurP,
            p.email,
            p.dateAjoutP,
            COUNT(s.idS) as signature_count
        FROM petition p 
        LEFT JOIN signature s ON p.idP = s.idP 
        WHERE p.dateFinP > NOW()
        GROUP BY p.idP 
        ORDER BY signature_count DESC 
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
            'signature_count' => (int)$popularData['signature_count']
        ]);
    } else {
        // Si aucune pétition active, prendre la plus populaire toutes catégories
        $query = "
            SELECT 
                p.idP as petition_id,
                p.titreP,
                p.descriptionP,
                p.dateFinP,
                p.nomPorteurP,
                p.email,
                p.dateAjoutP,
                COUNT(s.idS) as signature_count
            FROM petition p 
            LEFT JOIN signature s ON p.idP = s.idP 
            GROUP BY p.idP 
            ORDER BY signature_count DESC 
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
                'signature_count' => (int)$popularData['signature_count']
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