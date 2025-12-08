<?php
// Démarrage de la session PHP pour gérer l'état de connexion
session_start();

/**
 * Vérifie si l'administrateur est actuellement connecté
 * 
 * Cette fonction contrôle l'existence et la valeur du flag de connexion
 * dans la session pour déterminer l'état d'authentification
 * 
 * return bool True si l'admin est connecté, false sinon
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Exige une authentification administrateur valide
 * 
 * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié.
 * Cette fonction est utilisée comme garde pour les pages protégées.
 * 
 * return void Redirige vers login.php si non authentifié
 */
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        // Redirection immédiate vers la page de connexion
        header('Location: login.php');
        exit; // Arrêt immédiat du script pour éviter toute exécution supplémentaire
    }
}

/**
 * Récupère les informations de l'administrateur connecté
 * 
 * Retourne un tableau contenant l'ID et le nom d'utilisateur de l'admin
 * connecté, ou null si aucun admin n'est connecté
 * 
 * return array|null Tableau avec 'id' et 'username' ou null si non connecté
 */
function getAdminInfo() {
    if (isAdminLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'] ?? null,           // ID unique de l'admin
            'username' => $_SESSION['admin_username'] ?? null // Nom d'utilisateur
        ];
    }
    return null; // Aucune information si non connecté
}

/**
 * Déconnecte l'administrateur et nettoie la session
 * 
 * Cette fonction effectue un nettoyage complet de la session :
 * - Vide le tableau de session
 * - Supprime le cookie de session
 * - Détruit la session côté serveur
 * - Redirige vers la page publique
 * 
 * return void Redirige vers la liste des pétitions après déconnexion
 */
function adminLogout() {
    // VIDAGE COMPLET DU TABLEAU DE SESSION
    // Réinitialise toutes les variables de session
    $_SESSION = array();
    
    // SUPPRESSION DU COOKIE DE SESSION COTÉ CLIENT
    // Cette partie gère la suppression technique du cookie
    if (ini_get("session.use_cookies")) {
        // Récupération des paramètres du cookie de session
        $params = session_get_cookie_params();
        
        // Création d'un cookie expiré (date dans le passé) pour forcer la suppression
        setcookie(session_name(), '', time() - 42000,
            $params["path"],      // Chemin d'accès du cookie
            $params["domain"],    // Domaine du cookie
            $params["secure"],    // Flag HTTPS
            $params["httponly"]   // Flag HTTP Only (protection XSS)
        );
    }
    
    // DESTRUCTION DE LA SESSION COTÉ SERVEUR
    // Supprime les données de session du serveur
    session_destroy();
    
    // REDIRECTION VERS LA PAGE PUBLIQUE
    // Après déconnexion, on renvoie l'utilisateur vers l'espace public
    header('Location: ../liste_petitions.php');
    exit; // Arrêt du script après redirection
}
?>