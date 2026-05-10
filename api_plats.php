<?php
// On indique qu'on va renvoyer des données brutes (JSON)
header('Content-Type: application/json');

// On lit tout le menu
$json_data = file_get_contents('data/plats.json');
$plats = json_decode($json_data, true);

// On regarde ce que le JS nous demande de filtrer (par défaut "tous")
$filtre = isset($_GET['categorie']) ? strtolower($_GET['categorie']) : 'tous';

if ($filtre === 'tous') {
    // Si on veut tout, on renvoie tout directement
    echo json_encode($plats);
    exit();
}

$plats_filtres = [];
foreach ($plats as $plat) {
    // NOUVEAU : On regarde UNIQUEMENT dans la case 'categorie' (et pas dans la description)
    $cat = isset($plat['categorie']) ? strtolower($plat['categorie']) : (isset($plat['catégorie']) ? strtolower($plat['catégorie']) : '');
    
    // Si la catégorie contient le mot cherché (ex: 'boisson' est trouvé dans 'boissons')
    if (strpos($cat, $filtre) !== false) {
        $plats_filtres[] = $plat;
    }
}

// On renvoie seulement les plats qui correspondent vraiment
echo json_encode($plats_filtres);
?>