# Publicar Global Market en línea (tienda + panel del encargado, con MySQL)

Un solo servidor sirve las dos partes con **la misma base MySQL**:

| Qué | URL (ejemplo) |
|-----|---------------|
| Tienda pública (todos la ven) | `https://tu-app.up.railway.app/` |
| Panel del encargado (editas todo) | `https://tu-app.up.railway.app/admin/login` |

Lo que edites en el panel se ve al instante en la tienda, porque ambos leen MySQL.

## Opción recomendada: Railway (gratis con crédito de prueba)

1. Sube el proyecto a GitHub (el repositorio debe tener `Dockerfile` en la raíz).
2. Entra a https://railway.com con tu cuenta de GitHub → **New Project** → **Deploy from GitHub repo** → elige tu repositorio.
3. En el mismo proyecto: **+ New** → **Database** → **Add MySQL**.
4. Abre el servicio de tu app → **Variables** → agrega:

| Variable | Valor |
|----------|-------|
| `DB_CONNECTION` | `mysql` |
| `DB_URL` | `${{MySQL.MYSQL_URL}}`  (si tu servicio MySQL tiene otro nombre, cámbialo) |
| `APP_KEY` | una clave (ver abajo) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_LOCALE` | `es` |
| `SESSION_DRIVER` | `database` |
| `RUN_SEED` | `true`  (solo el primer despliegue) |

   `APP_KEY`: en tu PC, dentro de `sistema`, ejecuta `php artisan key:generate --show` y copia el resultado (`base64:...`).
5. App → **Settings → Networking → Generate Domain**. Copia la URL y agrégala como variable `APP_URL` (con `https://`).
6. Cuando el despliegue termine (Deployments en verde), **cambia `RUN_SEED` a `false` o bórrala**: si no, cada despliegue
   borraría los datos y volvería a cargar los de prueba.
7. Abre `https://TU-URL/` (tienda) y `https://TU-URL/admin/login` (panel, `admin@globalmarket.com` / `123456`).
   Cambia esa contraseña en el panel.

## Limitaciones
* Las fotos que suba el encargado desde el panel se guardan en el disco del servidor y se pierden al volver a desplegar
  (Railway usa disco temporal). Las fotos del catálogo inicial están en el repositorio (`public/uploads/productos`) y no se pierden.
  Para fotos permanentes añade un *Volume* en Railway montado en `/app/storage/app/public`.
* El crédito gratis de Railway es limitado; para la exposición alcanza de sobra.

## Que salga en Google
Con la URL pública: https://search.google.com/search-console → agrega la URL → verifícala → *Inspección de URL* →
*Solicitar indexación*. Google tarda días en mostrarla.

## Alternativa sin Docker
Hostings PHP gratuitos (InfinityFree, etc.) no tienen terminal ni Composer: tendrías que subir `vendor/` por FTP e importar
`database/sql/global_market_completo.sql` con phpMyAdmin. Es más lento y frágil; usa Railway.
