<?php 
$page_title = "Africa United - Gestion des Commandes"; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'restaurateur')) {
    header("Location: accueil.php");
    exit();
}

$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);
$commandes_a_preparer = [];

foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        foreach ($user['fidelite']['historique_commandes'] as $commande) {
            $commande['client_nom'] = $user['informations']['prenom'] . ' ' . $user['informations']['nom'];
            $commandes_a_preparer[] = $commande;
        }
    }
}
$commandes_a_preparer = array_reverse($commandes_a_preparer);
?>

<main class="admin-page main-container-1000">
    <div class="page-header-centered">
        <h1 class="text-red">👨‍🍳 Commandes en Cuisine</h1>
        <p>Visualisez et gérez le flux de préparation (Affichage Phase 2).</p>
    </div>

    <div class="admin-container">
        <section class="menu-section">
            <h2 class="section-title title-border-red">En attente de préparation</h2>
            
            <?php if(empty($commandes_a_preparer)): ?>
                <div class="empty-state-box">
                    <p class="empty-message">Aucune commande pour le moment</p>
                </div>
            <?php else: ?>
                <div class="presentation-grid grid-1-col">
                    <?php foreach($commandes_a_preparer as $cmd): ?>
                        <div class="admin-order-box">
                            <div class="admin-order-header">
                                <strong><?php echo $cmd['client_nom']; ?></strong>
                                <span class="text-secondary">📅 <?php echo $cmd['date'] ?? ''; ?></span>
                            </div>
                            
                            <p class="margin-bottom-15"><strong>Articles :</strong><br> <?php echo implode(', ', $cmd['articles']); ?></p>
                            
                            <div class="admin-order-actions">
                                <div>
                                    <label class="font-bold-small">Statut :</label><br>
                                    <select class="input-select-small">
                                        <option>À préparer</option>
                                        <option>En cours</option>
                                        <option>Prêt pour livraison</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold-small">Livreur :</label><br>
                                    <select class="input-select-small">
                                        <option>Non attribué</option>
                                        <option>Lucas Dubois</option>
                                    </select>
                                </div>
                                <button class="btn-submit btn-update-small">Mettre à jour</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php include 'includes/footer.php'; ?>