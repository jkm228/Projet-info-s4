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
    echo "<main style='padding: 50px; text-align: center;'><h2 style='color: #e74c3c;'>Action impossible</h2><p>Cette commande est déjà en préparation ou a été livrée. Elle ne peut plus être modifiée.</p><a href='profil.php' class='btn-submit' style='display:inline-block; margin-top:20px; text-decoration:none;'>Retour</a></main>";
    include 'includes/footer.php';
    exit();
}

$total_original = $commande['total'];
$remise_pourcentage = $utilisateurs[$user_index]['informations']['remise'] ?? 0;

// -------------------------------------------------------------------------
// 1. PRÉPARATION DES QUANTITÉS ACTUELLES (AVANT MODIFICATION)
// -------------------------------------------------------------------------
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

// -------------------------------------------------------------------------
// 2. TRAITEMENT DU FORMULAIRE : VALIDATION DES AJOUTS
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelles_quantites = $_POST['qty'] ?? [];
    $nouveaux_articles = [];
    $total_ajouts_brut = 0;

    foreach ($plats as $plat) {
        $id_plat = $plat['id'];
        $qty_ancienne = $quantites_actuelles[$id_plat] ?? 0;
        $qty_nouvelle = isset($nouvelles_quantites[$id_plat]) ? intval($nouvelles_quantites[$id_plat]) : $qty_ancienne;

        // Si le client a augmenté la quantité, on calcule la valeur de l'ajout
        if ($qty_nouvelle > $qty_ancienne) {
            $difference_qty = $qty_nouvelle - $qty_ancienne;
            $total_ajouts_brut += $plat['prix'] * $difference_qty;
        }

        // On conserve l'article s'il y a au moins 1 quantité
        if ($qty_nouvelle > 0) {
            $nouveaux_articles[] = $plat['nom'] . " (x" . $qty_nouvelle . ")";
        }
    }

    // Application de la remise UNIQUEMENT sur les nouveaux ajouts
    $montant_remise_ajouts = $total_ajouts_brut * ($remise_pourcentage / 100);
    $reste_a_payer = $total_ajouts_brut - $montant_remise_ajouts;

    // Le nouveau total de la commande devient l'ancien total + le reste à payer des ajouts
    $nouveau_total_commande = $total_original + $reste_a_payer;

    // Mise à jour de la commande
    $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['articles'] = $nouveaux_articles;
    $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['total'] = $nouveau_total_commande;

    if ($reste_a_payer > 0) {
        $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['type'] .= " (Complément payé: " . number_format($reste_a_payer, 2) . "€)";
        
        // Attribution des points de fidélité sur ce qui est réellement rajouté et payé (1€ = 1pt)
        if (!isset($utilisateurs[$user_index]['fidelite']['points'])) {
            $utilisateurs[$user_index]['fidelite']['points'] = 0;
        }
        $utilisateurs[$user_index]['fidelite']['points'] += floor($reste_a_payer);
    }

    // Sauvegarde
    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Écran de succès propre
    echo "<main style='padding: 50px; text-align: center;' class='dashboard-card'><h2 style='color: #27ae60;'>Commande modifiée avec succès !</h2>";
    if ($reste_a_payer > 0) {
        echo "<p style='font-size: 1.2em;'>Vous avez procédé au paiement du complément pour vos nouveaux ajouts : <strong style='color:#e74c3c;'>+" . number_format($reste_a_payer, 2) . "€</strong></p>";
        echo "<p style='color: #f39c12; font-weight: bold;'>🎁 Vous gagnez " . floor($reste_a_payer) . " points de fidélité supplémentaires !</p>";
    } else {
        echo "<p style='font-size: 1.2em;'>Le panier est resté inchangé.</p>";
    }
    echo "<a href='profil.php' class='btn-submit' style='display:inline-block; margin-top:20px; text-decoration:none;'>Retour au profil</a></main>";
    
    include 'includes/footer.php';
    exit();
}
?>

