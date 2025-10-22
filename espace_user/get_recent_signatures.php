<?php
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');

if (isset($_GET['petition_id'])) {
    $petitionId = $_GET['petition_id'];
    
    try {
        // Récupérer les 5 dernières signatures
        $stmt = $pdo->prepare("
            SELECT s.prenomS, s.nomS, s.paysS, s.dateS, s.heureS 
            FROM signature s 
            WHERE s.idP = ? 
            ORDER BY s.dateS DESC, s.heureS DESC 
            LIMIT 5
        ");
        $stmt->execute([$petitionId]);
        $signatures = $stmt->fetchAll();
        
        // Formater les données pour l'affichage
        $formattedSignatures = [];
        foreach ($signatures as $signature) {
            $formattedSignatures[] = [
                'prenom' => $signature['prenomS'],
                'nom' => $signature['nomS'],
                'pays' => $signature['paysS'],
                'date' => $signature['dateS'],
                'heure' => $signature['heureS'],
                'initials' => strtoupper(substr($signature['prenomS'], 0, 1) . substr($signature['nomS'], 0, 1)),
                'display_date' => date('d/m/Y', strtotime($signature['dateS'])),
                'display_time' => date('H:i', strtotime($signature['heureS']))
            ];
        }
        
        echo json_encode([
            'success' => true,
            'signatures' => $formattedSignatures,
            'count' => count($formattedSignatures),
            'timestamp' => date('d/m/Y H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la récupération des signatures',
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de pétition manquant'
    ]);
}
?>