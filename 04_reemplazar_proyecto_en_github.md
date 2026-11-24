# Reemplazar tu proyecto anterior por este ZIP y seguir trabajando

## Qué archivos de entorno hay y cuál va a GitHub

| Archivo | Ubicación | ¿Se sube a GitHub? | Para qué sirve |
|---------|-----------|--------------------|----------------|
| `.env.example` | `sistema/` | **Sí** | Plantilla del equipo, ya configurada para MySQL. `APP_KEY` vacío, sin contraseñas. |
| `.env` | `sistema/` | **No** (lo ignora `sistema/.gitignore`) | Tu configuración local. Ya viene listo con MySQL de Laragon y un `APP_KEY` generado. |

Conexión MySQL configurada (Laragon): `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=global_market`,
`DB_USERNAME=root`, `DB_PASSWORD=` (vacío).

## Pasos para reemplazar el proyecto anterior

1. **Cierra VS Code** y detén `php artisan serve` (Ctrl + C).
2. En **GitHub Desktop**: *Current repository* → tu repositorio → **Fetch origin** y, si aparece, **Pull origin**.
   Anota la carpeta local (Repository → *Show in Explorer*), normalmente `C:\laragon\www\NombreDelProyecto`.
3. En esa carpeta **borra todo menos la carpeta oculta `.git`** (no la toques: es la historia del repositorio).
   Si tenías cosas de la guía 10 (`docs/`, `prototipo/`, `sistema/`), también se reemplazan.
4. Descomprime `global-market-laravel.zip` y **copia el CONTENIDO de la carpeta `global-market`**
   (`docs`, `prototipo`, `sistema`, `README.md`, `.gitignore`) dentro de la carpeta del repositorio.
   Debe quedar `NombreDelProyecto\sistema\artisan`, no `NombreDelProyecto\global-market\sistema\...`.
5. En **GitHub Desktop → Changes** revisa la lista. Deben aparecer `sistema/...`, `prototipo/...` y `docs/...`.
   **No** deben aparecer `sistema/.env`, `sistema/vendor/` ni imágenes de `sistema/storage/app/public/`.
   Si aparece `.env`, no hagas commit y revisa `sistema/.gitignore`.
6. Commit con mensaje, por ejemplo `feat: Global Market en Laravel 13 con MySQL, migraciones y CRUD`, y **Push origin**.
7. Levanta el proyecto en tu PC (terminal de Laragon):
   ```
   cd C:\laragon\www\NombreDelProyecto\sistema
   composer install
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS global_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   php artisan config:clear
   php artisan migrate:fresh --seed
   php artisan serve
   ```
   El `.env` ya viene incluido; si no lo tienes (por ejemplo, clonaste el repositorio en otra PC):
   `copy .env.example .env` y luego `php artisan key:generate`.
8. Abre `code .` dentro de `sistema` y sigue modificando. Después de cada cambio: commit → Pull origin → Push origin.

## En otra PC (laboratorio congelado)

Repite solo: clonar → `composer install` → `copy .env.example .env` → `php artisan key:generate` →
crear la base → `php artisan migrate:fresh --seed` → `php artisan serve`. Nada más se pierde: todo está en GitHub.

## Importante sobre GitHub Pages

**Actualización:** el ZIP incluye `index.html` (catálogo) y `admin.html` (encargado) en la raíz, con categorías y fotos de `js/catalogo.js`. En GitHub:
*Settings → Pages → Branch `main` / carpeta `/ (root)`*. Para el sistema con MySQL se usa Laragon (o un hosting con PHP + MySQL).

(Nota histórica) El enlace anterior (`jesusdelpieroy-afk.github.io/UPSJB_Proyect_GlobalMarket-`) publicaba el HTML estático desde la
raíz del repositorio. Laravel necesita PHP y MySQL, así que **no puede ejecutarse en GitHub Pages**. Al mover los
archivos a `prototipo/`, ese enlace dejará de mostrar la tienda. Si necesitas conservarlo, deja una copia de
`index.html`, `admin.html`, `css/`, `js/` e `img/` en la raíz del repositorio (o publica desde `/docs`).
El sistema nuevo se ejecuta solo en Laragon (o en un hosting con PHP 8.3 y MySQL).
