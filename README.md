# Global Market · Tienda + Panel del encargado

Proyecto del curso **Ingeniería de Software · UPSJB 2026-II** (Laravel 13 · PHP 8.3 · MySQL · Blade · Bootstrap 5).

Sistema web con dos partes que comparten la misma base de datos:

* **Tienda pública** (`/`): catálogo con efecto 3D, buscador, promociones, carrito, asistente virtual con preguntas
  frecuentes y envío del pedido por WhatsApp (el pedido queda guardado como venta *pendiente*).
* **Panel del encargado** (`/admin`): dashboard y CRUD de todos los módulos (categorías, productos y stock,
  promociones, clientes, roles, usuarios, preguntas frecuentes, pedidos y ventas, portada y ajustes).

Implementa las guías **S10** (Laragon + Laravel + GitHub), **S11** (migraciones y Eloquent), **S12** (plantilla Blade y
CRUD de categorías) y **S13** (CRUD de todos los módulos).

---

## 1. Contenido de la entrega

```
global-market/
├── README.md                 Este archivo (instalación, configuración, ejecución, accesos)
├── docs/                     Diagrama de clases, niveles de dependencia, verificación (guía 13), notas de GitHub
├── index.html                CATÁLOGO público (GitHub Pages): categorías, productos con foto, buscador, carrito, WhatsApp
├── admin.html                PANEL DEL ENCARGADO (GitHub Pages): edita productos, categorías, promociones, portada y ajustes
├── css/ js/ img/             Recursos de las dos páginas (js/catalogo.js = catálogo inicial, img/productos = fotos)
└── sistema/                  Proyecto Laravel (código fuente completo)
    ├── app/                  Modelos, controladores, helper de imágenes
    ├── bootstrap/ config/    Configuración de Laravel (zona horaria America/Lima, idioma es)
    ├── database/
    │   ├── migrations/       11 migraciones del sistema + las 3 base de Laravel
    │   ├── seeders/          DatabaseSeeder + data/datos_prueba.php (datos de prueba)
    │   └── sql/              Scripts SQL (ver sección 4)
    ├── lang/es/              Mensajes de validación en español
    ├── public/               Recursos: css, js, img (logo, fotos del local), index.php
    ├── resources/views/      Frontend Blade: plantilla padre, parciales, tienda y vistas de cada módulo
    ├── routes/web.php        Rutas de la tienda y del panel
    ├── storage/              Imágenes subidas (storage/app/public)
    ├── .env                  Configuración lista para Laragon (MySQL, root sin contraseña)
    └── .env.example          Plantilla de configuración
```

**Frontend**: vistas Blade (`resources/views`), CSS/JS/iconos locales en `public/` (no depende de CDN, salvo la
fuente Montserrat de Google Fonts, que es opcional). **Backend**: controladores, modelos Eloquent, migraciones y
validaciones de Laravel sobre **MySQL**.

---

## 2. Requisitos

