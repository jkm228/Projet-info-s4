<?php 
$page_title = "Africa United - Bienvenue"; 
include 'includes/header.php'; 

// 1. On lit le fichier JSON pour récupérer les menus
$json_data = file_get_contents('data/plats.json');
$plats = json_decode($json_data, true);
?>

    <main>
        <section class="hero-section">
            <div class="hero-content">
                <h1>Africa United</h1>
                <div class="search-container">
                    <input type="text" id="search-input" placeholder="Recherchez un plat (ex: Mafé, Yassa...)">
                    <button type="button">🔍</button>
                </div>
            </div>
        </section>

        <section class="plats-section" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; margin-bottom: 30px;">Nos Escales Gourmandes (Menus)</h2>
            
            <div class="presentation-grid">
                <?php 
                // 2. On boucle sur tous les éléments du JSON
                foreach ($plats as $item): 
                    // On vérifie le nom de la catégorie ("menu" ou "Menu")
                    $cat = isset($item['categorie']) ? strtolower($item['categorie']) : (isset($item['catégorie']) ? strtolower($item['catégorie']) : '');
                    
                    if ($cat == 'menu'): 
                ?>
                    <div class="menu-card">
                        <div class="menu-flag"><?php echo $item['pays']; ?></div>
                        <div class="menu-title bg-rouge"><?php echo $item['nom']; ?></div>
                        
                        <div class="menu-items" style="padding: 15px; text-align: left; font-size: 0.9em;">
                            <ul style="list-style-type: none; padding-left: 0;">
                                <?php foreach($item['plats'] as $sous_plat): ?>
                                    <li style="margin-bottom: 8px;">
                                        <strong><?php echo $sous_plat['nom']; ?></strong> : <br>
                                        <span style="color: #666; font-size: 0.85em;"><?php echo $sous_plat['description']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($item['prix'], 2); ?>€</span>
                            <a href="panier.php?ajouter=<?php echo $item['id']; ?>" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                        </div>
                    </div>
                <?php 
                    endif; 
                endforeach; 
                ?>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <a href="presentation.php" class="btn-carte">Découvrir notre carte complète</a>
            </div>
        </section>
    </main>

<?php 
include 'includes/footer.php'; 
?>