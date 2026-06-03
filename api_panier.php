<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_article'])) {
    $id = $data['id_article'];
    
    // Ajout ou incrémentation de l'article
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id]++;
    } else {
        $_SESSION['panier'][$id] = 1;
    }
    
    // On renvoie le nouveau nombre total d'articles pour mettre à jour le compteur du header
    echo json_encode([
        'success' => true, 
        'total_articles' => array_sum($_SESSION['panier'])
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>