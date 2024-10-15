## Plateforme E-learning Symfony
Ce projet est une plateforme e-learning développée en utilisant le framework Symfony. Elle permet aux utilisateurs de s'inscrire, d'acheter des cours et des cursus, de suivre des leçons, et d'obtenir des certifications.


## Prérequis

Avant de commencer, assurez-vous que vous avez les éléments suivants installés sur votre machine :

PHP 8.1 ou supérieur
Composer
Symfony CLI
MySQL ou tout autre système de gestion de base de données supporté par Symfony
Node.js et npm (pour la gestion des assets)
Extensions PHP requises : dom, json, libxml, mbstring, tokenizer, xml, xmlwriter, intl


## Installation

1. Cloner le projet depuis le dépôt GitHub :
git clone https://github.com/Aliiixxx/Devoir-KNOWLEDGE-LEARNING.git
cd votre-projet
2. Installer les dépendances du projet avec Composer :
composer install
composer update
3. Configurer votre base de données dans le fichier .env :
Mettez à jour la section DATABASE_URL dans votre fichier .env avec les informations de connexion à votre base de données :
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
4. Créer la base de données et exécuter les migrations :
symfony console make:migration
symfony console doctrine:migrations:migrate (ignorable si erreur)
symfony console doctrine:fixtures:load
5. Démarrer le serveur de développement Symfony :
symfony serve -d
6. Accéder à l'application :
Ouvrez votre navigateur et rendez-vous sur http://localhost:8000.


## Utilisation

Fonctionnalités Principales
Inscription et Connexion : Les utilisateurs peuvent s'inscrire et se connecter à la plateforme.
Gestion des Cours et Cursus : Les utilisateurs peuvent acheter des leçons individuelles ou des cursus complets.
Progression des Leçons : Les utilisateurs peuvent suivre la progression de leurs leçons.
Certification : Les utilisateurs peuvent obtenir des certifications après avoir terminé un cursus.
Panier et Paiement : Les utilisateurs peuvent ajouter des leçons ou des cursus à leur panier et procéder au paiement en utilisant Stripe.
Administration : Les administrateurs peuvent gérer les utilisateurs, les cours, les cursus, et les achats.