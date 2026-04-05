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

// SI LE CLIENT CLIQUE SUR LE BOUTON PAYER DE CETTE PAGE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_payer'])) {
    
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    // On récupère les options sauvegardées
    $options = $_SESSION['options_commande'] ?? ['type_retrait' => 'Livraison', 'moment' => 'Immédiate', 'date_prevue' => ''];
    
    // On formate la date prévue (si c'est pour plus tard, on met la date choisie, sinon "Dès que possible")
    $heure_livraison = "Dès que possible";
    if ($options['moment'] == 'Programmée' && !empty($options['date_prevue'])) {
        $heure_livraison = date("d/m/Y à H:i", strtotime($options['date_prevue']));
    }

    // 2. LA COMMANDE EST BEAUCOUP PLUS COMPLÈTE MAINTENANT !
    $nouvelle_commande = [
        "date_passage" => date("d/m/Y à H:i"), // L'heure à laquelle il a cliqué
        "date_prevue" => $heure_livraison, // L'heure à laquelle il VEUT sa commande
        "type" => $options['type_retrait'], // Livraison ou À emporter
        "statut" => "À préparer", // Utile pour la suite (Phase 3)
        "total" => $total_commande,
        "articles" => $liste_noms_articles
    ];

    foreach ($utilisateurs as &$user) {
        if ($user['id'] == $_SESSION['user_id']) {
            $user['fidelite']['historique_commandes'][] = $nouvelle_commande;
            $user['fidelite']['points'] += floor($total_commande / 10);
            break;
        }
    }

    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // On vide le panier et les options
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
            <a href="profil.php" class="btn-submit" style="display: inline-block; margin-top: 30px; text-decoration: none;">Voir mon historique</a>
        </div>
    <?php else: ?>
        
        <h1>Paiement Sécurisé 🔒</h1>
        
        <?php $opts = $_SESSION['options_commande'] ?? null; if($opts): ?>
            <div style="background: #eaf2f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9em; text-align: left; color: #2980b9;">
                <strong>Récapitulatif :</strong> <?php echo $opts['type_retrait']; ?> 
                (<?php echo ($opts['moment'] == 'Programmée') ? 'Prévue le ' . date("d/m/Y à H:i", strtotime($opts['date_prevue'])) : 'Dès que possible'; ?>)
            </div>
        <?php endif; ?>

        <p style="margin-bottom: 30px;">Montant total à régler : <strong style="font-size: 1.5em; color: #e74c3c;"><?php echo number_format($total_commande, 2); ?> €</strong></p>
        
        <div style="background: #f9f9f9; padding: 30px; border-radius: 10px; text-align: left; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
            <form action="paiement.php" method="POST">
                <input type="hidden" name="action_payer" value="1">

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Nom sur la carte :</label>
                    <input type="text" value="<?php echo $_SESSION['user_prenom']; ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: #eee;">
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Numéro de carte bancaire :</label>
                    <input type="text" value="**** **** **** 1234" readonly style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: #eee;">
                </div>
                
                <button type="submit" class="btn-submit" style="width: 100%; font-size: 1.2em; padding: 15px;">Confirmer et Payer</button>
            </form>
        </div>
    <?php endif; ?>
    
</main>

<?php include 'includes/footer.php'; ?>