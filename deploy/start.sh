#!/bin/sh
# Arranque en el hosting: prepara la base MySQL y enciende el servidor.
cd /app
export APP_ENV="${APP_ENV:-production}"
php artisan config:clear
php artisan storage:link 2>/dev/null || true

if [ "$RUN_SEED" = "true" ]; then
  # PRIMER DESPLIEGUE: crea las tablas y carga categorías, productos y usuarios de prueba (borra lo que haya)
  php artisan migrate:fresh --seed --force
else
  # Siguientes despliegues: solo aplica migraciones nuevas y conserva los datos
  php artisan migrate --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
