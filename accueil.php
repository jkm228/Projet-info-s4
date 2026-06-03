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
                
                </div>
        </section>

        <section class="plats-section accueil-section-container">
            <h2 class="accueil-main-title">Nos Escales Gourmandes (Menus)</h2>
            
            <div class="presentation-grid">
                <?php 
                // 2. On boucle sur tous les éléments du JSON
                foreach ($plats as $item): 
                    // On vérifie le nom de la catégorie ("menu" ou "Menu")
                    $cat = isset($item['categorie']) ? strtolower($item['categorie']) : (isset($item['catégorie']) ? strtolower($item['catégorie']) : '');
                    
                    if ($cat == 'menu'): 
                ?>
                    <div class="menu-card">
                        <div class="menu-flag"><?php echo htmlspecialchars($item['pays']); ?></div>
                        <div class="menu-title bg-rouge"><?php echo htmlspecialchars($item['nom']); ?></div>
                        
                        <div class="menu-items accueil-menu-items-box">
                            <ul class="accueil-menu-list">
                                <?php foreach($item['plats'] as $sous_plat): ?>
                                    <li class="accueil-menu-item-li">
                                        <strong><?php echo htmlspecialchars($sous_plat['nom']); ?></strong> : <br>
                                        <span class="accueil-menu-item-desc"><?php echo htmlspecialchars($sous_plat['description']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($item['prix'], 2); ?>€</span>
                            <button type="button" onclick="ajouterAuPanier('<?php echo $item['id']; ?>')" class="btn-commander">Ajouter</button>
                        </div>
                    </div>
                <?php 
                    endif; 
                endforeach; 
                ?>
            </div>

            <div class="accueil-more-link-zone">
                <a href="presentation.php" class="btn-carte">Découvrir notre carte complète</a>
            </div>
        </section>
    </main>

<?php 
include 'includes/footer.php'; 
?>