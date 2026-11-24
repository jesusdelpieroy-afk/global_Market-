-- =============================================================================
-- Global Market · 02_datos_prueba.sql
-- Datos de prueba. Ejecutar DESPUÉS de 01_estructura.sql (o de php artisan migrate).
-- Generado por database/sql/generar_sql.php desde database/seeders/data/datos_prueba.php
-- Usuarios de prueba (contraseña de todos: 123456):
--   admin@globalmarket.com  (Administrador)
--   encargado@globalmarket.com  (Encargado)
--   vendedor@globalmarket.com  (Vendedor)
-- =============================================================================

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
