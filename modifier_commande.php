<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Modifier ma commande";
include 'includes/header.php';

// Sécurité : On vérifie que l'utilisateur est connecté et qu'il a bien ciblé une commande
if (!isset($_SESSION['user_id']) || !isset($_GET['id_cmd'])) {
    header("Location: profil.php");
    exit();
}

$id_cmd = intval($_GET['id_cmd']);
$users_file = 'data/utilisateurs.json';
$utilisateurs = json_decode(file_get_contents($users_file), true);
$plats = json_decode(file_get_contents('data/plats.json'), true);

// On cherche l'index de l'utilisateur dans le JSON
$user_index = -1;
foreach ($utilisateurs as $index => $u) {
    if ($u['id'] === $_SESSION['user_id']) {
        $user_index = $index;
        break;
    }
}

if ($user_index === -1) { header("Location: connexion.php"); exit(); }

// On récupère la commande spécifique
$historique = $utilisateurs[$user_index]['fidelite']['historique_commandes'] ?? [];
if (!isset($historique[$id_cmd])) { header("Location: profil.php"); exit(); }

$commande = $historique[$id_cmd];

// Sécurité : On ne peut modifier QUE les commandes "À préparer"
if (($commande['statut'] ?? '') !== 'À préparer') {
    echo "<main class='modify-cmd-error-container'><h2 class='text-red'>Action impossible</h2><p>Cette commande est déjà en préparation ou a été livrée. Elle ne peut plus être modifiée.</p><a href='profil.php' class='btn-submit btn-modify-error-back'>Retour</a></main>";
    include 'includes/footer.php';
    exit();
}

$total_original = $commande['total'];
$remise_pourcentage = $utilisateurs[$user_index]['informations']['remise'] ?? 0;

// PRÉPARATION DES QUANTITÉS ACTUELLES
$quantites_actuelles = [];
foreach ($commande['articles'] as $art_str) {
    if (preg_match('/^(.*) \(x(\d+)\)$/', $art_str, $matches)) {
        $nom = trim($matches[1]);
        $qty = intval($matches[2]);
        foreach ($plats as $p) {
            if ($p['nom'] === $nom) {
                $quantites_actuelles[$p['id']] = $qty;
                break;
            }
        }
    }
}

// TRAITEMENT DU FORMULAIRE : VALIDATION DES AJOUTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelles_quantites = $_POST['qty'] ?? [];
    $nouveaux_articles = [];
    $total_ajouts_brut = 0;

    foreach ($plats as $plat) {
        $id_plat = $plat['id'];
        $qty_ancienne = $quantites_actuelles[$id_plat] ?? 0;
        $qty_nouvelle = isset($nouvelles_quantites[$id_plat]) ? intval($nouvelles_quantites[$id_plat]) : $qty_ancienne;

        if ($qty_nouvelle > $qty_ancienne) {
            $difference_qty = $qty_nouvelle - $qty_ancienne;
            $total_ajouts_brut += $plat['prix'] * $difference_qty;
        }

        if ($qty_nouvelle > 0) {
            $nouveaux_articles[] = $plat['nom'] . " (x" . $qty_nouvelle . ")";
        }
    }

    $montant_remise_ajouts = $total_ajouts_brut * ($remise_pourcentage / 100);
    $reste_a_payer = $total_ajouts_brut - $montant_remise_ajouts;
    $nouveau_total_commande = $total_original + $reste_a_payer;

    $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['articles'] = $nouveaux_articles;
    $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['total'] = $nouveau_total_commande;

    if ($reste_a_payer > 0) {
        $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['type'] .= " (Complément payé: " . number_format($reste_a_payer, 2) . "€)";
        if (!isset($utilisateurs[$user_index]['fidelite']['points'])) {
            $utilisateurs[$user_index]['fidelite']['points'] = 0;
        }
        $utilisateurs[$user_index]['fidelite']['points'] += floor($reste_a_payer);
    }

    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "<main class='modify-cmd-success-container'><h2 class='text-green'>Commande modifiée avec succès !</h2>";
    if ($reste_a_payer > 0) {
        echo "<p class='modify-success-text'>Vous avez procédé au paiement du complément pour vos nouveaux ajouts : <strong class='text-red'>+" . number_format($reste_a_payer, 2) . "€</strong></p>";
        echo "<p class='modify-success-points'>🎁 Vous gagnez " . floor($reste_a_payer) . " points de fidélité supplémentaires !</p>";
    } else {
        echo "<p class='modify-success-text'>Le panier est resté inchangé.</p>";
    }
    echo "<a href='profil.php' class='btn-submit btn-modify-success-back'>Retour au profil</a></main>";
    
    include 'includes/footer.php';
    exit();
}
?>

