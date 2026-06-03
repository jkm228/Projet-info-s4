<?php 
$page_title = "Africa United - Mon Panier"; 
include 'includes/header.php'; 

if (!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id_article = isset($_GET['id']) ? $_GET['id'] : null;

    if ($action == 'vider') { $_SESSION['panier'] = []; } 
    elseif ($action == 'plus' && $id_article) { $_SESSION['panier'][$id_article]++; } 
    elseif ($action == 'moins' && $id_article) {
        $_SESSION['panier'][$id_article]--; 
        if ($_SESSION['panier'][$id_article] <= 0) { unset($_SESSION['panier'][$id_article]); }
    }
    header("Location: panier.php"); exit();
}

if (isset($_GET['ajouter'])) {
    $id_article = $_GET['ajouter'];
    if (isset($_SESSION['panier'][$id_article])) { $_SESSION['panier'][$id_article]++; } 
    else { $_SESSION['panier'][$id_article] = 1; }
    $page_precedente = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'presentation.php';
    header("Location: " . $page_precedente); exit();
}

$json_data = file_get_contents('data/plats.json');
$tous_les_articles = json_decode($json_data, true);
$total_commande = 0;
?>

<main class="panier-page main-container-900">
    <h1 class="text-center margin-bottom-30">🛒 Mon Panier</h1>

    <?php if (empty($_SESSION['panier'])): ?>
        <div class="empty-panier-box">
            <p class="text-lg text-secondary">Votre panier est tristement vide...</p>
            <a href="presentation.php" class="btn-submit btn-panier-empty">Voir la carte</a>
        </div>
    <?php else: ?>
        <table class="panier-table">
            <thead class="panier-thead">
                <tr>
                    <th class="text-left">Article</th>
                    <th class="text-center">Quantité</th>
                    <th class="text-right">Prix Unitaire</th>
                    <th class="text-right">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($_SESSION['panier'] as $id_dans_panier => $quantite): 
                    $article_trouve = null;
                    foreach ($tous_les_articles as $item) {
                        if ($item['id'] == $id_dans_panier) { $article_trouve = $item; break; }
                    }
                    if ($article_trouve): 
                        $sous_total = $article_trouve['prix'] * $quantite;
                        $total_commande += $sous_total;
                ?>
                    <tr class="panier-tr-body">
                        <td><strong><?php echo $article_trouve['nom']; ?></strong></td>
                        <td class="text-center">
                            <a href="panier.php?action=moins&id=<?php echo $id_dans_panier; ?>" class="btn-qty-minus">-</a>
                            <span class="panier-qty-text"><?php echo $quantite; ?></span>
                            <a href="panier.php?action=plus&id=<?php echo $id_dans_panier; ?>" class="btn-qty-plus">+</a>
                        </td>
                        <td class="text-right"><?php echo number_format($article_trouve['prix'], 2); ?> €</td>
                        <td class="text-right font-bold"><?php echo number_format($sous_total, 2); ?> €</td>
                    </tr>
                <?php endif; endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="panier-tfoot-tr">
                    <td colspan="3" class="text-right"><strong>TOTAL À PAYER :</strong></td>
                    <td class="text-right text-red font-bold"><strong><?php echo number_format($total_commande, 2); ?> €</strong></td>
                </tr>
            </tfoot>
        </table>

        <form action="paiement.php" method="POST" class="panier-options-form">
            <h3 class="panier-options-title">Vos options de récupération</h3>

            <div class="panier-options-row">
                <strong class="panier-options-label">Mode de retrait :</strong>
                <label class="pointer-margin-right"><input type="radio" name="type_retrait" value="Livraison" checked> 🛵 Livraison</label>
                <label class="pointer-cursor"><input type="radio" name="type_retrait" value="À emporter"> 🛍️ À emporter</label>
            </div>

            <div class="panier-options-row-large">
                <strong class="panier-options-label">Préparation :</strong>
                <label class="pointer-margin-right"><input type="radio" name="moment" value="Immédiate" checked> 🕒 Dès que possible</label>
                <label class="pointer-cursor"><input type="radio" name="moment" value="Programmée"> 📅 Plus tard :</label>
                <input type="datetime-local" name="date_prevue" class="panier-datetime-input">
            </div>

            <div class="panier-options-footer">
                <a href="panier.php?action=vider" class="btn-panier-clear">Vider le panier</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <button type="submit" class="btn-submit btn-panier-pay">Passer au paiement</button>
                <?php else: ?>
                    <a href="connexion.php" class="btn-submit btn-panier-login">Connectez-vous pour valider</a>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>