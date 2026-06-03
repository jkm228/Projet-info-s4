<?php 
$page_title = "Africa United - La Carte"; 
include 'includes/header.php'; 

$json_data = file_get_contents('data/plats.json');
$tous_les_plats = json_decode($json_data, true);

// 🛡️ NOUVEAU : Filtrage PHP instantané
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$plats = [];

if ($search_query !== '') {
    foreach ($tous_les_plats as $plat) {
        $nom = $plat['nom'] ?? '';
        $desc = $plat['description'] ?? '';
        if (stripos($nom, $search_query) !== false || stripos($desc, $search_query) !== false) {
            $plats[] = $plat;
        }
    }
} else {
    $plats = $tous_les_plats; 
}
?>

    <main class="presentation-page">
        
        <div class="presentation-header">
            <h1>Composez votre voyage</h1>
            <p>De Marrakech au Cap, savourez l'Afrique.</p>
            
            <div class="search-container search-carte">
                <input type="text" id="search-input" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Rechercher un plat (ex: Mafé, Yassa...)">
                <button type="button">🔍</button>
            </div>
        </div>

        <div class="filters-container presentation-filters">
            <strong class="filter-label">Filtres :</strong>
            <button class="btn-filter btn-submit btn-filter-all" data-categorie="tous">Tout</button>
            <button class="btn-filter btn-submit btn-filter-item" data-categorie="entree">Entrées</button>
            <button class="btn-filter btn-submit btn-filter-item" data-categorie="plat">Plats</button>
            <button class="btn-filter btn-submit btn-filter-item" data-categorie="dessert">Desserts</button>
            <button class="btn-filter btn-submit btn-filter-item" data-categorie="boisson" style="margin-right: 20px;">Boissons</button>

            <strong class="filter-label sort-label">Trier par :</strong>
            <select id="sort-plats" class="sort-select">
                <option value="defaut">Ordre par défaut</option>
                <option value="prix-asc">Prix : Croissant 📈</option>
                <option value="prix-desc">Prix : Décroissant 📉</option>
            </select>
        </div>

        <section class="menu-section">
            <h2 class="section-title" id="titre-section">🌍 Toute notre carte</h2>
            
            <div id="menu-grid" class="presentation-grid">
                <?php if (empty($plats)): ?>
                    <p class="empty-plats-msg">Aucun plat ne correspond à votre recherche.</p>
                <?php else: ?>
                    <?php foreach ($plats as $plat): 
                        $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
                        
                        $bg_class = 'bg-rouge'; 
                        if (strpos($cat, 'boisson') !== false) $bg_class = 'bg-vert';
                        if (strpos($cat, 'entree') !== false || strpos($cat, 'entrée') !== false) $bg_class = 'bg-orange';
                        if (strpos($cat, 'dessert') !== false) $bg_class = 'bg-violet';
                    ?>
                        <div class="menu-card plat-card" data-prix="<?php echo $plat['prix']; ?>">
                            <?php if(isset($plat['image'])): ?>
                                <img src="<?php echo $plat['image']; ?>" alt="<?php echo htmlspecialchars($plat['nom']); ?>" class="plat-image">
                            <?php endif; ?>
                            
                            <div class="menu-flag"><?php echo htmlspecialchars($plat['pays'] ?? ''); ?></div>
                            <div class="menu-title <?php echo $bg_class; ?>"><?php echo htmlspecialchars($plat['nom']); ?></div>
                            <div class="menu-items"><p><?php echo htmlspecialchars($plat['description'] ?? ''); ?></p></div>
                            <div class="menu-footer">
                                <span class="menu-price"><?php echo number_format($plat['prix'], 2); ?>€</span>
                                <button type="button" onclick="ajouterAuPanier('<?php echo $plat['id']; ?>')" class="btn-commander">Ajouter</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

<?php include 'includes/footer.php'; ?>