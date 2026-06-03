<?php 
$page_title = "Africa United - Paiement"; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: connexion.php"); exit(); }
if (empty($_SESSION['panier'])) { header("Location: panier.php"); exit(); }

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
            $total_commande += $item['prix'] * $quantite;
            $liste_noms_articles[] = $item['nom'] . " (x" . $quantite . ")";
        }
    }
}

$remise_pourcentage = 0;
$utilisateurs_temp = json_decode(file_get_contents('data/utilisateurs.json'), true);
foreach ($utilisateurs_temp as $u) {
    if ($u['id'] == $_SESSION['user_id']) { $remise_pourcentage = $u['informations']['remise'] ?? 0; break; }
}

$montant_remise = $total_commande * ($remise_pourcentage / 100);
$total_a_payer = $total_commande - $montant_remise;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_payer'])) {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    $options = $_SESSION['options_commande'] ?? ['type_retrait' => 'Livraison', 'moment' => 'Immédiate', 'date_prevue' => ''];
    
    $heure_livraison = "Dès que possible";
    if ($options['moment'] == 'Programmée' && !empty($options['date_prevue'])) {
        $heure_livraison = date("d/m/Y à H:i", strtotime($options['date_prevue']));
    }

    $nouvelle_commande = [
        "date_passage" => date("d/m/Y à H:i"), "date_prevue" => $heure_livraison, 
        "type" => $options['type_retrait'], "statut" => "À préparer", 
        "total" => $total_a_payer, "articles" => $liste_noms_articles
    ];

    foreach ($utilisateurs as &$user) {
        if ($user['id'] == $_SESSION['user_id']) {
            $user['fidelite']['historique_commandes'][] = $nouvelle_commande;
            if (!isset($user['fidelite']['points'])) { $user['fidelite']['points'] = 0; }
            $user['fidelite']['points'] += floor($total_a_payer);
            break;
        }
    }
    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION['panier'] = []; unset($_SESSION['options_commande']);
    $paiement_reussi = true;
}
?>

<main class="main-container-600 text-center">
    <?php if(isset($paiement_reussi)): ?>
        <div class="payment-success-box">
            <h1 class="text-green margin-bottom-20">Paiement Réussi ! ✅</h1>
            <p class="text-lg text-black">Merci pour votre commande.</p>
            <p class="text-secondary margin-top-10">Elle a bien été enregistrée selon vos préférences.</p>
            <p class="payment-points-earned">🎁 Vous venez de gagner <?php echo floor($total_a_payer); ?> points de fidélité !</p>
            <a href="profil.php" class="btn-submit btn-payment-history">Voir mon historique</a>
        </div>
    <?php else: ?>
        <h1>Paiement Sécurisé 🔒</h1>
        
        <?php $opts = $_SESSION['options_commande'] ?? null; if($opts): ?>
            <div class="payment-recap-box">
                <strong>Récapitulatif :</strong> <?php echo htmlspecialchars($opts['type_retrait']); ?> 
                (<?php echo ($opts['moment'] == 'Programmée') ? 'Prévue le ' . date("d/m/Y à H:i", strtotime($opts['date_prevue'])) : 'Dès que possible'; ?>)
            </div>
        <?php endif; ?>

        <?php if($remise_pourcentage > 0): ?>
            <div class="payment-remise-alert">
                <p class="margin-0 text-gold-dark font-bold">🎉 Avantage Fidélité : Vous bénéficiez d'une remise de <?php echo $remise_pourcentage; ?>% !</p>
            </div>
            <p class="margin-bottom-30 text-lg">
                Sous-total : <del class="text-secondary"><?php echo number_format($total_commande, 2); ?> €</del><br>
                Montant total à régler : <strong class="text-red-important"><?php echo number_format($total_a_payer, 2); ?> €</strong>
            </p>
        <?php else: ?>
            <p class="margin-bottom-30">Montant total à régler : <strong class="text-red-important"><?php echo number_format($total_a_payer, 2); ?> €</strong></p>
        <?php endif; ?>
        
        <div class="payment-form-container">
            <form action="paiement.php" method="POST">
                <input type="hidden" name="action_payer" value="1">
                <div class="form-group-block">
                    <label class="form-label-bold">Nom sur la carte :</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['user_prenom']); ?>" readonly class="input-readonly">
                </div>
                <div class="form-group-block password-margin">
                    <label class="form-label-bold">Numéro de carte bancaire :</label>
                    <input type="text" value="**** **** **** 1234" readonly class="input-readonly">
                </div>
                <button type="submit" class="btn-submit btn-payment-submit">Confirmer et Payer (<?php echo number_format($total_a_payer, 2); ?> €)</button>
            </form>
        </div>
    <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>