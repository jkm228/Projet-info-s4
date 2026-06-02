<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🔒 SÉCURITÉ : Seul le livreur peut accéder à cette page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
    header("Location: accueil.php");
    exit();
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
<body style="background: #1e272e; color: white; font-family: sans-serif; margin: 0; padding: 0;">

    <header style="background: #2c3e50; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
        <div style="font-size: 1.4em; font-weight: bold; color: #e74c3c; letter-spacing: 1px;">
            🌍 AFRICA UNITED <span style="font-size: 0.65em; color: #f1c40f; vertical-align: middle; margin-left: 5px;">🛵 LIVREUR</span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px; font-size: 0.95em;">
            <span>Livreur : <strong><?php echo htmlspecialchars($_SESSION['user_prenom']); ?></strong></span>
            <a href="deconnexion.php" style="background: #e74c3c; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 0.9em;">Déconnexion</a>
        </div>
    </header>

    <main style="padding: 30px 15px; max-width: 600px; margin: 0 auto;">
        <h1 style="text-align: center; margin-bottom: 30px; color: #f1c40f; font-size: 1.8em;">📋 Tournée de Livraison</h1>

        <?php
        $utilisateurs = json_decode(file_get_contents('data/utilisateurs.json'), true);
        $livraisons_trouvees = false;

        // On parcourt la bdd pour extraire les commandes assignées à ce livreur
        foreach ($utilisateurs as $user) {
            foreach ($user['fidelite']['historique_commandes'] ?? [] as $idx => $cmd) {
                // On affiche uniquement les commandes 'En cours de livraison' qui lui appartiennent
                if (isset($cmd['livreur_id']) && $cmd['livreur_id'] === $_SESSION['user_id'] && $cmd['statut'] === 'En cours de livraison') {
                    $livraisons_trouvees = true;
                    ?>
                    <div class="dashboard-card" style="background: #2c3e50; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-left: 5px solid #f1c40f; text-align: left;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #34495e; padding-bottom: 10px; margin-bottom: 15px;">
                            <span style="font-size: 1.1em; font-weight: bold; color: #f1c40f;">👤 <?php echo htmlspecialchars($user['informations']['prenom'] . ' ' . $user['informations']['nom']); ?></span>
                            <span style="font-size: 0.85em; color: #bdc3c7; background: #34495e; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($cmd['date_prevue']); ?></span>
                        </div>

                        <div style="font-size: 0.95em; line-height: 1.6; color: #ecf0f1;">
                            <p style="margin: 5px 0;">
                                <strong>📍 Adresse de Livraison :</strong><br>
                                <span style="font-size: 1.15em; color: #2ecc71; font-weight: bold; display: block; margin-top: 3px;">
                                    <?php echo htmlspecialchars($user['informations']['adresse_livraison'] ?? $user['informations']['adresse'] ?? 'Non renseignée'); ?>
                                </span>
                            </p>
                            
                            <p style="margin: 12px 0 8px 0;">
                                <strong>📞 Téléphone Client :</strong> <br>
                                <a href="tel:<?php echo $user['informations']['telephone']; ?>" style="color: #3498db; text-decoration: none; font-weight: bold; font-size: 1.1em;">
                                    📱 <?php echo htmlspecialchars($user['informations']['telephone']); ?>
                                </a>
                            </p>
                            
                            <p style="margin: 8px 0;"><strong>📦 Commande :</strong> <?php echo htmlspecialchars(implode(', ', $cmd['articles'])); ?></p>
                            <p style="margin: 8px 0;"><strong>💵 Montant encaissé :</strong> <span style="font-weight: bold; color: #e74c3c; font-size: 1.1em;"><?php echo number_format($cmd['total'], 2); ?> €</span></p>
                        </div>

                        <div style="margin-top: 20px;">
                            <button onclick="validerLivraison('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>)" style="background: #2ecc71; color: white; border: none; padding: 14px; font-size: 1.05em; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: background 0.2s;">
                                ✅ VALIDER LA LIVRAISON
                            </button>
                        </div>
                    </div>
                    <?php
                }
            }
        }

        if (!$livraisons_trouvees) {
            echo '<div style="text-align: center; padding: 40px; background: #2c3e50; border-radius: 10px; color: #bdc3c7; font-size: 1.1em; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">🛵 Aucune course à effectuer pour le moment. Vous êtes à jour !</div>';
        }
        ?>
    </main>

    <script>
    function validerLivraison(emailClient, indexCmd) {
        if (confirm("Confirmez-vous avoir remis cette commande en main propre au client ?")) {
            fetch('api_commande.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    email_client: emailClient, 
                    index_cmd: indexCmd, 
                    nouveau_statut: 'Livrée' 
                })
            })
            .then(res => res.json())
            .then(data => { 
                if(data.success) {
                    alert("Livraison validée avec succès !");
                    location.reload(); 
                } else {
                    alert("Erreur lors du traitement.");
                }
            });
        }
    }
    </script>
</body>
</html>