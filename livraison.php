<?php 
$page_title = "Africa United - Espace Livreur"; 
include 'includes/header.php'; 

// SÉCURITÉ : Seul un livreur peut accéder à cette page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
    header("Location: accueil.php");
    exit();
}

// On récupère toutes les commandes assignées à CE livreur
$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

$mes_livraisons = [];

foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        foreach ($user['fidelite']['historique_commandes'] as $idx => $cmd) {
            // On cherche les commandes "En cours de livraison" assignées à l'ID du livreur connecté
            if (isset($cmd['livreur_id']) && $cmd['livreur_id'] === $_SESSION['user_id'] && $cmd['statut'] === 'En cours de livraison') {
                $cmd['email_client'] = $user['informations']['email'];
                $cmd['nom_client'] = $user['informations']['prenom'] . ' ' . $user['informations']['nom'];
                // On essaie de récupérer l'adresse (adresse_livraison ou adresse tout court)
                $cmd['adresse_client'] = $user['informations']['adresse_livraison'] ?? $user['informations']['adresse'] ?? 'Adresse non précisée';
                $cmd['index_cmd'] = $idx;
                $mes_livraisons[] = $cmd;
            }
        }
    }
}
?>

<main style="padding: 40px 20px; max-width: 900px; margin: 0 auto; min-height: 60vh;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #3498db;">🛵 Mon Espace Livreur</h1>
        <p>Bonjour <strong><?php echo $_SESSION['user_prenom']; ?></strong>, voici les commandes que vous devez livrer aujourd'hui.</p>
    </div>

    <?php if (empty($mes_livraisons)): ?>
        <div class="dashboard-card" style="background: white; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <h3 style="color: #7f8c8d;">Aucune livraison en cours</h3>
            <p>Prenez un café, la cuisine prépare les prochaines commandes ! ☕</p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($mes_livraisons as $livraison): ?>
                <div class="dashboard-card" style="background: white; padding: 25px; border-radius: 10px; border-left: 5px solid #3498db; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h3 style="margin-top: 0; color: #2c3e50;">📍 Livraison pour <?php echo htmlspecialchars($livraison['nom_client']); ?></h3>
                            <p style="font-size: 1.1em; font-weight: bold; margin: 10px 0;">🗺️ <?php echo htmlspecialchars($livraison['adresse_client']); ?></p>
                            <p style="color: #7f8c8d; font-size: 0.9em;"><strong>Contenu :</strong> <?php echo implode(', ', $livraison['articles']); ?></p>
                            <p style="color: #e74c3c; font-weight: bold;">Montant total : <?php echo number_format($livraison['total'], 2); ?> €</p>
                        </div>
                        
                        <div style="text-align: right;">
                            <span style="display: inline-block; background: #f39c12; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold; margin-bottom: 15px;">En cours</span><br>
                            <button onclick="terminerLivraison('<?php echo $livraison['email_client']; ?>', <?php echo $livraison['index_cmd']; ?>)" class="btn-submit" style="background-color: #27ae60; padding: 12px 20px; font-size: 1.1em; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold;">
                                ✅ Livraison effectuée
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
// On utilise exactement la même logique asynchrone que pour le restaurateur
function terminerLivraison(email, index) {
    if(confirm("Confirmez-vous que cette commande a bien été remise au client ?")) {
        fetch('api_commande.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email_client: email,
                index_cmd: index,
                nouveau_statut: 'Livrée' // Le statut magique qui débloquera la notation !
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Rafraîchit la page pour faire disparaître la course
            } else {
                alert("Erreur lors de la validation.");
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>