<?php
require_once 'database.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$response = [
    'success' => true,
    'has_new_petition' => false,
    'petition' => null,
    'message' => ''
];

try {
    $signalFile = __DIR__ . '/new_petition_signal.json';
    
    if (file_exists($signalFile)) {
        $signalData = json_decode(file_get_contents($signalFile), true);
        
        // Vérifier si le signal est récent (moins de 30 secondes)
        if ($signalData && isset($signalData['timestamp']) && (time() - $signalData['timestamp']) < 30) {
            
            // Récupérer les détails complets de la pétition
            $stmt = $pdo->prepare("SELECT * FROM petition WHERE idP = ?");
            $stmt->execute([$signalData['petition_id']]);
            $petition = $stmt->fetch();
            
            if ($petition) {
                $response['has_new_petition'] = true;
                $response['petition'] = [
                    'idP' => $petition['idP'],
                    'titreP' => $petition['titreP'],
                    'descriptionP' => $petition['descriptionP'],
                    'dateAjoutP' => $petition['dateAjoutP'],
                    'dateFinP' => $petition['dateFinP'],
                    'nomPorteurP' => $petition['nomPorteurP'],
                    'email' => $petition['email']
                ];
                $response['message'] = "Nouvelle pétition ajoutée par " . $signalData['admin'];
                
                //SUPPRIMER le fichier après lecture
                unlink($signalFile);
                
            } else {
                // Pétition non trouvée, supprimer le signal
                unlink($signalFile);
            }
        } else {
            // Signal trop ancien ou invalide, le supprimer
            unlink($signalFile);
        }
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Erreur: " . $e->getMessage();
}

echo json_encode($response);
?>