<?php 
$page_title = "Africa United - Espace Livreur"; 
include 'includes/header.php'; 

// 🔒 SÉCURITÉ : Uniquement accessible au livreur
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
    header("Location: accueil.php");
    exit();
}

$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

// Pour simuler la phase 2, on récupère la toute dernière commande enregistrée
$commande_attribuee = null;
$infos_client = null;

foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        $commande_attribuee = end($user['fidelite']['historique_commandes']); // "end" prend le dernier élément
        $infos_client = $user['informations'];
    }
}

// On prépare le lien GPS (On remplace les espaces par des '+' pour l'URL)
$adresse_encodee = "";
if ($infos_client) {
    $adresse_encodee = urlencode($infos_client['adresse_livraison'] ?? 'Paris');
}
?>

<main class="delivery-page" style="padding: 40px 20px; max-width: 600px; margin: 0 auto; min-height: 60vh;">
    
    <div class="delivery-header" style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3498db;">🛵 Ma Course Actuelle</h1>
    </div>

    <div class="delivery-container">
        <?php if(!$commande_attribuee): ?>
            <div style="background: #f9f9f9; padding: 30px; text-align: center; border-radius: 10px;">
                <p>Aucune livraison ne vous est attribuée pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="dashboard-card info-card" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-size: 0.9em; text-transform: uppercase;">Client :</label>
                    <div style="font-size: 1.2em; font-weight: bold;">
                        <?php echo $infos_client['prenom'] . ' ' . $infos_client['nom']; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-size: 0.9em; text-transform: uppercase;">Téléphone :</label>
                    <div style="font-size: 1.2em; font-weight: bold; color: #e74c3c;">
                        📞 <?php echo $infos_client['telephone'] ?? 'Non renseigné'; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-size: 0.9em; text-transform: uppercase;">Adresse de livraison :</label>
                    <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin-top: 5px;">
                        <?php echo $infos_client['adresse_livraison'] ?? 'Non renseignée'; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-size: 0.9em; text-transform: uppercase;">Détails de la commande :</label>
                    <div style="font-size: 0.9em; margin-top: 5px;">
                        <?php echo implode(', ', $commande_attribuee['articles']); ?>
                    </div>
                </div>

                <div class="form-group gps-container" style="margin-top: 30px; display: flex; flex-direction: column; gap: 15px;">
                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $adresse_encodee; ?>" target="_blank" class="btn-submit" style="background-color: #3498db; text-decoration: none; text-align: center; padding: 15px;">
                        📍 Ouvrir dans le GPS
                    </a>
                    
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">

                    <button class="btn-submit" style="background-color: #27ae60; text-align: center; padding: 15px; font-size: 1.1em;">
                        ✅ LIVRAISON TERMINÉE
                    </button>
                </div>
                
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>