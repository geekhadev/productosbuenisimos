#!/bin/bash

# Inicia el túnel ngrok para exponer el servidor Laravel en internet
# Uso: ./scripts/tunnel.sh [dominio-estatico]
# Ejemplo: ./scripts/tunnel.sh mi-app.ngrok-free.app

PORT=8000
DOMAIN=${1:-""}

# Verificar que ngrok esté instalado
if ! command -v ngrok &> /dev/null; then
    echo "Error: ngrok no está instalado. Instálalo con: brew install ngrok"
    exit 1
fi

# Verificar que el servidor Laravel esté corriendo
if ! lsof -i :$PORT -sTCP:LISTEN &> /dev/null; then
    echo "Advertencia: no se detectó un servidor corriendo en el puerto $PORT."
    echo "Asegúrate de tener 'composer run dev' activo en otra terminal."
    echo ""
fi

# Iniciar ngrok
if [ -n "$DOMAIN" ]; then
    echo "Iniciando túnel en dominio estático: $DOMAIN"
    ngrok http $PORT --url=$DOMAIN
else
    echo "Iniciando túnel (URL temporal)..."
    ngrok http $PORT
fi
