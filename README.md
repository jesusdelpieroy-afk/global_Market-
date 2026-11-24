# Global Market · sistema (Laravel 13)

Proyecto Laravel de la tienda y del panel del encargado. Las instrucciones completas están en el `README.md` de la
carpeta superior. Resumen (Terminal de Laragon, dentro de `sistema`):

    composer install
    copy .env.example .env          (solo si no existe .env)
    php artisan key:generate        (solo si creaste el .env)
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS global_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    php artisan migrate:fresh --seed
    php artisan storage:link
    php artisan serve

* Tienda: http://127.0.0.1:8000
* Panel: http://127.0.0.1:8000/admin/login  →  `admin@globalmarket.com` / `123456`
* Scripts SQL alternativos: `database/sql/` (ver el README principal).
