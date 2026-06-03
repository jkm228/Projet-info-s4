<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'restaurateur') {
    header("Location: accueil.php"); exit();
}
$page_title = "Africa United - En Cuisine";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dark-app-body">
    <header class="dark-app-header">
        <div class="dark-app-brand">🌍 AFRICA UNITED <span class="badge-cuisine-header">👨‍🍳 CUISINE</span></div>
        <div class="dark-app-user">
            <span>Chef : <strong><?php echo htmlspecialchars($_SESSION['user_prenom']); ?></strong></span>
            <a href="deconnexion.php" class="btn-dark-logout">Déconnexion</a>
        </div>
    </header>

    <main class="main-container-800">
        <h1 class="dark-app-title">🔥 Bons de Commande</h1>
        <?php
        $utilisateurs = json_decode(file_get_contents('data/utilisateurs.json'), true);
        $commandes_trouvees = false;

        foreach ($utilisateurs as $user) {
            foreach ($user['fidelite']['historique_commandes'] ?? [] as $idx => $cmd) {
                if ($cmd['statut'] === 'En préparation') {
                    $commandes_trouvees = true;
                    $est_emporter = (strpos(strtolower($cmd['type']), 'emporter') !== false);
                    $type_badge = $est_emporter ? "<span class='badge-cuisine-emporter'>🛍️ À Emporter</span>" : "<span class='badge-cuisine-livraison'>🛵 Livraison</span>";
                    ?>
                    <div class="cuisine-card">
                        <div class="cuisine-card-header">
                            <span class="cuisine-client-name">Client : <?php echo htmlspecialchars($user['informations']['prenom'] . ' ' . $user['informations']['nom']); ?></span>
                            <span class="cuisine-client-date"><?php echo $type_badge; ?> - <?php echo htmlspecialchars($cmd['date_prevue']); ?></span>
                        </div>
                        <div class="cuisine-card-details">
                            <strong>🍽️ Plats à préparer :</strong><br>
                            <span class="cuisine-dishes"><?php echo htmlspecialchars(implode(', ', $cmd['articles'])); ?></span>
                        </div>
                        <button onclick="validerFinPreparation('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>)" class="btn-cuisine-ready">✅ COMMANDE PRÊTE</button>
                    </div>
                    <?php
                }
            }
        }
        if (!$commandes_trouvees) {
            echo '<div class="dark-app-empty">Aucune commande en attente. Reposez-vous chef ! ☕</div>';
        }
        ?>
    </main>
    <script>
    function validerFinPreparation(emailClient, indexCmd) {
        fetch('api_commande.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email_client: emailClient, index_cmd: indexCmd, nouveau_statut: 'Prête' })
        }).then(res => res.json()).then(data => { if(data.success) { location.reload(); } });
    }
    </script>
</body>
</html>