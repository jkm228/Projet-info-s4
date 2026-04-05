<?php 
$page_title = "Africa United - Mon Panier"; 
include 'includes/header.php'; 

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = []; 
}

// 1. GESTION DES ACTIONS (Vider, Plus, Moins)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id_article = isset($_GET['id']) ? $_GET['id'] : null;

    if ($action == 'vider') {
        $_SESSION['panier'] = []; // On vide le sac
    } 
    elseif ($action == 'plus' && $id_article) {
        $_SESSION['panier'][$id_article]++; // On ajoute 1
    } 
    elseif ($action == 'moins' && $id_article) {
        $_SESSION['panier'][$id_article]--; // On enlève 1
        if ($_SESSION['panier'][$id_article] <= 0) {
            unset($_SESSION['panier'][$id_article]);
        }
    }
    header("Location: panier.php");
    exit();
}

// 2. AJOUT CLASSIQUE DEPUIS LA CARTE
if (isset($_GET['ajouter'])) {
    $id_article = $_GET['ajouter'];
    if (isset($_SESSION['panier'][$id_article])) {
        $_SESSION['panier'][$id_article]++;
    } else {
        $_SESSION['panier'][$id_article] = 1;
    }
    $page_precedente = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'presentation.php';
    header("Location: " . $page_precedente);
    exit();
}

// 3. AFFICHAGE DU PANIER
$json_data = file_get_contents('data/plats.json');
$tous_les_articles = json_decode($json_data, true);
$total_commande = 0;
?>

<main class="panier-page" style="padding: 40px 20px; max-width: 900px; margin: 0 auto; min-height: 60vh;">
    <h1 style="text-align: center; margin-bottom: 30px;">🛒 Mon Panier</h1>

    <?php if (empty($_SESSION['panier'])): ?>
        <div style="text-align: center; padding: 50px; background: #f9f9f9; border-radius: 10px;">
            <p style="font-size: 1.2em; color: #666;">Votre panier est tristement vide...</p>
            <a href="presentation.php" class="btn-submit" style="display: inline-block; margin-top: 15px; text-decoration: none;">Voir la carte</a>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <thead style="background: #e74c3c; color: white;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Article</th>
                    <th style="padding: 15px; text-align: center;">Quantité</th>
                    <th style="padding: 15px; text-align: right;">Prix Unitaire</th>
                    <th style="padding: 15px; text-align: right;">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($_SESSION['panier'] as $id_dans_panier => $quantite): 
                    $article_trouve = null;
                    foreach ($tous_les_articles as $item) {
                        if ($item['id'] == $id_dans_panier) {
                            $article_trouve = $item;
                            break;
                        }
                    }

                    if ($article_trouve): 
                        $sous_total = $article_trouve['prix'] * $quantite;
                        $total_commande += $sous_total;
                ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><strong><?php echo $article_trouve['nom']; ?></strong></td>
                        
                        <td style="padding: 15px; text-align: center;">
                            <a href="panier.php?action=moins&id=<?php echo $id_dans_panier; ?>" style="display: inline-block; width: 25px; height: 25px; line-height: 25px; background: #ddd; text-decoration: none; border-radius: 50%; color: black; font-weight: bold;">-</a>
                            <span style="margin: 0 10px; font-weight: bold; font-size: 1.1em;"><?php echo $quantite; ?></span>
                            <a href="panier.php?action=plus&id=<?php echo $id_dans_panier; ?>" style="display: inline-block; width: 25px; height: 25px; line-height: 25px; background: #e74c3c; text-decoration: none; border-radius: 50%; color: white; font-weight: bold;">+</a>
                        </td>
                        
                        <td style="padding: 15px; text-align: right;"><?php echo number_format($article_trouve['prix'], 2); ?> €</td>
                        <td style="padding: 15px; text-align: right; font-weight: bold;"><?php echo number_format($sous_total, 2); ?> €</td>
                    </tr>
                <?php endif; endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f4f4f4; font-size: 1.2em;">
                    <td colspan="3" style="padding: 20px; text-align: right;"><strong>TOTAL À PAYER :</strong></td>
                    <td style="padding: 20px; text-align: right; color: #e74c3c;"><strong><?php echo number_format($total_commande, 2); ?> €</strong></td>
                </tr>
            </tfoot>
        </table>

        <form action="paiement.php" method="POST" style="margin-top: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Vos options de récupération</h3>

            <div style="margin-bottom: 15px; display: flex; align-items: center;">
                <strong style="width: 180px;">Mode de retrait :</strong>
                <label style="margin-right: 20px; cursor: pointer;"><input type="radio" name="type_retrait" value="Livraison" checked> 🛵 Livraison</label>
                <label style="cursor: pointer;"><input type="radio" name="type_retrait" value="À emporter"> 🛍️ À emporter</label>
            </div>

            <div style="margin-bottom: 25px; display: flex; align-items: center;">
                <strong style="width: 180px;">Préparation :</strong>
                <label style="margin-right: 20px; cursor: pointer;"><input type="radio" name="moment" value="Immédiate" checked> 🕒 Dès que possible</label>
                <label style="cursor: pointer;"><input type="radio" name="moment" value="Programmée"> 📅 Plus tard :</label>
                <input type="datetime-local" name="date_prevue" style="margin-left: 10px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="panier.php?action=vider" style="color: #e74c3c; text-decoration: underline; font-weight: bold;">Vider le panier</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <button type="submit" class="btn-submit" style="width: auto; padding: 15px 30px; font-size: 1.1em;">Passer au paiement</button>
                <?php else: ?>
                    <a href="connexion.php" class="btn-submit" style="background-color: #f39c12; text-decoration: none; padding: 15px 30px; width: auto;">Connectez-vous pour valider</a>
                <?php endif; ?>
            </div>
        </form>

    <?php endif; ?> </main>

<?php include 'includes/footer.php'; ?>