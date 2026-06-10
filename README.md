# 🌍 Africa United - Plateforme de Restauration en Ligne

Bienvenue sur le dépôt du projet **Africa United**. Cette application web a été conçue pour digitaliser l'expérience d'un restaurant spécialisé dans la gastronomie africaine. De la commande du client jusqu'à la livraison, en passant par la préparation en cuisine, l'application gère l'intégralité du cycle de vie d'une commande grâce à des interfaces dédiées pour chaque corps de métier.

## 🌟 Fonctionnalités clés (Par rôle)

**🧑‍💻 Côté Client :**
* Consultation de la carte interactive avec filtres de recherche instantanés.
* Ajout au panier fluide et asynchrone sans rechargement de page (AJAX).
* Paiement sécurisé avec calcul dynamique et revérification côté serveur.
* Système de fidélité automatique incluant un cumul de points et des remises calculées en temps réel.
* Possibilité de compléter une commande en attente via un algorithme de calcul de différence (Delta) pour facturer uniquement les ajouts.
* Suivi de l'état de la commande et système de notation à la réception.

**👨‍🍳 Côté Cuisine (Restaurateur) :**
* Tableau de bord optimisé (Mode Sombre) affichant uniquement les bons de commandes "En préparation".
* Validation en un clic lorsqu'une commande est prête à être récupérée ou expédiée.

**🛵 Côté Livreur :**
* Interface adaptée aux mobiles (Mode Sombre) détaillant les tournées avec adresses et contacts clients cliquables.
* Validation de la remise en main propre pour déclencher la fin de la commande.

**👑 Côté Administration :**
* Vue d'ensemble sur l'activité du restaurant et gestion des flux vers la cuisine ou les livreurs.
* Gestion de la base de données utilisateurs (blocage de comptes, suppression, attribution d'avantages de fidélité).

## 🛠️ Technologies Utilisées

* **Frontend (Interface) :** HTML5, CSS3 (Design sur-mesure, 100% responsive) et Vanilla JavaScript (pour les requêtes dynamiques).
* **Backend (Logique métier) :** PHP natif avec séparation stricte de la logique et de l'affichage visuel.
* **Base de données :** Fichiers JSON (`plats.json`, `utilisateurs.json`) permettant un stockage léger, rapide et ne nécessitant pas de configuration de serveur SQL.
* **Sécurité :** Hachage des mots de passe via Bcrypt, protection stricte des pages selon les rôles (RBAC), et validation systématique des données sensibles côté serveur.

## 🚀 Installation et Lancement en local

L'application est très simple à déployer car elle ne nécessite aucune configuration de base de données relationnelle.
* **Étape 1 :** Téléchargez et installez un serveur web local comme WampServer (Windows), XAMPP ou MAMP (Mac).
* **Étape 2 :** Placez le dossier du projet dans le répertoire public de votre serveur (ex: le dossier `www` pour WAMP ou `htdocs` pour XAMPP).
* **Étape 3 :** Démarrez les services de votre serveur local (Apache).
* **Étape 4 :** Ouvrez votre navigateur et accédez au projet via l'adresse locale classique (par exemple : `http://localhost/nom-du-dossier/accueil.php`).

## 👥 L'Équipe du Projet

Ce projet a été imaginé et développé en équipe :
* **Joseph :** Cœur de la logique métier E-commerce, algorithmique complexe (Modification et ajouts aux commandes), et calculs du système de fidélité.
* **Greg :** Architecture logicielle, conception UI/UX (Design et CSS), et sécurité de l'authentification (Hachage des accès).
* **Timothé :** Gestion de la base de données (JSON), conception du système multi-rôles et interconnexions du flux de validation des commandes.
