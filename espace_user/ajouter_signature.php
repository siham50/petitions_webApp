<?php
// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . '/../includes/database.php';

// Vérification que la requête est bien de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire
    $idP = $_POST['idP'] ?? '';       // ID de la pétition
    $nom = $_POST['nom'] ?? '';       // Nom du signataire
    $prenom = $_POST['prenom'] ?? ''; // Prénom du signataire
    $email = $_POST['email'] ?? '';   // Email du signataire
    $pays = $_POST['pays'] ?? '';     // Pays du signataire (optionnel)

    // VALIDATION DES CHAMPS OBLIGATOIRES
    if (empty($idP) || empty($nom) || empty($prenom) || empty($email)) {
        // Redirection avec message d'erreur si des champs obligatoires sont vides
        header('Location: signature.php?id=' . $idP . '&error=' . urlencode('Veuillez remplir tous les champs obligatoires.'));
        exit;
    }

    // VÉRIFICATION DE L'EXISTENCE ET DU STATUT DE LA PÉTITION
    $stmt = $pdo->prepare("SELECT * FROM petition WHERE idP = ?");
    $stmt->execute([$idP]);
    $petition = $stmt->fetch();

    // Vérifier si la pétition existe dans la base de données
    if (!$petition) {
        header('Location: liste_petitions.php?error=' . urlencode('Pétition non trouvée.'));
        exit;
    }

    // Vérifier si la pétition est encore active (date de fin non dépassée)
    $isActive = strtotime($petition['dateFinP']) > time();
    if (!$isActive) {
        header('Location: liste_petitions.php?error=' . urlencode('Cette pétition n\'est plus active.'));
        exit;
    }

    // TRAITEMENT DE LA SIGNATURE
    try {
        // VÉRIFICATION DE LA DOUBLE SIGNATURE
        // Empêcher qu'une même personne signe plusieurs fois avec le même email
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM signature WHERE idP = ? AND emailS = ?");
        $stmt->execute([$idP, $email]);
        $alreadySigned = $stmt->fetch()['count'];

        // Si l'email a déjà signé cette pétition, bloquer l'ajout
        if ($alreadySigned > 0) {
            header('Location: signature.php?id=' . $idP . '&error=' . urlencode('Vous avez déjà signé cette pétition avec cet email.'));
            exit;
        }

        // INSERTION DE LA SIGNATURE DANS LA BASE DE DONNÉES
        $stmt = $pdo->prepare("INSERT INTO signature (idP, nomS, prenomS, paysS, dateS, heureS, emailS) VALUES (?, ?, ?, ?, CURDATE(), CURTIME(), ?)");
        $stmt->execute([$idP, $nom, $prenom, $pays, $email]);
        
        // SUCCÈS - Redirection vers la liste des pétitions avec message de confirmation
        header('Location: liste_petitions.php?success=' . urlencode('Votre signature a été ajoutée avec succès !'));
        exit;

    } catch (PDOException $e) {
        // GESTION DES ERREURS DE BASE DE DONNÉES
        // En cas d'erreur technique, rediriger avec un message d'erreur générique
        header('Location: signature.php?id=' . $idP . '&error=' . urlencode('Une erreur est survenue lors de l\'enregistrement de votre signature.'));
        exit;
    }
} else {
    // PROTECTION CONTRE LES ACCÈS DIRECTS
    // Si la méthode n'est pas POST, rediriger vers la liste des pétitions
    header('Location: liste_petitions.php');
    exit;
}
?>