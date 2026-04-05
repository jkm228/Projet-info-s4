<?php 
$page_title = "Africa United - Gestion des Commandes"; 
include 'includes/header.php'; 

// 🔒 SÉCURITÉ : Uniquement accessible aux restaurateurs et admins
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'restaurateur')) {
    header("Location: accueil.php");
    exit();
}

// Récupération des données
$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

$commandes_a_preparer = [];

// On rassemble les commandes
foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        foreach ($user['fidelite']['historique_commandes'] as $commande) {
            $commande['client_nom'] = $user['informations']['prenom'] . ' ' . $user['informations']['nom'];
            $commandes_a_preparer[] = $commande;
        }
    }
}
// On inverse pour avoir la plus récente en haut
$commandes_a_preparer = array_reverse($commandes_a_preparer);
?>

<main class="admin-page" style="padding: 40px 20px; max-width: 1000px; margin: 0 auto; min-height: 60vh;">
    
    <div class="admin-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #e74c3c;">👨‍🍳 Commandes en Cuisine</h1>
        <p>Visualisez et gérez le flux de préparation (Affichage Phase 2).</p>
    </div>

    <div class="admin-container">
        
        <section class="menu-section">
            <h2 class="section-title" style="border-bottom: 2px solid #e74c3c; padding-bottom: 10px;">En attente de préparation</h2>
            
            <?php if(empty($commandes_a_preparer)): ?>
                <div style="background: #f9f9f9; padding: 30px; text-align: center; border-radius: 10px;">
                    <p class="empty-message">Aucune commande pour le moment</p>
                </div>
            <?php else: ?>
                <div class="presentation-grid" style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">
                    
                    <?php foreach($commandes_a_preparer as $cmd): ?>
                        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                                <strong><?php echo $cmd['client_nom']; ?></strong>
                                <span style="color: #7f8c8d;">📅 <?php echo $cmd['date']; ?></span>
                            </div>
                            
                            <p style="margin-bottom: 15px;"><strong>Articles :</strong><br> <?php echo implode(', ', $cmd['articles']); ?></p>
                            
                            <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; display: flex; gap: 15px; align-items: center;">
                                <div>
                                    <label style="font-size: 0.9em; font-weight: bold;">Statut :</label><br>
                                    <select style="padding: 5px;">
                                        <option>À préparer</option>
                                        <option>En cours</option>
                                        <option>Prêt pour livraison</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.9em; font-weight: bold;">Livreur :</label><br>
                                    <select style="padding: 5px;">
                                        <option>Non attribué</option>
                                        <option>Lucas Dubois</option>
                                    </select>
                                </div>
                                <button class="btn-submit" style="padding: 8px 15px; width: auto; margin-top: 15px;">Mettre à jour</button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php include 'includes/footer.php'; ?>