<main style="padding: 40px 20px; max-width: 900px; margin: 0 auto; min-height: 60vh;">
    <div class="dashboard-card" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #e74c3c; margin-bottom: 5px;">Compléter ma commande</h2>
        <p style="text-align: center; margin-bottom: 25px; color: #7f8c8d;">Ajoutez les articles que vous avez oubliés</p>
        
        <?php if($remise_pourcentage > 0): ?>
            <p style="text-align: center; color: #f39c12; font-weight: bold; background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                🎉 Votre avantage fidélité (-<?php echo $remise_pourcentage; ?>%) s'appliquera directement sur vos ajouts !
            </p>
        <?php endif; ?>

        <form method="POST">
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f4f4f4; border-bottom: 2px solid #ddd; color: #333;">
                        <th style="padding: 10px; text-align: left;">Plat</th>
                        <th style="padding: 10px; text-align: center;">Prix unitaire</th>
                        <th style="padding: 10px; text-align: center;">Quantité</th>
                        <th style="padding: 10px; text-align: right;">Sous-total Ligne</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($plats as $plat): 
                        $id_p = $plat['id'];
                        $qty_initiale = $quantites_actuelles[$id_p] ?? 0;
                        $sous_total = $qty_initiale * $plat['prix'];
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">
                            <strong><?php echo htmlspecialchars($plat['nom']); ?></strong>
                            <?php if($qty_initiale > 0): ?>
                                <span style="font-size: 0.85em; color: #27ae60; block; margin-left: 5px;">(Déjà commandé: x<?php echo $qty_initiale; ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px; text-align: center; color: #7f8c8d;"><?php echo number_format($plat['prix'], 2); ?> €</td>
                        <td style="padding: 10px; text-align: center;">
                            <input type="number" name="qty[<?php echo $id_p; ?>]" class="qty-input" data-id="<?php echo $id_p; ?>" data-prix="<?php echo $plat['prix']; ?>" value="<?php echo $qty_initiale; ?>" min="<?php echo $qty_initiale; ?>" max="20" style="width: 60px; padding: 5px; text-align: center; border: 1px solid #ccc; border-radius: 4px;">
                        </td>
                        <td style="padding: 10px; text-align: right; font-weight: bold;" class="sous-total-cell"><?php echo number_format($sous_total, 2); ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd; max-width: 500px; margin-left: auto;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1em;">
                    <span style="color: #555;">Montant de vos ajouts (Brut) :</span>
                    <strong id="montant-ajouts" style="color: #333;">0.00 €</strong>
                </div>
                
                <?php if($remise_pourcentage > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1em; color: #bc9229;">
                        <span>Remise sur ajouts (-<?php echo $remise_pourcentage; ?>%) :</span>
                        <strong id="remise-ajouts">-0.00 €</strong>
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #ccc; font-size: 1.3em;">
                    <span style="font-weight: bold; color: #333;">Reste à payer :</span>
                    <strong id="reste-a-payer" style="color: #e74c3c;">0.00 €</strong>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn-submit" style="background-color: #27ae60; font-size: 1.2em; padding: 15px 40px;">Payer mes ajouts</button>
                <a href="profil.php" style="display: block; margin-top: 15px; color: #7f8c8d; text-decoration: underline;">Annuler et revenir</a>
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
            
            // Met à jour l'affichage visuel de la ligne du tableau
            const sousTotalLigne = qtyNouvelle * prix;
            input.closest('tr').querySelector('.sous-total-cell').textContent = sousTotalLigne.toFixed(2) + ' €';
            
            // On calcule l'écart uniquement si la quantité augmente
            if (qtyNouvelle > qtyInitiale) {
                totalAjoutsBrut += (qtyNouvelle - qtyInitiale) * prix;
            }
        });

        // Calculs financiers des ajouts uniquement
        const montantRemise = totalAjoutsBrut * (remisePourcentage / 100);
        const resteAPayer = totalAjoutsBrut - montantRemise;

        // Injection dynamique dans la boîte récapitulative
        spanAjouts.textContent = totalAjoutsBrut.toFixed(2) + ' €';
        if (spanRemise) {
            spanRemise.textContent = '-' + montantRemise.toFixed(2) + ' €';
        }
        spanResteAPayer.textContent = resteAPayer.toFixed(2) + ' €';
    }
    
    // Lancement au chargement de la page
    updateTotals();
});
</script>

<?php include 'includes/footer.php'; ?>