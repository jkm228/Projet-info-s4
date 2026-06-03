<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
    header("Location: accueil.php"); exit();
}
$page_title = "Africa United - Espace Livreur";
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
        <div class="dark-app-brand">🌍 AFRICA UNITED <span class="badge-livreur-header">🛵 LIVREUR</span></div>
        <div class="dark-app-user">
            <span>Livreur : <strong><?php echo htmlspecialchars($_SESSION['user_prenom']); ?></strong></span>
            <a href="deconnexion.php" class="btn-dark-logout">Déconnexion</a>
        </div>
    </header>

    <main class="main-container-600">
        <h1 class="dark-app-title">📋 Tournée de Livraison</h1>
        <?php
        $utilisateurs = json_decode(file_get_contents('data/utilisateurs.json'), true);
        $livraisons_trouvees = false;

        foreach ($utilisateurs as $user) {
            foreach ($user['fidelite']['historique_commandes'] ?? [] as $idx => $cmd) {
                if (isset($cmd['livreur_id']) && $cmd['livreur_id'] === $_SESSION['user_id'] && $cmd['statut'] === 'En cours de livraison') {
                    $livraisons_trouvees = true;
                    ?>
                    <div class="dashboard-card livreur-card">
                        <div class="cuisine-card-header">
                            <span class="livreur-client-name">👤 <?php echo htmlspecialchars($user['informations']['prenom'] . ' ' . $user['informations']['nom']); ?></span>
                            <span class="livreur-client-date"><?php echo htmlspecialchars($cmd['date_prevue']); ?></span>
                        </div>
                        <div class="livreur-card-details">
                            <p class="margin-small"><strong>📍 Adresse de Livraison :</strong><br>
                                <span class="livreur-address"><?php echo htmlspecialchars($user['informations']['adresse_livraison'] ?? $user['informations']['adresse'] ?? 'Non renseignée'); ?></span>
                            </p>
                            <p class="margin-medium"><strong>📞 Téléphone Client :</strong> <br>
                                <a href="tel:<?php echo $user['informations']['telephone']; ?>" class="livreur-phone">📱 <?php echo htmlspecialchars($user['informations']['telephone']); ?></a>
                            </p>
                            <p class="margin-small"><strong>📦 Commande :</strong> <?php echo htmlspecialchars(implode(', ', $cmd['articles'])); ?></p>
                            <p class="margin-small"><strong>💵 Montant encaissé :</strong> <span class="livreur-price"><?php echo number_format($cmd['total'], 2); ?> €</span></p>
                        </div>
                        <div class="livreur-action-zone">
                            <button onclick="validerLivraison('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>)" class="btn-livreur-ready">✅ VALIDER LA LIVRAISON</button>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        if (!$livraisons_trouvees) {
            echo '<div class="dark-app-empty">🛵 Aucune course à effectuer pour le moment. Vous êtes à jour !</div>';
        }
        ?>
    </main>
    <script>
    function validerLivraison(emailClient, indexCmd) {
        if (confirm("Confirmez-vous avoir remis cette commande en main propre au client ?")) {
            fetch('api_commande.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email_client: emailClient, index_cmd: indexCmd, nouveau_statut: 'Livrée' })
            }).then(res => res.json()).then(data => { 
                if(data.success) { alert("Livraison validée !"); location.reload(); }
            });
        }
    }
    </script>
</body>
</html>