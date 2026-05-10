<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------------------
// 🛡️ SÉCURITÉ MAJEURE : Vérification du blocage en temps réel
// ----------------------------------------------------
if (isset($_SESSION['user_id'])) {
    $utilisateurs_check = json_decode(file_get_contents('data/utilisateurs.json'), true);
    if ($utilisateurs_check) { // Sécurité si le fichier est en cours de lecture
        foreach ($utilisateurs_check as $u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (isset($u['bloque']) && $u['bloque'] === true) {
                    // L'utilisateur est bloqué ! On détruit sa session de force.
                    session_destroy();
                    // On le renvoie vers la page de connexion avec un message (tu pourras afficher ce message sur connexion.php si tu veux)
                    header("Location: connexion.php?erreur=bloque");
                    exit();
                }
                break;
            }
        }
    }
}

// ----------------------------------------------------
// NOUVEAU : VÉRIFICATION DU COOKIE DE THÈME (Phase 3)
// ----------------------------------------------------
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'clair';

// Sécurité : si la valeur est incohérente, on force le mode clair
if ($theme !== 'clair' && $theme !== 'sombre') {
    $theme = 'clair'; 
}

// Calcul du panier
$total_articles = 0;
if(isset($_SESSION['panier'])) {
    $total_articles = array_sum($_SESSION['panier']); 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "Africa United"; ?></title>
    
    <link rel="stylesheet" href="assets/style.css"> 

    <?php if ($theme === 'sombre'): ?>
        <link id="theme-style" rel="stylesheet" href="assets/dark-mode.css">
    <?php endif; ?>
</head>
<body>

    <header>
        <div class="header-title">
            <a href="accueil.php" style="color: white; text-decoration: none;">AFRICA UNITED</a>
        </div>
        
        <div class="auth-buttons" style="display: flex; align-items: center;">
            
            <button id="btn-theme" style="background: none; border: none; cursor: pointer; font-size: 1.5em; margin-right: 15px; text-decoration: none;" title="Changer de thème">🌓</button>

            <a href="accueil.php">Accueil</a>
            <a href="presentation.php">La Carte</a>
            
            <a href="panier.php" style="background-color: #e74c3c; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 15px;">
                🛒 Panier (<?php echo $total_articles; ?>)
            </a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'restaurateur'): ?>
                    <a href="admin.php" style="color: #f1c40f; font-weight: bold;">Administration</a>
                <?php endif; ?>
                <?php if($_SESSION['user_role'] == 'livreur'): ?>
                    <a href="livraison.php" style="color: #3498db; font-weight: bold;">Espace Livreur</a>
                <?php endif; ?>
                <a href="profil.php" class="signup">Mon Profil</a>
                <a href="deconnexion.php" class="login btn-logout">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="login">Connexion</a>
                <a href="inscription.php" class="signup">S'inscrire</a>
            <?php endif; ?>
        </div>
    </header>