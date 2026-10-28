#!/bin/bash

# Chemin vers le projet
PROJECT_PATH="/Volumes/Sans titre/app/gestion-stock-StockGX"

# Chemin vers PHP
PHP_PATH="/Users/palmer/.config/herd-lite/bin/php"

# Se déplacer dans le répertoire du projet
cd "$PROJECT_PATH" || exit 1

# Charger les variables d'environnement si un fichier .env existe
if [ -f "$PROJECT_PATH/.env" ]; then
    # Exporter les variables d'environnement du fichier .env
    export $(grep -v '^#' "$PROJECT_PATH/.env" | xargs)
fi

# Exécuter la commande schedule:run avec l'environnement complet
"$PHP_PATH" artisan schedule:run >> "$PROJECT_PATH/storage/logs/scheduler.log" 2>&1

# Ajouter un timestamp pour le suivi
echo "Cron exécuté le $(date)" >> "$PROJECT_PATH/storage/logs/scheduler.log"

# Le script se termine ici