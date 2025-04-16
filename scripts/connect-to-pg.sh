#!/bin/bash

# Database connection details
DB_NAME="trading_journal"
DB_USER="admin"
CONTAINER_NAME="postgres"

# Function to run query
run_query() {
    local query="$1"
    docker-compose exec $CONTAINER_NAME psql -U $DB_USER -d $DB_NAME -c "$query"
}

# Interactive menu
while true; do
    clear
    echo "=============================="
    echo " PostgreSQL Query Runner Menu "
    echo "=============================="
    echo "1. Show all trades"
    echo "2. Run custom query"
    echo "3. Exit"
    echo "=============================="
    
    read -p "Enter your choice [1-3]: " choice
    
    case $choice in
        1)
            run_query "SELECT * FROM trades;"
            ;;
        2)
            read -p "Enter your SQL query: " custom_query
            run_query "$custom_query"
            ;;
        3)
            echo "Exiting..."
            exit 0
            ;;
        *)
            echo "Invalid option, please try again."
            ;;
    esac
    
    read -p "Press [Enter] to continue..."
done