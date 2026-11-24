-- Global Market · script completo (estructura + datos de prueba) en un solo archivo.
-- Importar con HeidiSQL o: mysql -u root < database/sql/global_market_completo.sql

-- =============================================================================
-- Global Market · 01_estructura.sql
-- Crea la base de datos `global_market` y todas sus tablas (MySQL 8 / MariaDB 10.4+).
-- Equivale a ejecutar `php artisan migrate`: incluye también las tablas internas de
-- Laravel y registra las migraciones, de modo que `php artisan migrate` después de
-- importar este script responde «Nothing to migrate».
--
-- ATENCIÓN: borra las tablas existentes de global_market antes de crearlas.
-- Importar: HeidiSQL (Archivo > Ejecutar archivo SQL) o
--           mysql -u root < database/sql/01_estructura.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `global_market` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `global_market`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `detalle_ventas`, `ventas`, `producto_promocion`, `promociones`, `productos`,
  `categorias`, `clientes`, `users`, `roles`, `ajustes`, `metodos_pago`, `preguntas_frecuentes`,
  `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`,
  `failed_jobs`, `migrations`;

-- ---------------------------------------------------------------- Nivel 1 (sin claves foráneas)
CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(191) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categorias` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(80) NOT NULL,
  `descripcion` VARCHAR(191) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clientes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NOT NULL,
  `dni` CHAR(8) NULL,
  `telefono` VARCHAR(15) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_dni_unique` (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promociones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(60) NOT NULL,
  `descripcion` VARCHAR(160) NULL,
  `porcentaje` TINYINT UNSIGNED NOT NULL,
  `aplica_todos` TINYINT(1) NOT NULL DEFAULT 1,
  `vence` DATE NULL,
  `imagen` VARCHAR(191) NULL,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ajustes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(80) NOT NULL,
  `descripcion_corta` VARCHAR(60) NULL,
  `whatsapp` VARCHAR(20) NOT NULL,
  `moneda` VARCHAR(4) NOT NULL DEFAULT 'S/',
  `direccion` VARCHAR(120) NULL,
  `horario` VARCHAR(100) NULL,
  `portada_titulo` VARCHAR(80) NOT NULL,
  `portada_texto` VARCHAR(260) NULL,
  `portada_imagen1` VARCHAR(191) NULL,
  `portada_imagen2` VARCHAR(191) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medios de pago que ve el cliente (QR de Yape/Plin, cuentas BCP...)
CREATE TABLE `metodos_pago` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(60) NOT NULL,
  `titular` VARCHAR(100) NULL,
  `numero` VARCHAR(40) NULL,
  `cci` VARCHAR(40) NULL,
  `instrucciones` VARCHAR(191) NULL,
  `imagen` VARCHAR(191) NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `orden` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `preguntas_frecuentes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pregunta` VARCHAR(200) NOT NULL,
  `respuesta` VARCHAR(500) NOT NULL,
  `orden` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Nivel 2
-- Rol 1 — 0..* Usuario (restrict: un rol con usuarios no se puede borrar)
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rol_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `telefono` VARCHAR(15) NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(191) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  CONSTRAINT `users_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categoria 1 — 0..* Producto (restrict)
CREATE TABLE `productos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` BIGINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(120) NOT NULL,
  `descripcion` VARCHAR(200) NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `stock` INT UNSIGNED NOT NULL DEFAULT 0,
  `imagen` VARCHAR(191) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Nivel 3
-- Producto * — * Promocion (tabla pivote; cascade)
CREATE TABLE `producto_promocion` (
  `producto_id` BIGINT UNSIGNED NOT NULL,
  `promocion_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`producto_id`, `promocion_id`),
  CONSTRAINT `producto_promocion_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_promocion_promocion_id_foreign` FOREIGN KEY (`promocion_id`) REFERENCES `promociones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cliente 0..1 — * Venta, Usuario 0..1 — * Venta (set null)
CREATE TABLE `ventas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NULL,
  `cliente_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `fecha` DATETIME NOT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  `metodo_pago` VARCHAR(60) NULL,
  `codigo_operacion` VARCHAR(40) NULL,
  `comprobante` VARCHAR(191) NULL,
  `comprobante_hash` VARCHAR(64) NULL,
  `comprobante_at` DATETIME NULL,
  `entregado_at` DATETIME NULL,
  `entregado_por` BIGINT UNSIGNED NULL,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `igv` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_codigo_unique` (`codigo`),
  UNIQUE KEY `ventas_comprobante_hash_unique` (`comprobante_hash`),
  KEY `ventas_codigo_operacion_index` (`codigo_operacion`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_entregado_por_foreign` FOREIGN KEY (`entregado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Nivel 4
-- Composición Venta ◆— DetalleVenta (cascade); un producto vendido no se borra (restrict)
CREATE TABLE `detalle_ventas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id` BIGINT UNSIGNED NOT NULL,
  `producto_id` BIGINT UNSIGNED NOT NULL,
  `cantidad` INT UNSIGNED NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Tablas internas de Laravel
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(191) NOT NULL,
  `token` VARCHAR(191) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(191) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` VARCHAR(191) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(191) NOT NULL,
  `owner` VARCHAR(191) NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(191) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` SMALLINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` VARCHAR(191) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(191) NOT NULL,
  `connection` VARCHAR(191) NOT NULL,
  `queue` VARCHAR(191) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`, `queue`, `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(191) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- >>> MIGRACIONES (lo rellena generar_sql.php)
-- Registro de migraciones: `php artisan migrate` responderá «Nothing to migrate»
INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('0001_01_01_000000_create_users_table', 1),
  ('0001_01_01_000001_create_cache_table', 1),
  ('0001_01_01_000002_create_jobs_table', 1),
  ('2026_11_20_100000_create_roles_table', 1),
  ('2026_11_20_100004_create_categorias_table', 1),
  ('2026_11_20_100008_create_clientes_table', 1),
  ('2026_11_20_101200_add_rol_y_datos_to_users_table', 1),
  ('2026_11_20_101205_create_productos_table', 1),
  ('2026_11_20_101210_create_promociones_table', 1),
  ('2026_11_20_101215_create_producto_promocion_table', 1),
  ('2026_11_20_102000_create_ventas_table', 1),
  ('2026_11_20_102004_create_detalle_ventas_table', 1),
  ('2026_11_20_103000_create_ajustes_table', 1),
  ('2026_11_20_103004_create_preguntas_frecuentes_table', 1),
  ('2026_11_23_100000_create_metodos_pago_table', 1),
  ('2026_11_23_100100_add_pago_y_entrega_to_ventas_table', 1),
  ('2026_11_23_100200_add_comprobante_hash_to_ventas_table', 1),
  ('2026_11_23_100300_seed_yape_metodo_pago', 1);

-- <<< MIGRACIONES

SET FOREIGN_KEY_CHECKS = 1;

USE `global_market`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Vacía las tablas para poder volver a cargar los datos sin duplicarlos
TRUNCATE TABLE `detalle_ventas`; TRUNCATE TABLE `ventas`; TRUNCATE TABLE `producto_promocion`;
TRUNCATE TABLE `promociones`; TRUNCATE TABLE `productos`; TRUNCATE TABLE `categorias`;
TRUNCATE TABLE `clientes`; TRUNCATE TABLE `users`; TRUNCATE TABLE `roles`; TRUNCATE TABLE `ajustes`; TRUNCATE TABLE `metodos_pago`;
TRUNCATE TABLE `preguntas_frecuentes`; TRUNCATE TABLE `sessions`;

-- Roles
INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
  (1, 'Administrador', 'Acceso total al panel', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, 'Encargado', 'Gestiona productos, stock y pedidos', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, 'Vendedor', 'Registra ventas de mostrador', '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Usuarios (la contraseña se guarda cifrada con bcrypt, igual que Laravel)
INSERT INTO `users` (`id`, `rol_id`, `name`, `email`, `telefono`, `password`, `created_at`, `updated_at`) VALUES
  (1, 1, 'Administrador', 'admin@globalmarket.com', '999111222', '$2y$12$jPu4k8WNWv3PZQmJXaEqz.MhLA5QGEXqayJe0cg2eXF.Zr0uGKfvq', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, 2, 'Elena Paredes', 'encargado@globalmarket.com', '999333444', '$2y$12$jPu4k8WNWv3PZQmJXaEqz.MhLA5QGEXqayJe0cg2eXF.Zr0uGKfvq', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, 3, 'Jorge Castillo', 'vendedor@globalmarket.com', '999555666', '$2y$12$jPu4k8WNWv3PZQmJXaEqz.MhLA5QGEXqayJe0cg2eXF.Zr0uGKfvq', '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Categorías
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
  (1, 'Hogar', 'Decoración y textiles para la casa', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, 'Organizadores', 'Canastas, cajas y organizadores', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, 'Accesorios de cabello', 'Peinetas, cepillos y otros accesorios para el cabello', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (4, 'Cocina', 'Utensilios y almacenaje de cocina', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (5, 'Limpieza', 'Productos y accesorios de limpieza', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (6, 'Scrunchies', 'Colas de cabello de tela: algodón, terciopelo, satén y con cinta', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (7, 'Diademas y moños', 'Diademas de tela, moños y balacas', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (8, 'Vasos y tazas', 'Vasos de plástico y vidrio, tazas de cerámica', '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Clientes
INSERT INTO `clientes` (`id`, `nombre`, `dni`, `telefono`, `created_at`, `updated_at`) VALUES
  (1, 'Rosa Quispe Mamani', '45871236', '987654321', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, 'Luis Ramos Huamán', '70231458', '986112233', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, 'María Torres Salas', '41587963', '955443322', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (4, 'Carlos Medina Paredes', '72904415', '944001122', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (5, 'Ana Flores Ccori', NULL, '933221100', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (6, 'Julia Vargas Rojas', '46120987', NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Productos (stock actual: ya descontadas las ventas confirmadas)
INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`, `created_at`, `updated_at`) VALUES
  (1, 2, 'Canasta organizadora con tapa', 'Ideal para ordenar ropa, juguetes o utensilios.', 24.90, 10, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, 1, 'Cesto de ropa tejido', 'Cesto ventilado, resistente y fácil de limpiar.', 39.90, 7, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, 6, 'Set de scrunchies de colores', 'Pack de 6 colores, suaves con el cabello.', 9.90, 27, 'uploads/productos/scrunchies-peluche-x6.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (4, 3, 'Diadema de spa', 'Para el cuidado facial y el maquillaje.', 7.50, 4, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (5, 2, 'Organizador de cajones x6', 'Seis divisores ajustables para cajones.', 18.50, 15, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (6, 2, 'Caja multiusos apilable', 'Caja resistente que se apila para ahorrar espacio.', 32.00, 3, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (7, 4, 'Juego de toallas de cocina x3', 'Algodón absorbente, tres diseños.', 14.90, 17, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (8, 4, 'Set de tapers herméticos x5', 'Aptos para microondas y congelador.', 29.90, 8, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (9, 4, 'Escurridor de platos plegable', 'Se pliega para guardarlo fácilmente.', 35.00, 0, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (10, 1, 'Espejo decorativo redondo', 'Marco metálico dorado de 40 cm.', 59.90, 6, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (11, 1, 'Cojín decorativo', 'Funda lavable con relleno suave.', 22.00, 14, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (12, 3, 'Peineta y cepillo desenredante', 'Para cabello seco o mojado.', 12.50, 5, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (13, 5, 'Limpiador multiuso 1 L', 'Aroma cítrico, para todas las superficies.', 8.90, 38, 'uploads/productos/atomizador-azul.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (14, 5, 'Set de esponjas x6', 'Esponjas de doble cara.', 6.50, 2, NULL, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (15, 6, 'Scrunchie de algodón crema', 'Algodón suave y elástico en color crema.', 6.90, 20, 'uploads/productos/scrunchie-crema.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (16, 6, 'Scrunchie de terciopelo rojo', 'Terciopelo suave que cuida el cabello.', 7.50, 18, 'uploads/productos/scrunchie-terciopelo-rojo.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (17, 6, 'Scrunchie de satén negro', 'Satén sedoso, ideal para el uso diario y para dormir.', 8.50, 15, 'uploads/productos/scrunchie-saten-negro.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (18, 6, 'Scrunchie con cinta roja', 'Con cinta larga para lucirlo como lazo.', 9.90, 12, 'uploads/productos/scrunchie-cinta-roja.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (19, 6, 'Scrunchie con cinta animal print', 'Cinta estampada de leopardo, se puede usar de lazo.', 10.90, 9, 'uploads/productos/scrunchie-animal-print.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (20, 7, 'Diadema de tela con nudo rosa', 'Diadema acolchada con nudo al frente.', 12.90, 11, 'uploads/productos/diadema-nudo-rosa.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (21, 7, 'Diadema estampado paisley rojo', 'Diadema ancha de tela con estampado paisley.', 9.90, 14, 'uploads/productos/diadema-paisley-rojo.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (22, 7, 'Moño de cinta rosa con lunares', 'Moño de cinta tipo grosgrain con lunares blancos.', 6.50, 25, 'uploads/productos/mono-lunares-rosa.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (23, 7, 'Balaca con moño fucsia', 'Moño grande fucsia con banda elástica suave.', 8.90, 10, 'uploads/productos/mono-fucsia-elastico.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (24, 8, 'Set de vasos de colores x6', 'Vasos reutilizables en seis colores.', 12.90, 16, 'uploads/productos/vasos-plasticos-x6.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (25, 8, 'Taza de cerámica roja', 'Taza grande con interior blanco.', 11.90, 13, 'uploads/productos/taza-roja.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (26, 8, 'Set de vasos de vidrio x4', 'Vasos bajos de vidrio con diseño grabado.', 39.90, 6, 'uploads/productos/vasos-vidrio-x4.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (27, 5, 'Botella atomizadora rosada', 'Botella con gatillo para limpiadores o agua.', 4.90, 30, 'uploads/productos/atomizador-rosado.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (28, 5, 'Lavavajilla en pasta Sapolio 800 g', 'Lavavajilla en pasta con esponja incluida.', 7.90, 22, 'uploads/productos/lavavajilla-sapolio.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (29, 1, 'Tacho de basura con tapa vaivén', 'Tacho de plástico con tapa vaivén fácil de limpiar.', 24.90, 9, 'uploads/productos/tacho-vaiven-rojo.jpg', '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Promociones y su relación con productos
INSERT INTO `promociones` (`id`, `titulo`, `descripcion`, `porcentaje`, `aplica_todos`, `vence`, `activa`, `created_at`, `updated_at`) VALUES
  (1, 'Oferta de bienvenida', '10% de descuento en todos los productos de la tienda.', 10, 1, NULL, 1, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, 'Especial cocina', '15% de descuento en productos seleccionados de cocina.', 15, 0, '2027-01-31', 1, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, 'Liquidación de verano', '20% de descuento (campaña finalizada).', 20, 1, '2026-03-31', 0, '2026-11-23 08:00:00', '2026-11-23 08:00:00');

INSERT INTO `producto_promocion` (`producto_id`, `promocion_id`) VALUES
  (7, 2),
  (8, 2),
  (9, 2);

-- Ventas
INSERT INTO `ventas` (`id`, `cliente_id`, `user_id`, `fecha`, `estado`, `total`, `igv`, `created_at`, `updated_at`) VALUES
  (1, 1, 3, '2026-11-15 11:20:00', 'confirmado', 79.50, 12.13, '2026-11-15 11:20:00', '2026-11-15 11:20:00'),
  (2, 2, 1, '2026-11-17 16:45:00', 'confirmado', 99.70, 15.21, '2026-11-17 16:45:00', '2026-11-17 16:45:00'),
  (3, NULL, 3, '2026-11-19 09:10:00', 'confirmado', 62.50, 9.53, '2026-11-19 09:10:00', '2026-11-19 09:10:00'),
  (4, 3, NULL, '2026-11-21 18:05:00', 'pendiente', 93.51, 14.26, '2026-11-21 18:05:00', '2026-11-21 18:05:00'),
  (5, 1, NULL, '2026-11-22 12:30:00', 'cancelado', 6.75, 1.03, '2026-11-22 12:30:00', '2026-11-22 12:30:00');

-- Detalle de las ventas
INSERT INTO `detalle_ventas` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`, `created_at`, `updated_at`) VALUES
  (1, 1, 1, 2, 24.90, 49.80, '2026-11-15 11:20:00', '2026-11-15 11:20:00'),
  (2, 1, 3, 3, 9.90, 29.70, '2026-11-15 11:20:00', '2026-11-15 11:20:00'),
  (3, 2, 2, 1, 39.90, 39.90, '2026-11-17 16:45:00', '2026-11-17 16:45:00'),
  (4, 2, 8, 2, 29.90, 59.80, '2026-11-17 16:45:00', '2026-11-17 16:45:00'),
  (5, 3, 7, 3, 14.90, 44.70, '2026-11-19 09:10:00', '2026-11-19 09:10:00'),
  (6, 3, 13, 2, 8.90, 17.80, '2026-11-19 09:10:00', '2026-11-19 09:10:00'),
  (7, 4, 10, 1, 53.91, 53.91, '2026-11-21 18:05:00', '2026-11-21 18:05:00'),
  (8, 4, 11, 2, 19.80, 39.60, '2026-11-21 18:05:00', '2026-11-21 18:05:00'),
  (9, 5, 4, 1, 6.75, 6.75, '2026-11-22 12:30:00', '2026-11-22 12:30:00');

-- Ajustes de la tienda (una sola fila)
INSERT INTO `ajustes` (`id`, `nombre`, `descripcion_corta`, `whatsapp`, `moneda`, `direccion`, `horario`, `portada_titulo`, `portada_texto`, `created_at`, `updated_at`) VALUES
  (1, 'Global Market', 'Hogar, organizadores y accesorios', '51999999999', 'S/', 'Av. Principal 123, Lima', 'Lunes a sábado de 9:00 a 20:00', 'Todo para tu hogar en un solo lugar', 'Elige lo que necesitas del catálogo y haz tu pedido por WhatsApp. Confirmamos la disponibilidad y coordinamos el pago y la entrega contigo.', '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Preguntas frecuentes (alimentan la tienda y el asistente virtual)
INSERT INTO `preguntas_frecuentes` (`id`, `pregunta`, `respuesta`, `orden`, `created_at`, `updated_at`) VALUES
  (1, '¿Cómo hago mi pedido?', 'Agrega los productos al carrito y presiona «Enviar pedido por WhatsApp». Te confirmamos la disponibilidad y coordinamos contigo el pago y la entrega.', 1, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (2, '¿Qué medios de pago aceptan?', 'El pago se coordina por WhatsApp una vez confirmado tu pedido. Escríbenos y te indicamos las opciones disponibles.', 2, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (3, '¿Hacen envíos?', 'La entrega se coordina contigo por WhatsApp según tu zona.', 3, '2026-11-23 08:00:00', '2026-11-23 08:00:00'),
  (4, '¿Los productos tienen stock?', 'El catálogo muestra el stock disponible de cada producto. Si algo aparece agotado, escríbenos y te avisamos cuando vuelva a haber.', 4, '2026-11-23 08:00:00', '2026-11-23 08:00:00');

-- Método de pago: QR de Yape (public/uploads/pagos/qr-yape.png); se edita desde el panel
INSERT INTO `metodos_pago` (`id`, `nombre`, `instrucciones`, `imagen`, `activo`, `orden`, `created_at`, `updated_at`) VALUES
  (1, 'Yape', 'Escanea el QR con Yape y paga el total de tu pedido.', 'uploads/pagos/qr-yape.png', 1, 1, '2026-11-23 08:00:00', '2026-11-23 08:00:00');

SET FOREIGN_KEY_CHECKS = 1;
