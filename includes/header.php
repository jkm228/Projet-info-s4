<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $utilisateurs_check = json_decode(file_get_contents('data/utilisateurs.json'), true);
    if ($utilisateurs_check) { 
        foreach ($utilisateurs_check as $u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (isset($u['bloque']) && $u['bloque'] === true) {
                    session_destroy();
                    header("Location: connexion.php?erreur=bloque");
                    exit();
                }
                break;
            }
        }
    }
}

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'clair';
if ($theme !== 'clair' && $theme !== 'sombre') {
    $theme = 'clair'; 
}

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

    <div id="toast-notif" class="toast-notification">✅ Produit ajouté au panier !</div>

    <header>
        <div class="header-title">
            <a href="accueil.php" class="header-brand-link">AFRICA UNITED</a>
        </div>
        
        <div class="auth-buttons header-nav-flex">
            
            <button id="btn-theme" class="btn-theme-toggle" title="Changer de thème">🌓</button>

            <a href="accueil.php">Accueil</a>
            <a href="presentation.php">La Carte</a>
            
            <a href="panier.php" class="header-cart-badge">
                🛒 Panier (<span id="cart-count"><?php echo $total_articles; ?></span>)
            </a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'restaurateur'): ?>
                    <a href="admin.php" class="header-link-admin">Administration</a>
                <?php endif; ?>
                <?php if($_SESSION['user_role'] == 'livreur'): ?>
                    <a href="livraison.php" class="header-link-livreur">Espace Livreur</a>
                <?php endif; ?>
                <a href="profil.php" class="signup">Mon Profil</a>
                <a href="deconnexion.php" class="login btn-logout">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="login">Connexion</a>
                <a href="inscription.php" class="signup">S'inscrire</a>
            <?php endif; ?>
        </div>
    </header>

    <script>
    function ajouterAuPanier(idArticle) {
        fetch('api_panier.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_article: idArticle })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // 1. Mettre à jour le chiffre dans le panier en direct
                document.getElementById('cart-count').textContent = data.total_articles;
                
                // 2. Faire descendre la notification
                const toast = document.getElementById('toast-notif');
                toast.classList.add('show-toast');
                
                // 3. La faire remonter et disparaître après 3 secondes
                setTimeout(() => {
                    toast.classList.remove('show-toast');
                }, 3000);
            }
        });
    }
    </script>