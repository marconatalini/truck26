#!/bin/bash

# Configurazione delle variabili d'ambiente
export SERVER_NAME="truck26.natalinitrasporti.it"
export APP_SECRET="affe8085cd8d3b605e521b0d3ad945d0"
export CADDY_MERCURE_JWT_SECRET="7c8d2f5a9e3b1c4d0e6f8a7b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d"

# Stampa un messaggio di avvio
echo "Avvio di Docker Compose in modalità di produzione per ${SERVER_NAME}..."

# Esecuzione del comando Docker Compose
docker compose -f compose.yaml -f compose.prod.yaml up --wait

# Verifica se il comando è andato a buon fine
if [ $? -eq 0 ]; then
    echo "🚀 I servizi sono stati avviati correttamente e sono pronti!"
else
    echo "❌ Si è verificato un errore durante l'avvio dei servizi."
    exit 1
fi
