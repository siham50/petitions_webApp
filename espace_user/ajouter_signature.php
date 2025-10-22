<?php
require_once __DIR__ . '/../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idP = $_POST['idP'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $pays = $_POST['pays'] ?? '';

    // Validation des données
    if (empty($idP) || empty($nom) || empty($prenom) || empty($email)) {
        header('Location: signature.php?id=' . $idP . '&error=' . urlencode('Veuillez remplir tous les champs obligatoires.'));
        exit;
    }

    // Vérifier que la pétition existe et est active
    $stmt = $pdo->prepare("SELECT * FROM petition WHERE idP = ?");
    $stmt->execute([$idP]);
    $petition = $stmt->fetch();

    if (!$petition) {
        header('Location: liste_petitions.php?error=' . urlencode('Pétition non trouvée.'));
        exit;
    }

    $isActive = strtotime($petition['dateFinP']) > time();
    if (!$isActive) {
        header('Location: liste_petitions.php?error=' . urlencode('Cette pétition n\'est plus active.'));
        exit;
    }

    try {
        // Vérifier si l'email n'a pas déjà signé cette pétition
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ? AND emailS = ?");
        $stmt->execute([$idP, $email]);
        $alreadySigned = $stmt->fetch()['count'];

        if ($alreadySigned > 0) {
            header('Location: signature.php?id=' . $idP . '&error=' . urlencode('Vous avez déjà signé cette pétition avec cet email.'));
            exit;
        }

        // Insérer la signature
        $stmt = $pdo->prepare("INSERT INTO signature (idP, nomS, prenomS, paysS, dateS, heureS, emailS) VALUES (?, ?, ?, ?, CURDATE(), CURTIME(), ?)");
        $stmt->execute([$idP, $nom, $prenom, $pays, $email]);
        
        // Rediriger vers la liste des pétitions avec un message de succès
        header('Location: liste_petitions.php?success=' . urlencode('Votre signature a été ajoutée avec succès !'));
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur, rediriger vers la page de signature avec un message d'erreur
        header('Location: signature.php?id=' . $idP . '&error=' . urlencode('Une erreur est survenue lors de l\'enregistrement de votre signature.'));
        exit;
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers la liste des pétitions
    header('Location: liste_petitions.php');
    exit;
}
?>