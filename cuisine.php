<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 🔒 SÉCURITÉ : Seul le restaurateur (cuisine) peut accéder ici
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'restaurateur') {
    header("Location: accueil.php");
    exit();
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
<body style="background: #1e272e; color: white; font-family: sans-serif; margin: 0; padding: 0;">

    <header style="background: #2c3e50; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
        <div style="font-size: 1.4em; font-weight: bold; color: #e74c3c; letter-spacing: 1px;">
            🌍 AFRICA UNITED <span style="font-size: 0.65em; color: #f39c12; vertical-align: middle; margin-left: 5px;">👨‍🍳 CUISINE</span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px; font-size: 0.95em;">
            <span>Chef : <strong><?php echo htmlspecialchars($_SESSION['user_prenom']); ?></strong></span>
            <a href="deconnexion.php" style="background: #e74c3c; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-weight: bold;">Déconnexion</a>
        </div>
    </header>

    <main style="padding: 30px 15px; max-width: 800px; margin: 0 auto;">
        <h1 style="text-align: center; margin-bottom: 30px; color: #f1c40f;">🔥 Bons de Commande</h1>

        <?php
        $utilisateurs = json_decode(file_get_contents('data/utilisateurs.json'), true);
        $commandes_trouvees = false;

        foreach ($utilisateurs as $user) {
            foreach ($user['fidelite']['historique_commandes'] ?? [] as $idx => $cmd) {
                if ($cmd['statut'] === 'En préparation') {
                    $commandes_trouvees = true;
                    // On détermine la couleur du type de retrait pour aider la cuisine
                    $est_emporter = (strpos(strtolower($cmd['type']), 'emporter') !== false);
                    $type_badge = $est_emporter ? "<span style='background:#9b59b6; padding:3px 8px; border-radius:4px;'>🛍️ À Emporter</span>" : "<span style='background:#3498db; padding:3px 8px; border-radius:4px;'>🛵 Livraison</span>";
                    ?>
                    <div style="background: #34495e; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-left: 5px solid #e74c3c;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2c3e50; padding-bottom: 10px; margin-bottom: 15px;">
                            <span style="font-size: 1.2em; font-weight: bold; color: white;">Client : <?php echo htmlspecialchars($user['informations']['prenom'] . ' ' . $user['informations']['nom']); ?></span>
                            <span style="font-size: 0.9em;"><?php echo $type_badge; ?> - <?php echo htmlspecialchars($cmd['date_prevue']); ?></span>
                        </div>
                        <div style="font-size: 1.1em; line-height: 1.6; color: #ecf0f1; margin-bottom: 20px;">
                            <strong>🍽️ Plats à préparer :</strong><br>
                            <span style="color: #f1c40f; font-size: 1.1em;">
                                <?php echo htmlspecialchars(implode(', ', $cmd['articles'])); ?>
                            </span>
                        </div>
                        <button onclick="validerFinPreparation('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>)" style="background: #27ae60; color: white; border: none; padding: 14px; font-size: 1.1em; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%;">
                            ✅ COMMANDE PRÊTE
                        </button>
                    </div>
                    <?php
                }
            }
        }

        if (!$commandes_trouvees) {
            echo '<div style="text-align: center; padding: 40px; background: #2c3e50; border-radius: 10px; color: #bdc3c7; font-size: 1.2em;">Aucune commande en attente. Reposez-vous chef ! ☕</div>';
        }
        ?>
    </main>

    <script>
    function validerFinPreparation(emailClient, indexCmd) {
        fetch('api_commande.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email_client: emailClient, index_cmd: indexCmd, nouveau_statut: 'Prête' })
        })
        .then(res => res.json())
        .then(data => { 
            if(data.success) { location.reload(); }
        });
    }
    </script>
</body>
</html>