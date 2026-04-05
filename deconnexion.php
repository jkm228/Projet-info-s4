<?php
// On démarre la session pour pouvoir la manipuler
session_start();

// On vide toutes les variables de session
session_unset();

// On détruit complètement la session
session_destroy();

// On renvoie l'utilisateur vers la page d'accueil
header("Location: accueil.php");
exit();
?>