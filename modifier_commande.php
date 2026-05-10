<?php
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

// Sécurité Phase 3 : On ne peut modifier QUE les commandes "À préparer"
if (($commande['statut'] ?? '') !== 'À préparer') {
    echo "<main style='padding: 50px; text-align: center;'><h2 style='color: #e74c3c;'>Action impossible</h2><p>Cette commande est déjà en préparation ou a été livrée. Elle ne peut plus être modifiée.</p><a href='profil.php' class='btn-submit' style='display:inline-block; margin-top:20px; text-decoration:none;'>Retour</a></main>";
    include 'includes/footer.php';
    exit();
}

$total_original = $commande['total'];

// -------------------------------------------------------------------------
// 2. TRAITEMENT DU FORMULAIRE : QUAND LE CLIENT VALIDE SES MODIFICATIONS
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelles_quantites = $_POST['qty'] ?? [];
    $nouveaux_articles = [];
    $nouveau_total = 0;

    // On recalcule le panier complet
    foreach ($plats as $plat) {
        $id_plat = $plat['id'];
        if (isset($nouvelles_quantites[$id_plat]) && $nouvelles_quantites[$id_plat] > 0) {
            $q = intval($nouvelles_quantites[$id_plat]);
            $nouveaux_articles[] = $plat['nom'] . " (x" . $q . ")"; // On reforme le texte "Plat (x2)"
            $nouveau_total += $plat['prix'] * $q;
        }
    }

    // Mise à jour de la commande dans le tableau PHP
    $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['articles'] = $nouveaux_articles;
    $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['total'] = $nouveau_total;

    // Gestion de la différence (Consigne de l'école)
    $difference = $nouveau_total - $total_original;
    if ($difference < 0) {
        $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['type'] .= " (Avoir: " . abs($difference) . "€)";
    } elseif ($difference > 0) {
        $utilisateurs[$user_index]['fidelite']['historique_commandes'][$id_cmd]['type'] .= " (Complément payé: " . $difference . "€)";
    }

    // On sauvegarde tout dans le fichier JSON
    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Message de succès final
    echo "<main style='padding: 50px; text-align: center;' class='dashboard-card'><h2 style='color: #27ae60;'>Commande modifiée avec succès !</h2>";
    if ($difference > 0) echo "<p style='font-size: 1.2em;'>Vous avez procédé au paiement de la différence : <strong style='color:#e74c3c;'>+" . number_format($difference, 2) . "€</strong></p>";
    if ($difference < 0) echo "<p style='font-size: 1.2em;'>Un ticket de réduction de <strong style='color:#27ae60;'>" . number_format(abs($difference), 2) . "€</strong> a été ajouté à votre compte pour votre prochaine commande !</p>";
    if ($difference == 0) echo "<p style='font-size: 1.2em;'>Le total reste inchangé.</p>";
    echo "<a href='profil.php' class='btn-submit' style='display:inline-block; margin-top:20px; text-decoration:none;'>Retour au profil</a></main>";
    
    include 'includes/footer.php';
    exit();
}

// -------------------------------------------------------------------------
// 1. PRÉPARATION DE L'AFFICHAGE : On déchiffre les articles actuels
// -------------------------------------------------------------------------
$quantites_actuelles = [];
// On lit des trucs comme "Café Buna (x2)" pour comprendre qu'il y en a 2
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
?>

<main style="padding: 40px 20px; max-width: 900px; margin: 0 auto; min-height: 60vh;">
    <div class="dashboard-card" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #e74c3c; margin-bottom: 5px;">Modifier ma commande</h2>
        <p style="text-align: center; margin-bottom: 25px; color: #7f8c8d;">Prévue pour le : <strong><?php echo $commande['date_prevue'] ?? ''; ?></strong></p>
        
        <form method="POST">
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f4f4f4; border-bottom: 2px solid #ddd; color: #333;">
                        <th style="padding: 10px; text-align: left;">Plat</th>
                        <th style="padding: 10px; text-align: center;">Prix unitaire</th>
                        <th style="padding: 10px; text-align: center;">Quantité</th>
                        <th style="padding: 10px; text-align: right;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($plats as $plat): 
                        $id_p = $plat['id'];
                        $qty = $quantites_actuelles[$id_p] ?? 0;
                        $sous_total = $qty * $plat['prix'];
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><strong><?php echo htmlspecialchars($plat['nom']); ?></strong></td>
                        <td style="padding: 10px; text-align: center; color: #7f8c8d;"><?php echo number_format($plat['prix'], 2); ?> €</td>
                        <td style="padding: 10px; text-align: center;">
                            <input type="number" name="qty[<?php echo $id_p; ?>]" class="qty-input" data-prix="<?php echo $plat['prix']; ?>" value="<?php echo $qty; ?>" min="0" max="20" style="width: 60px; padding: 5px; text-align: center; border: 1px solid #ccc; border-radius: 4px;">
                        </td>
                        <td style="padding: 10px; text-align: right; font-weight: bold;" class="sous-total-cell"><?php echo number_format($sous_total, 2); ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1em;">
                    <span style="color: #333;">Total initial (déjà payé) :</span>
                    <strong style="color: #333;"><?php echo number_format($total_original, 2); ?> €</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.2em; color: #2980b9;">
                    <span>Nouveau total :</span>
                    <strong id="nouveau-total"><?php echo number_format($total_original, 2); ?> €</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #ccc; font-size: 1.2em;" id="zone-difference">
                    <span style="color: #333;">Différence :</span>
                    <strong id="texte-difference" style="color: #7f8c8d;">Aucun changement</strong>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn-submit" style="background-color: #27ae60; font-size: 1.2em; padding: 15px 40px;">Valider les modifications</button>
                <a href="profil.php" style="display: block; margin-top: 15px; color: #7f8c8d; text-decoration: underline;">Annuler et revenir au profil</a>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const totalOriginal = <?php echo $total_original; ?>;
    const qtyInputs = document.querySelectorAll('.qty-input');
    const spanNouveauTotal = document.getElementById('nouveau-total');
    const spanDifference = document.getElementById('texte-difference');

    qtyInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });

    function updateTotals() {
        let nouveauTotal = 0;
        
        // 1. Calculer le nouveau total
        qtyInputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            const prix = parseFloat(input.getAttribute('data-prix'));
            const sousTotal = qty * prix;
            
            // Mettre à jour la ligne du tableau
            input.closest('tr').querySelector('.sous-total-cell').textContent = sousTotal.toFixed(2) + ' €';
            nouveauTotal += sousTotal;
        });

        spanNouveauTotal.textContent = nouveauTotal.toFixed(2) + ' €';

        // 2. Calculer la différence pour afficher si le client doit payer ou recevoir un bon d'achat
        const diff = nouveauTotal - totalOriginal;
        
        if (diff > 0) {
            spanDifference.textContent = 'Reste à payer : +' + diff.toFixed(2) + ' €';
            spanDifference.style.color = '#e74c3c'; // Rouge : Aïe, il faut payer
        } else if (diff < 0) {
            spanDifference.textContent = 'Remboursement (Bon d\'achat) : ' + Math.abs(diff).toFixed(2) + ' €';
            spanDifference.style.color = '#27ae60'; // Vert : Super, de l'argent récupéré
        } else {
            spanDifference.textContent = 'Aucun changement (0.00 €)';
            spanDifference.style.color = '#7f8c8d'; // Gris
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>