<main class="modify-cmd-page">
    <div class="dashboard-card modify-cmd-card">
        <h2 class="modify-cmd-title">Compléter ma commande</h2>
        <p class="modify-cmd-subtitle">Ajoutez les articles que vous avez oubliés</p>
        
        <?php if($remise_pourcentage > 0): ?>
            <p class="modify-cmd-alert-remise">
                🎉 Votre avantage fidélité (-<?php echo $remise_pourcentage; ?>%) s'appliquera directement sur vos ajouts !
            </p>
        <?php endif; ?>

        <form method="POST">
            <table class="modify-cmd-table">
                <thead>
                    <tr class="modify-table-header-row">
                        <th>Plat</th>
                        <th class="text-center">Prix unitaire</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-right">Sous-total Ligne</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($plats as $plat): 
                        $id_p = $plat['id'];
                        $qty_initiale = $quantites_actuelles[$id_p] ?? 0;
                        $sous_total = $qty_initiale * $plat['prix'];
                    ?>
                    <tr class="modify-table-body-row">
                        <td>
                            <strong><?php echo htmlspecialchars($plat['nom']); ?></strong>
                            <?php if($qty_initiale > 0): ?>
                                <span class="already-ordered-badge">(Déjà commandé: x<?php echo $qty_initiale; ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center text-secondary"><?php echo number_format($plat['prix'], 2); ?> €</td>
                        <td class="text-center">
                            <input type="number" name="qty[<?php echo $id_p; ?>]" class="qty-input modify-qty-field" data-id="<?php echo $id_p; ?>" data-prix="<?php echo $plat['prix']; ?>" value="<?php echo $qty_initiale; ?>" min="<?php echo $qty_initiale; ?>" max="20">
                        </td>
                        <td class="text-right text-bold sous-total-cell"><?php echo number_format($sous_total, 2); ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="modify-summary-box">
                <div class="modify-summary-row">
                    <span class="text-dark-gray">Montant de vos ajouts (Brut) :</span>
                    <strong id="montant-ajouts" class="text-black">0.00 €</strong>
                </div>
                
                <?php if($remise_pourcentage > 0): ?>
                    <div class="modify-summary-row text-gold">
                        <span>Remise sur ajouts (-<?php echo $remise_pourcentage; ?>%) :</span>
                        <strong id="remise-ajouts">-0.00 €</strong>
                    </div>
                <?php endif; ?>

                <div class="modify-summary-total-row">
                    <span class="text-bold text-black">Reste à payer :</span>
                    <strong id="reste-a-payer" class="text-red-important">0.00 €</strong>
                </div>
            </div>

            <div class="modify-action-buttons-zone">
                <button type="submit" class="btn-submit btn-modify-submit">Payer mes ajouts</button>
                <a href="profil.php" class="btn-modify-cancel">Annuler et revenir</a>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const remisePourcentage = <?php echo $remise_pourcentage; ?>;
    const quantitesInitiales = <?php echo json_encode($quantites_actuelles); ?>;
    
    const qtyInputs = document.querySelectorAll('.qty-input');
    const spanAjouts = document.getElementById('montant-ajouts');
    const spanRemise = document.getElementById('remise-ajouts');
    const spanResteAPayer = document.getElementById('reste-a-payer');

    qtyInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });

    function updateTotals() {
        let totalAjoutsBrut = 0;
        
        qtyInputs.forEach(input => {
            const idPlat = input.getAttribute('data-id');
            const prix = parseFloat(input.getAttribute('data-prix'));
            const qtyNouvelle = parseInt(input.value) || 0;
            const qtyInitiale = quantitesInitiales[idPlat] || 0;
            
            const sousTotalLigne = qtyNouvelle * prix;
            input.closest('tr').querySelector('.sous-total-cell').textContent = sousTotalLigne.toFixed(2) + ' €';
            
            if (qtyNouvelle > qtyInitiale) {
                totalAjoutsBrut += (qtyNouvelle - qtyInitiale) * prix;
            }
        });

        const montantRemise = totalAjoutsBrut * (remisePourcentage / 100);
        const resteAPayer = totalAjoutsBrut - montantRemise;

        spanAjouts.textContent = totalAjoutsBrut.toFixed(2) + ' €';
        if (spanRemise) {
            spanRemise.textContent = '-' + montantRemise.toFixed(2) + ' €';
        }
        spanResteAPayer.textContent = resteAPayer.toFixed(2) + ' €';

        // Code professionnel : On applique une classe CSS plutôt que de modifier le .style en brut !
        if (resteAPayer > 0) {
            spanResteAPayer.className = "text-red-important";
        } else {
            spanResteAPayer.className = "text-gray-important";
        }
    }
    
    updateTotals();
});
</script>

<?php include 'includes/footer.php'; ?>