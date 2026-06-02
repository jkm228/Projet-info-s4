<?php
session_start();
header('Content-Type: application/json');

// Sécurité : Seul l'admin a le droit de vie ou de mort sur les comptes
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = $data['user_id'];
    
    // Sécurité : Empêcher l'admin de se supprimer lui-même par erreur
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer votre propre compte administrateur !']);
        exit();
    }

    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    $initial_count = count($utilisateurs);

    // On crée un nouveau tableau qui contient tout le monde SAUF l'utilisateur à supprimer
    $utilisateurs_filtres = array_filter($utilisateurs, function($user) use ($user_id) {
        return $user['id'] != $user_id;
    });

    // On réindexe le tableau pour éviter des clés manquantes dans le JSON
    $utilisateurs_filtres = array_values($utilisateurs_filtres);

    if (count($utilisateurs_filtres) < $initial_count) {
        file_put_contents($users_file, json_encode($utilisateurs_filtres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    }
}
?>