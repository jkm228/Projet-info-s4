<?php 
$page_title = "Africa United - Espace Livreur"; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
    header("Location: accueil.php"); exit();
}

$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);
$mes_livraisons = [];

foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        foreach ($user['fidelite']['historique_commandes'] as $idx => $cmd) {
            if (isset($cmd['livreur_id']) && $cmd['livreur_id'] === $_SESSION['user_id'] && $cmd['statut'] === 'En cours de livraison') {
                $cmd['email_client'] = $user['informations']['email'];
                $cmd['nom_client'] = $user['informations']['prenom'] . ' ' . $user['informations']['nom'];
                $cmd['adresse_client'] = $user['informations']['adresse_livraison'] ?? $user['informations']['adresse'] ?? 'Adresse non précisée';
                $cmd['index_cmd'] = $idx;
                $mes_livraisons[] = $cmd;
            }
        }
    }
}
?>

<main class="main-container-900">
    <div class="page-header-centered">
        <h1 class="text-blue">🛵 Mon Espace Livreur</h1>
        <p>Bonjour <strong><?php echo $_SESSION['user_prenom']; ?></strong>, voici les commandes que vous devez livrer aujourd'hui.</p>
    </div>

    <?php if (empty($mes_livraisons)): ?>
        <div class="dashboard-card empty-livraison-box">
            <h3 class="text-secondary">Aucune livraison en cours</h3>
            <p>Prenez un café, la cuisine prépare les prochaines commandes ! ☕</p>
        </div>
    <?php else: ?>
        <div class="grid-1-col">
            <?php foreach ($mes_livraisons as $livraison): ?>
                <div class="dashboard-card livraison-card">
                    <div class="livraison-flex-row">
                        <div>
                            <h3 class="livraison-client-title">📍 Livraison pour <?php echo htmlspecialchars($livraison['nom_client']); ?></h3>
                            <p class="livraison-address">🗺️ <?php echo htmlspecialchars($livraison['adresse_client']); ?></p>
                            <p class="livraison-content"><strong>Contenu :</strong> <?php echo implode(', ', $livraison['articles']); ?></p>
                            <p class="livraison-price">Montant total : <?php echo number_format($livraison['total'], 2); ?> €</p>
                        </div>
                        <div class="text-right">
                            <span class="badge-livraison-encours">En cours</span><br>
                            <button onclick="terminerLivraison('<?php echo $livraison['email_client']; ?>', <?php echo $livraison['index_cmd']; ?>)" class="btn-submit btn-livraison-done">
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
function terminerLivraison(email, index) {
    if(confirm("Confirmez-vous que cette commande a bien été remise au client ?")) {
        fetch('api_commande.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email_client: email, index_cmd: index, nouveau_statut: 'Livrée' })
        }).then(res => res.json()).then(data => { if(data.success) { location.reload(); } });
    }
}
</script>
<?php include 'includes/footer.php'; ?>