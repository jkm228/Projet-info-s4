<?php 
$page_title = "Africa United - Paiement"; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}
if (empty($_SESSION['panier'])) {
    header("Location: panier.php");
    exit();
}

// 1. On récupère les options du panier (Livraison/Emporter, Immédiat/Programmé)
// On les sauvegarde dans la session pour ne pas les perdre quand on clique sur "Payer"
if (isset($_POST['type_retrait'])) {
    $_SESSION['options_commande'] = [
        'type_retrait' => $_POST['type_retrait'],
        'moment' => $_POST['moment'],
        'date_prevue' => $_POST['date_prevue']
    ];
}

$json_data = file_get_contents('data/plats.json');
$tous_les_articles = json_decode($json_data, true);
$total_commande = 0;
$liste_noms_articles = [];

foreach ($_SESSION['panier'] as $id => $quantite) {
    foreach ($tous_les_articles as $item) {
        if ($item['id'] == $id) {
            $prix_ligne = $item['prix'] * $quantite;
            $total_commande += $prix_ligne;
            $liste_noms_articles[] = $item['nom'] . " (x" . $quantite . ")";
        }
    }
}

// --- CALCUL DE LA REMISE ---
$remise_pourcentage = 0;
$users_file_temp = 'data/utilisateurs.json';
$utilisateurs_temp = json_decode(file_get_contents($users_file_temp), true);

foreach ($utilisateurs_temp as $u) {
    if ($u['id'] == $_SESSION['user_id']) {
        $remise_pourcentage = $u['informations']['remise'] ?? 0;
        break;
    }
}

$montant_remise = $total_commande * ($remise_pourcentage / 100);
$total_a_payer = $total_commande - $montant_remise;
// ---------------------------

// SI LE CLIENT CLIQUE SUR LE BOUTON PAYER DE CETTE PAGE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_payer'])) {
    
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    $options = $_SESSION['options_commande'] ?? ['type_retrait' => 'Livraison', 'moment' => 'Immédiate', 'date_prevue' => ''];
    
    $heure_livraison = "Dès que possible";
    if ($options['moment'] == 'Programmée' && !empty($options['date_prevue'])) {
        $heure_livraison = date("d/m/Y à H:i", strtotime($options['date_prevue']));
    }

    $nouvelle_commande = [
        "date_passage" => date("d/m/Y à H:i"), 
        "date_prevue" => $heure_livraison, 
        "type" => $options['type_retrait'], 
        "statut" => "À préparer", 
        "total" => $total_a_payer, // La commande enregistre le prix avec remise
        "articles" => $liste_noms_articles
    ];

    foreach ($utilisateurs as &$user) {
        if ($user['id'] == $_SESSION['user_id']) {
            
            $user['fidelite']['historique_commandes'][] = $nouvelle_commande;
            
            if (!isset($user['fidelite']['points'])) {
                $user['fidelite']['points'] = 0;
            }
            
            // Les points sont calculés sur le prix final payé
            $points_gagnes = floor($total_a_payer); 
            $user['fidelite']['points'] += $points_gagnes;
            
            break;
        }
    }

    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $_SESSION['panier'] = [];
    unset($_SESSION['options_commande']);
    $paiement_reussi = true;
}
?>

<main style="padding: 40px 20px; max-width: 600px; margin: 0 auto; text-align: center; min-height: 60vh;">
    
    <?php if(isset($paiement_reussi)): ?>
        <div style="background: #e8f8f5; border: 2px solid #27ae60; padding: 40px; border-radius: 10px;">
            <h1 style="color: #27ae60; margin-bottom: 20px;">Paiement Réussi ! ✅</h1>
            <p style="font-size: 1.2em; color: #333;">Merci pour votre commande.</p>
            <p style="color: #666; margin-top: 10px;">Elle a bien été enregistrée selon vos préférences.</p>
            <p style="color: #f39c12; font-weight: bold; margin-top: 10px;">🎁 Vous venez de gagner <?php echo floor($total_a_payer); ?> points de fidélité !</p>
            <a href="profil.php" class="btn-submit" style="display: inline-block; margin-top: 30px; text-decoration: none;">Voir mon historique</a>
        </div>
    <?php else: ?>
        
        <h1>Paiement Sécurisé 🔒</h1>
        
        <?php $opts = $_SESSION['options_commande'] ?? null; if($opts): ?>
            <div style="background: #eaf2f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9em; text-align: left; color: #2980b9;">
                <strong>Récapitulatif :</strong> <?php echo htmlspecialchars($opts['type_retrait']); ?> 
                (<?php echo ($opts['moment'] == 'Programmée') ? 'Prévue le ' . date("d/m/Y à H:i", strtotime($opts['date_prevue'])) : 'Dès que possible'; ?>)
            </div>
        <?php endif; ?>

        <!-- Affichage dynamique de la remise -->
        <?php if($remise_pourcentage > 0): ?>
            <div style="background: #fff3cd; border-left: 4px solid #f1c40f; padding: 15px; text-align: left; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; font-weight: bold; color: #856404;">🎉 Avantage Fidélité : Vous bénéficiez d'une remise de <?php echo $remise_pourcentage; ?>% !</p>
            </div>
            <p style="margin-bottom: 30px; font-size: 1.2em;">
                Sous-total : <del style="color: #7f8c8d;"><?php echo number_format($total_commande, 2); ?> €</del><br>
                Montant total à régler : <strong style="font-size: 1.5em; color: #e74c3c;"><?php echo number_format($total_a_payer, 2); ?> €</strong>
            </p>
        <?php else: ?>
            <p style="margin-bottom: 30px;">Montant total à régler : <strong style="font-size: 1.5em; color: #e74c3c;"><?php echo number_format($total_a_payer, 2); ?> €</strong></p>
        <?php endif; ?>
        
        <div style="background: #f9f9f9; padding: 30px; border-radius: 10px; text-align: left; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
            <form action="paiement.php" method="POST">
                <input type="hidden" name="action_payer" value="1">

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Nom sur la carte :</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['user_prenom']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: #eee;">
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Numéro de carte bancaire :</label>
                    <input type="text" value="**** **** **** 1234" readonly style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: #eee;">
                </div>
                
                <button type="submit" class="btn-submit" style="width: 100%; font-size: 1.2em; padding: 15px;">Confirmer et Payer (<?php echo number_format($total_a_payer, 2); ?> €)</button>
            </form>
        </div>
    <?php endif; ?>
    
</main>

<?php include 'includes/footer.php'; ?>