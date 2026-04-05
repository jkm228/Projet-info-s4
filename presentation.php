<?php 
$page_title = "Africa United - La Carte"; 
include 'includes/header.php'; 

// 1. PHP va lire ton fichier JSON
$json_data = file_get_contents('data/plats.json');
// 2. PHP le transforme en un tableau compréhensible
$plats = json_decode($json_data, true);
?>

    <main class="presentation-page">
        
        <div class="presentation-header">
            <h1>Composez votre voyage</h1>
            <p>De Marrakech au Cap, savourez l'Afrique.</p>
            
            <div class="search-container search-carte">
                <input type="text" placeholder="Rechercher un plat (ex: Mafé, Yassa...)">
                <button type="button">🔍</button>
            </div>
        </div>

        <div class="filters-container">
            <div class="category-buttons sticky-nav">
                <a href="#boissons" class="filter-btn">Boissons</a>
                <a href="#entrees" class="filter-btn">Entrées</a>
                <a href="#plats" class="filter-btn">Plats</a>
                <a href="#desserts" class="filter-btn">Desserts</a>
            </div>
        </div>

        <section id="boissons" class="menu-section">
            <h2 class="section-title">🍹 Nos Boissons</h2>
            <div class="presentation-grid">
                <?php foreach ($plats as $plat): 
                    // On vérifie le nom de la catégorie (pour éviter les bugs d'accents ou majuscules)
                    $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
                    if ($cat == 'boisson' || $cat == 'boissons'): 
                ?>
                    <div class="menu-card">
                        <?php if(isset($plat['image'])): ?>
                            <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">
                        <?php endif; ?>
                        
                        <div class="menu-flag"><?php echo $plat['pays']; ?></div>
                        <div class="menu-title bg-vert"><?php echo $plat['nom']; ?></div>
                        <div class="menu-items"><p><?php echo $plat['description']; ?></p></div>
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($plat['prix'], 2); ?>€</span>
                            <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>

        <section id="entrees" class="menu-section">
            <h2 class="section-title">🥟 Nos Entrées</h2>
            <div class="presentation-grid">
                <?php foreach ($plats as $plat): 
                    $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
                    if ($cat == 'entree' || $cat == 'entrée' || $cat == 'entrées'): 
                ?>
                    <div class="menu-card">
                        <?php if(isset($plat['image'])): ?>
                            <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">
                        <?php endif; ?>
                        
                        <div class="menu-flag"><?php echo $plat['pays']; ?></div>
                        <div class="menu-title bg-orange"><?php echo $plat['nom']; ?></div>
                        <div class="menu-items"><p><?php echo $plat['description']; ?></p></div>
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($plat['prix'], 2); ?>€</span>
                            <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>

        <section id="plats" class="menu-section">
            <h2 class="section-title">🥘 Nos Plats de Résistance</h2>
            <div class="presentation-grid">
                <?php foreach ($plats as $plat): 
                    $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
                    if ($cat == 'plat' || $cat == 'plats'): 
                ?>
                    <div class="menu-card">
                        <?php if(isset($plat['image'])): ?>
                            <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">
                        <?php endif; ?>
                        
                        <div class="menu-flag"><?php echo $plat['pays']; ?></div>
                        <div class="menu-title bg-rouge"><?php echo $plat['nom']; ?></div>
                        <div class="menu-items"><p><?php echo $plat['description']; ?></p></div>
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($plat['prix'], 2); ?>€</span>
                            <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>

        <section id="desserts" class="menu-section">
            <h2 class="section-title">🍰 Nos Douceurs Sucrées</h2>
            <div class="presentation-grid">
                <?php foreach ($plats as $plat): 
                    $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
                    if ($cat == 'dessert' || $cat == 'desserts'): 
                ?>
                    <div class="menu-card">
                        <?php if(isset($plat['image'])): ?>
                            <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">
                        <?php endif; ?>
                        
                        <div class="menu-flag"><?php echo $plat['pays']; ?></div>
                        <div class="menu-title bg-violet"><?php echo $plat['nom']; ?></div>
                        <div class="menu-items"><p><?php echo $plat['description']; ?></p></div>
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($plat['prix'], 2); ?>€</span>
                            <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>

    </main>

<?php 
include 'includes/footer.php'; 
?>