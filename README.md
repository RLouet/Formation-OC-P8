# Formation Développeur PHP / Symfony
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/c97b12733e3244aea0bad56e468600b3)](https://www.codacy.com/gh/RLouet/Formation-OC-P8/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=RLouet/Formation-OC-P8&amp;utm_campaign=Badge_Grade)

## Projet 8 : ToDo & Co

### Introduction
Projet 8 de la formation **OpenClassrooms** [*Développeur d'application PHP / Symfony*](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony) :

#### Améliorez une application existante de ToDo & Co

Vous pouvez voir la démo du projet [ici](https://todo-and-co.romainlouet.fr/)

### Installation

#### Prérequis
*   Version minimum de PHP : 8.0
*   Extensions PHP : Sodium
*   Git
*   Composer

#### Copie du projet
`git clone https://github.com/RLouet/Formation-OC-P8.git`

#### Installation des dépendances
`composer install --optimize-autoloader`

#### Configuration

##### Configuration du .env
Modifier le fichier .env avec vos informations et passer le projet en dev.
*   Application en dev

    `APP_ENV=dev`

*   Configuration de la base de données

    `DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"`

##### Création de la bdd
`php bin/console doctrine:database:create`

*(Ou création de la base manuellement)*

##### Création des tables

`php bin/console doctrine:migrations:migrate`

##### Création des données
`php bin/console doctrine:fixtures:load`

#### Utilisation
Par défaut, les comptes sont les suivants :

> Administrateur : admin
> 
> Utilisateur : user
>
> Mot de passe : password

**Ces comptes sont destinés à une utilisation en local (les adresses email ne sont pas valides et le mot de passe non conforme à une utilisation en production), pensez à les supprimer ou modifier pour une utilisation en production.**

##### Utilisation en local
*Prérequis : symfony, php et un SGBD installés*
> https://symfony.com/download
*   Démarrer le serveur vote SGBD préféré.

*   Démarrer le serveur local :
`symfony server:start`

*   Le site est accessible à l’adresse <localhost:8000>

##### Utilisation en production
*   Modifier l’environnement de l’application dans le .env :

    `APP_ENV=prod`

*   Améliorer les performances du .env :

    `composer dump-env prod`

*   Mettre à jour les dépendances pour l’environnement :

    `composer install --no-dev --optimize-autoloader`

*   Vidage du cache :

    `APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear`

*   Configurer le domaine pour qu’il pointe vers le dossier */public*

### Tests

#### Phan

`php vendor/bin/phan --allow-polyfill-parser`

#### Php CS Fixer

`composer phpcsfixer` 

#### PhpUnit

##### Initialisation
```console
symfony console doctrine:database:drop --force --env=test
symfony console doctrine:database:create --env=test
symfony console doctrine:migrations:migrate -n --env=test
symfony console doctrine:fixtures:load -n --env=test
php bin/phpunit
```

##### Commandes

Lancer les tests : `php bin/phpunit`

Générer le fichier de couverture des tests : `php bin/phpunit --coverage-html tests/code-coverage`

Voir la couverture des tests (text) : `php bin/phpunit --coverage-text`

La couverture des tests est disponible ici : *tests/code-coverage/index.html*