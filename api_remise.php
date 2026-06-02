<?php
session_start();
header('Content-Type: application/json');

// Seul l'admin ou le restaurateur peut accorder des remises
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'restaurateur')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id']) && isset($data['remise'])) {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    foreach ($utilisateurs as &$user) {
        if ($user['id'] === $data['user_id']) {
            // On enregistre la remise dans les informations du client
            $user['informations']['remise'] = (int)$data['remise'];
            break;
        }
    }

    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>