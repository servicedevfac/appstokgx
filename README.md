<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</p>

<p align="center">
    <a href="https://github.com/laravel/framework/actions">
        <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
    </a>
</p>

# Gestion Stock StockGX

Gestion Stock StockGX est une application web basée sur le framework Laravel, conçue pour faciliter la gestion des stocks de votre entreprise. Elle offre une interface intuitive et des fonctionnalités puissantes pour suivre, organiser et optimiser vos inventaires.

## Fonctionnalités principales

- Gestion des produits et des catégories
- Suivi des entrées et sorties de stock
- Alertes de seuil de stock
- Historique des mouvements
- Rapports personnalisés

## Prérequis

- PHP >= 8.1
- Composer
- MySQL ou autre base de données compatible
- Node.js & npm (pour la gestion des assets)

## Installation

1. Clonez ce dépôt :
     ```bash
     git clone <url-du-repo>
     cd gestion-stock-StockGX
     ```
2. Installez les dépendances PHP :
     ```bash
     composer install
     ```
3. Installez les dépendances front-end :
     ```bash
     npm install && npm run dev
     ```
4. Configurez votre fichier `.env` puis générez la clé d’application :
     ```bash
     cp .env.example .env
     php artisan key:generate
     ```
5. Lancez les migrations :
     ```bash
     php artisan migrate
     ```
6. Démarrez le serveur :
     ```bash
     php artisan serve
     ```
    ## Ajout des Seeders

    Pour initialiser la base de données avec des données de test, vous pouvez utiliser les seeders Laravel :

    1. Exécutez les seeders après les migrations :
        ```bash
        php artisan db:seed
        ```

    

    Les seeders se trouvent dans le dossier `database/seeders`. Modifiez-les selon vos besoins pour ajouter des données par défaut.
## Documentation

Pour plus d’informations sur l’utilisation de Laravel, consultez la [documentation officielle](https://laravel.com/docs).

## Contribution

Les contributions sont les bienvenues ! Merci de consulter le guide de contribution dans la [documentation Laravel](https://laravel.com/docs/contributions).

## Sécurité

Si vous découvrez une faille de sécurité, veuillez contacter l’équipe via [taylor@laravel.com](mailto:taylor@laravel.com).

## Licence

Ce projet est sous licence [MIT](https://opensource.org/licenses/MIT).
