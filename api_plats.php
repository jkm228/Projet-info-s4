<?php
header('Content-Type: application/json');

$json_data = file_get_contents('data/plats.json');
$plats = json_decode($json_data, true);

$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'tous';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$plats_filtres = [];

foreach ($plats as $plat) {
    // 1. Catégorie
    $cat = isset($plat['categorie']) ? $plat['categorie'] : ($plat['catégorie'] ?? '');
    $match_cat = ($categorie === 'tous' || stripos($cat, $categorie) !== false);

    // 2. Recherche textuelle sécurisée
    $nom = $plat['nom'] ?? '';
    $desc = $plat['description'] ?? '';
    
    if ($search === '') {
        $match_search = true;
    } else {
        // stripos trouve le mot même s'il y a une différence de majuscules
        $match_search = (stripos($nom, $search) !== false || stripos($desc, $search) !== false);
    }

    if ($match_cat && $match_search) {
        $plats_filtres[] = $plat;
    }
}

echo json_encode($plats_filtres);
?>