| Necesitas | Detalle |
|-----------|---------|
| **Laragon Full** (recomendado) | Incluye Apache, MySQL, PHP 8.3 y Composer 2. Detén XAMPP antes (usa los mismos puertos 80/3306). |
| o bien | PHP **8.3+** (extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`), Composer 2 y MySQL 8 / MariaDB 10.4+ |
| Internet | Solo para `composer install` (descarga `vendor/`, que no viene en el ZIP). |

Todos los comandos se escriben en la **Terminal de Laragon** (en PowerShell se usaría el PHP de XAMPP y Composer fallaría).

---

## 3. Instalación, configuración y ejecución (Laragon)

### Paso 1. Copiar el proyecto
Descomprime el ZIP en `C:\laragon\www\` para que quede `C:\laragon\www\global-market`.
Abre Laragon → **Start All** (Apache y MySQL deben mostrar su puerto) → botón **Terminal**.

### Paso 2. Instalar las librerías
```
cd C:\laragon\www\global-market\sistema
composer install
```
Tarda de 2 a 5 minutos y crea la carpeta `vendor/`.

### Paso 3. Configurar el `.env`
El ZIP ya incluye `sistema\.env` listo (con `APP_KEY`, `APP_LOCALE=es` y la conexión a MySQL de Laragon). Si no lo
tienes o lo borraste:
```
copy .env.example .env
php artisan key:generate
```
Conexión a la base de datos (ya configurada):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=global_market
DB_USERNAME=root
DB_PASSWORD=
```
Si tu MySQL tiene contraseña, escríbela en `DB_PASSWORD`. Luego: `php artisan config:clear`.

### Paso 4. Crear la base de datos con datos de prueba
Elige **una** de las dos opciones (la opción A es la que enseñan las guías).

**Opción A · Migraciones + seeder (recomendada)**
```
mysql -u root -e "CREATE DATABASE IF NOT EXISTS global_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate:fresh --seed
```

**Opción B · Scripts SQL** (sin usar artisan; ver sección 4)
```
mysql -u root < database\sql\global_market_completo.sql
```

### Paso 5. Enlazar las imágenes y encender el servidor
```
php artisan storage:link
php artisan serve
```
`storage:link` crea `public/storage` (acceso directo a `storage/app/public`); sin él las imágenes que subas no se ven
(404). Si responde *already exists*, no es un error. **Deja abierta la pestaña del servidor.**

### Paso 6. Abrir el sistema

| Qué | URL |
|-----|-----|
| Tienda pública | http://127.0.0.1:8000 |
| Panel del encargado | http://127.0.0.1:8000/admin/login |

**Usuarios de prueba** (contraseña de todos: `123456`):

| Correo | Rol |
|--------|-----|
| `admin@globalmarket.com` | Administrador |
| `encargado@globalmarket.com` | Encargado |
| `vendedor@globalmarket.com` | Vendedor |

Cambia la contraseña en *Configuraciones › Ajustes › Cuenta de acceso*, o desde *Usuarios*.

---

## 4. Base de datos y scripts SQL

Carpeta `sistema/database/sql/`:

| Archivo | Para qué sirve |
|---------|----------------|
| `01_estructura.sql` | Crea la base `global_market` y las 11 tablas del sistema (+ las internas de Laravel), con sus claves foráneas y reglas `restrict` / `cascade` / `set null`. Registra las migraciones: después de importarlo, `php artisan migrate` responde *Nothing to migrate*. **Borra** las tablas existentes. |
| `02_datos_prueba.sql` | Carga los datos de prueba (vacía las tablas antes de insertar). Ejecutar después del 01. |
| `global_market_completo.sql` | El 01 + el 02 en un solo archivo (para HeidiSQL: *Archivo › Ejecutar archivo SQL*). |
| `generar_sql.php` | Regenera el 02 y el completo desde `database/seeders/data/datos_prueba.php`: `php database/sql/generar_sql.php`. |

Esquema (niveles de dependencia de la guía 11):

```
roles ─┐                       categorias ─┐
       └─< users                          └─< productos >──┬──< detalle_ventas >── ventas >── clientes
clientes ─< ventas >─ users          productos >─< promociones (producto_promocion)       ventas >── users
ajustes (1 fila)    preguntas_frecuentes
```

* `users.rol_id → roles` y `productos.categoria_id → categorias`: **restrict** (no se borra lo que tiene hijos).
* `detalle_ventas.venta_id → ventas`: **cascade** (composición: al anular la venta se borran sus detalles).
* `detalle_ventas.producto_id → productos`: **restrict**. `ventas.cliente_id` / `user_id`: **set null**.

Los datos de prueba (8 categorías, 29 productos —17 con foto en `public/uploads/productos/`— con casos de stock bajo y agotado, 6 clientes, 3 roles, 3 usuarios,
3 promociones, 5 ventas —confirmadas, pendiente y cancelada—, ajustes y preguntas frecuentes) están en
`database/seeders/data/datos_prueba.php`. Los `stock` son el stock **actual** (ya descontadas las ventas confirmadas).

Para ver las tablas: Laragon › botón **Database** (HeidiSQL).

---

### ¿Dónde está la base de datos? (respuesta para el profesor)

* **Sistema real (`sistema/`)**: todo vive en **MySQL** (`global_market`): categorías, productos, stock, clientes, pedidos y
  ventas. No usa `localStorage` para los datos (solo el carrito de cada cliente). Si se reinicia el celular o la PC, nada se pierde.
* **Enlace de GitHub Pages** (`index.html` + `admin.html` en la raíz): páginas estáticas. Muestran el catálogo inicial
  (`js/catalogo.js`, generado desde los mismos datos que MySQL). El panel `admin.html` guarda sus cambios en el navegador
  (`localStorage`): se ven al instante en `index.html` **en ese mismo navegador**, pero no los ven otros visitantes.
  Para cambios compartidos por todos usa el sistema Laravel con MySQL (`sistema/`, ver `docs/07_publicar_en_linea.md`).
* Cuando cambies productos/categorías/fotos en `datos_prueba.php`, ejecuta en `sistema`:
  `php database/sql/generar_sql.php` (scripts SQL) y `php database/sql/generar_catalogo_web.php` (página de GitHub).
* Para tener el sistema completo con MySQL **en línea** hace falta un hosting con PHP 8.3 + MySQL (Railway, Render con
  base MySQL, InfinityFree, etc.); no es posible en GitHub Pages.

## 5. Módulos del panel y reglas de negocio

Todos siguen el patrón de la guía 12: `Route::resource`, controlador de recursos, vistas `index`/`create`/`edit` y un
formulario compartido `form.blade.php`, con `@csrf`, `@method`, validación, `old()`, `@error`, mensajes *flash* y el
patrón PRG (guardar → redirigir → mostrar).

| Módulo | Ruta | Lo particular (guía 13) |
|--------|------|-------------------------|
| Dashboard | `/admin` | Indicadores, pedidos recientes y productos con poco stock |
| Roles | `/admin/roles` | CRUD simple. **No se elimina si tiene usuarios.** |
| Usuarios | `/admin/usuarios` | `<select>` de rol + `exists`; contraseña con `confirmed` y cast `hashed`; al editar, contraseña vacía = se conserva. **No se elimina a uno mismo ni a quien registró ventas.** |
| Categorías | `/admin/categorias` | `unique` al crear y `Rule::unique()->ignore()` al editar. **No se elimina si tiene productos.** |
| Clientes | `/admin/clientes` | DNI de 8 dígitos, único (opcional). **No se elimina si tiene pedidos o ventas.** |
| Productos y stock | `/admin/productos` | Imagen en el disco `public` (`storage:link`), reemplazo/borrado del archivo anterior, `with('categoria')` (sin N+1) y **filtros combinables con `when()`**: nombre, categoría y *stock bajo* (por GET, se conservan en la URL). **No se elimina si figura en ventas.** |
| Promociones | `/admin/promociones` | Relación muchos a muchos con productos (tabla pivote) |
| Preguntas frecuentes | `/admin/preguntas` | Alimentan la tienda y el asistente virtual |
| Pedidos y ventas | `/admin/ventas` | **Nueva venta** con detalle dinámico (JavaScript), `DB::transaction()`, `lockForUpdate()`, validación de stock, precio leído de la base de datos (no del formulario) e **IGV 18 % incluido** (`total × 18 / 118`). Fecha en hora de Perú. No se edita: se **anula** (devuelve el stock) y se registra otra. Los pedidos de la tienda llegan *pendientes* y se confirman/cancelan desde el detalle. |
| Portada y Ajustes | `/admin/portada`, `/admin/ajustes` | Textos, fotos, WhatsApp, moneda, cuenta de acceso |

Otros ajustes de la guía 13: `APP_LOCALE=es` + `lang/es/validation.php` (mensajes con nombres legibles de campos),
`'timezone' => 'America/Lima'` en `config/app.php`, menú lateral con `route()` y opción activa, y parcial
`partials/alertas.blade.php` para los mensajes `ok` y `error`.

### Cómo viaja una petición
`Navegador → routes/web.php → Controlador → Modelo (MySQL) → Vista Blade → HTML`.

---

## 6. Verificación (guía 13, Paso 16)

La lista completa para marcar está en [`docs/05_verificacion_guia13.md`](docs/05_verificacion_guia13.md). Resumen:
validaciones en español; editar sin cambiar nombre/DNI; editar usuario sin contraseña; reemplazar imagen; filtros
combinados; vender más del stock (mensaje, no se crea la venta); venta válida (IGV, stock, hora de Perú); anular venta
(stock devuelto); reglas de borrado con mensaje rojo (nunca el error 1451); navegación por el menú.

Para empezar de cero en cualquier momento: `php artisan migrate:fresh --seed`.

---

## 7. Problemas frecuentes

| Lo que ves | Qué hacer |
|------------|-----------|
| `Port 80/3306 in use` | Detén XAMPP. |
| `php -v` muestra 8.0 | Estás en PowerShell: usa la Terminal de Laragon. |
| `Your requirements could not be resolved` | Composer corrió con otro PHP: repite en la Terminal de Laragon. |
| `vendor/autoload.php: Failed to open stream` | `composer install` no terminó: repítelo dentro de `sistema`. |
| `No application encryption key has been specified` | `php artisan key:generate` y recarga. |
| `Unknown database 'global_market'` | Crea la base (Paso 4) y vuelve a migrar. |
| `Base table or view already exists` | `php artisan migrate:fresh --seed` (borra y recrea). |
| Las imágenes subidas salen rotas | `php artisan storage:link`. |
| La imagen no llega al controlador | El `<form>` debe llevar `enctype="multipart/form-data"` (ya lo llevan). |
| `419 Page Expired` / `405 Method Not Allowed` | Falta `@csrf` / `@method('PUT'/'DELETE')` en un formulario nuevo. |
| Mensajes en inglés | `APP_LOCALE=es` en `.env` y `php artisan config:clear`. |
| `Address already in use` | Otro `serve` está abierto: `php artisan serve --port=8001`. |

---

## 8. GitHub Desktop (guías 10 a 13)

Un commit por avance, por ejemplo: `feat(crud): ventas con transacción e IGV`. Nunca subas `vendor/`, `.env` ni las
imágenes de `storage/` (el `.gitignore` ya lo evita).

## 9. Notas de diseño

* **Nombres en español** (convención de la guía 11): `Rol` y `Promocion` llevan `#[Table('roles')]` / `#[Table('promociones')]`
  porque Laravel los pluraliza mal; las rutas terminadas en consonante usan `->parameters([...])`.
* La guía 13 usa nombres en inglés (`medicines`, `sales`…) para el caso de la farmacia; este proyecto aplica **los mismos
  patrones** con las tablas de Global Market (`productos`, `ventas`…), como pide el Paso 17 de la guía.
* La tienda es la vista raíz `/`; el dashboard es la vista raíz del panel (`dashboard.index`).
* Stock bajo = 5 unidades o menos (`Producto::STOCK_BAJO`).
* La columna `imagen` de `productos` nació en su migración original; no hizo falta una migración adicional como en la
  farmacia (donde la imagen se agregó después).
* Una venta registrada es un comprobante: no tiene botón *Editar*; el único cambio permitido a un pedido de la tienda es
  su estado (pendiente → confirmado / cancelado).
