<?php
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');

if (isset($_GET['petition_id'])) {
    $petitionId = $_GET['petition_id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ?");
    $stmt->execute([$petitionId]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'petition_id' => $petitionId,
        'signature_count' => $result['count']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de pétition manquant'
    ]);
}
?>