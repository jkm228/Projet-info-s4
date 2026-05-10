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

        <div class="filters-container" style="text-align: center; margin-bottom: 30px; background: #f9f9f9; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <strong style="margin-right: 15px; color: #333;">Filtres :</strong>
            <button class="btn-filter btn-submit" data-categorie="tous" style="width: auto; padding: 8px 15px; margin-right: 5px; background-color: #e74c3c;">Tout</button>
            <button class="btn-filter btn-submit" data-categorie="entree" style="width: auto; padding: 8px 15px; margin-right: 5px; background-color: #34495e;">Entrées</button>
            <button class="btn-filter btn-submit" data-categorie="plat" style="width: auto; padding: 8px 15px; margin-right: 5px; background-color: #34495e;">Plats</button>
            <button class="btn-filter btn-submit" data-categorie="dessert" style="width: auto; padding: 8px 15px; margin-right: 5px; background-color: #34495e;">Desserts</button>
            <button class="btn-filter btn-submit" data-categorie="boisson" style="width: auto; padding: 8px 15px; margin-right: 20px; background-color: #34495e;">Boissons</button>

            <strong style="margin-right: 10px; color: #333;">Trier par :</strong>
            <select id="sort-plats" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; background: white; color: black;">
                <option value="defaut">Ordre par défaut</option>
                <option value="prix-asc">Prix : Croissant 📈</option>
                <option value="prix-desc">Prix : Décroissant 📉</option>
            </select>
        </div>

        <section class="menu-section">
            <h2 class="section-title" id="titre-section">🌍 Toute notre carte</h2>
            
            <div id="menu-grid" class="presentation-grid">
                <?php foreach ($plats as $plat): 
                    // Sécurité pour la catégorie
                    $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
                    
                    // On détermine la couleur du bandeau selon la catégorie
                    $bg_class = 'bg-rouge'; // Par défaut (Plats)
                    if (strpos($cat, 'boisson') !== false) $bg_class = 'bg-vert';
                    if (strpos($cat, 'entree') !== false || strpos($cat, 'entrée') !== false) $bg_class = 'bg-orange';
                    if (strpos($cat, 'dessert') !== false) $bg_class = 'bg-violet';
                ?>
                    <div class="menu-card plat-card" data-prix="<?php echo $plat['prix']; ?>">
                        <?php if(isset($plat['image'])): ?>
                            <img src="<?php echo $plat['image']; ?>" alt="<?php echo htmlspecialchars($plat['nom']); ?>" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">
                        <?php endif; ?>
                        
                        <div class="menu-flag"><?php echo htmlspecialchars($plat['pays'] ?? ''); ?></div>
                        <div class="menu-title <?php echo $bg_class; ?>"><?php echo htmlspecialchars($plat['nom']); ?></div>
                        <div class="menu-items"><p><?php echo htmlspecialchars($plat['description'] ?? ''); ?></p></div>
                        <div class="menu-footer">
                            <span class="menu-price"><?php echo number_format($plat['prix'], 2); ?>€</span>
                            <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

<?php 
include 'includes/footer.php'; 
?>