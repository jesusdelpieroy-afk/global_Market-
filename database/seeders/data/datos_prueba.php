<?php

/*
|--------------------------------------------------------------------------
| Datos de prueba de Global Market (fuente única)
|--------------------------------------------------------------------------
| Los usa DatabaseSeeder (php artisan migrate:fresh --seed) y el generador de los
| scripts SQL (php database/sql/generar_sql.php), así ambos caminos dejan la
| misma base de datos.
|
| Los «stock» son el stock ACTUAL: ya tienen descontadas las ventas confirmadas de abajo.
| Contraseña de todos los usuarios de prueba: 123456
*/

return [
    'password' => '123456',

    'roles' => [
        ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al panel'],
        ['nombre' => 'Encargado', 'descripcion' => 'Gestiona productos, stock y pedidos'],
        ['nombre' => 'Vendedor', 'descripcion' => 'Registra ventas de mostrador'],
    ],

    'usuarios' => [
        ['name' => 'Administrador', 'email' => 'admin@globalmarket.com', 'telefono' => '999111222', 'rol' => 'Administrador'],
        ['name' => 'Elena Paredes', 'email' => 'encargado@globalmarket.com', 'telefono' => '999333444', 'rol' => 'Encargado'],
        ['name' => 'Jorge Castillo', 'email' => 'vendedor@globalmarket.com', 'telefono' => '999555666', 'rol' => 'Vendedor'],
    ],

    'categorias' => [
        ['nombre' => 'Hogar', 'descripcion' => 'Decoración y textiles para la casa'],
        ['nombre' => 'Organizadores', 'descripcion' => 'Canastas, cajas y organizadores'],
        ['nombre' => 'Accesorios de cabello', 'descripcion' => 'Peinetas, cepillos y otros accesorios para el cabello'],
        ['nombre' => 'Cocina', 'descripcion' => 'Utensilios y almacenaje de cocina'],
        ['nombre' => 'Limpieza', 'descripcion' => 'Productos y accesorios de limpieza'],
        ['nombre' => 'Scrunchies', 'descripcion' => 'Colas de cabello de tela: algodón, terciopelo, satén y con cinta'],
        ['nombre' => 'Diademas y moños', 'descripcion' => 'Diademas de tela, moños y balacas'],
        ['nombre' => 'Vasos y tazas', 'descripcion' => 'Vasos de plástico y vidrio, tazas de cerámica'],
    ],

    'productos' => [
        ['categoria' => 'Organizadores', 'nombre' => 'Canasta organizadora con tapa', 'precio' => 24.90, 'stock' => 10, 'descripcion' => 'Ideal para ordenar ropa, juguetes o utensilios.'],
        ['categoria' => 'Hogar', 'nombre' => 'Cesto de ropa tejido', 'precio' => 39.90, 'stock' => 7, 'descripcion' => 'Cesto ventilado, resistente y fácil de limpiar.'],
        ['categoria' => 'Scrunchies', 'nombre' => 'Set de scrunchies de colores', 'precio' => 9.90, 'stock' => 27, 'descripcion' => 'Pack de 6 colores, suaves con el cabello.', 'imagen' => 'uploads/productos/scrunchies-peluche-x6.jpg'],
        ['categoria' => 'Accesorios de cabello', 'nombre' => 'Diadema de spa', 'precio' => 7.50, 'stock' => 4, 'descripcion' => 'Para el cuidado facial y el maquillaje.'],
        ['categoria' => 'Organizadores', 'nombre' => 'Organizador de cajones x6', 'precio' => 18.50, 'stock' => 15, 'descripcion' => 'Seis divisores ajustables para cajones.'],
        ['categoria' => 'Organizadores', 'nombre' => 'Caja multiusos apilable', 'precio' => 32.00, 'stock' => 3, 'descripcion' => 'Caja resistente que se apila para ahorrar espacio.'],
        ['categoria' => 'Cocina', 'nombre' => 'Juego de toallas de cocina x3', 'precio' => 14.90, 'stock' => 17, 'descripcion' => 'Algodón absorbente, tres diseños.'],
        ['categoria' => 'Cocina', 'nombre' => 'Set de tapers herméticos x5', 'precio' => 29.90, 'stock' => 8, 'descripcion' => 'Aptos para microondas y congelador.'],
        ['categoria' => 'Cocina', 'nombre' => 'Escurridor de platos plegable', 'precio' => 35.00, 'stock' => 0, 'descripcion' => 'Se pliega para guardarlo fácilmente.'],
        ['categoria' => 'Hogar', 'nombre' => 'Espejo decorativo redondo', 'precio' => 59.90, 'stock' => 6, 'descripcion' => 'Marco metálico dorado de 40 cm.'],
        ['categoria' => 'Hogar', 'nombre' => 'Cojín decorativo', 'precio' => 22.00, 'stock' => 14, 'descripcion' => 'Funda lavable con relleno suave.'],
        ['categoria' => 'Accesorios de cabello', 'nombre' => 'Peineta y cepillo desenredante', 'precio' => 12.50, 'stock' => 5, 'descripcion' => 'Para cabello seco o mojado.'],
        ['categoria' => 'Limpieza', 'nombre' => 'Limpiador multiuso 1 L', 'precio' => 8.90, 'stock' => 38, 'descripcion' => 'Aroma cítrico, para todas las superficies.', 'imagen' => 'uploads/productos/atomizador-azul.jpg'],
        ['categoria' => 'Limpieza', 'nombre' => 'Set de esponjas x6', 'precio' => 6.50, 'stock' => 2, 'descripcion' => 'Esponjas de doble cara.'],

        // Productos con foto (public/uploads/productos/)
        ['categoria' => 'Scrunchies', 'nombre' => 'Scrunchie de algodón crema', 'precio' => 6.90, 'stock' => 20, 'descripcion' => 'Algodón suave y elástico en color crema.', 'imagen' => 'uploads/productos/scrunchie-crema.jpg'],
        ['categoria' => 'Scrunchies', 'nombre' => 'Scrunchie de terciopelo rojo', 'precio' => 7.50, 'stock' => 18, 'descripcion' => 'Terciopelo suave que cuida el cabello.', 'imagen' => 'uploads/productos/scrunchie-terciopelo-rojo.jpg'],
        ['categoria' => 'Scrunchies', 'nombre' => 'Scrunchie de satén negro', 'precio' => 8.50, 'stock' => 15, 'descripcion' => 'Satén sedoso, ideal para el uso diario y para dormir.', 'imagen' => 'uploads/productos/scrunchie-saten-negro.jpg'],
        ['categoria' => 'Scrunchies', 'nombre' => 'Scrunchie con cinta roja', 'precio' => 9.90, 'stock' => 12, 'descripcion' => 'Con cinta larga para lucirlo como lazo.', 'imagen' => 'uploads/productos/scrunchie-cinta-roja.jpg'],
        ['categoria' => 'Scrunchies', 'nombre' => 'Scrunchie con cinta animal print', 'precio' => 10.90, 'stock' => 9, 'descripcion' => 'Cinta estampada de leopardo, se puede usar de lazo.', 'imagen' => 'uploads/productos/scrunchie-animal-print.jpg'],
        ['categoria' => 'Diademas y moños', 'nombre' => 'Diadema de tela con nudo rosa', 'precio' => 12.90, 'stock' => 11, 'descripcion' => 'Diadema acolchada con nudo al frente.', 'imagen' => 'uploads/productos/diadema-nudo-rosa.jpg'],
        ['categoria' => 'Diademas y moños', 'nombre' => 'Diadema estampado paisley rojo', 'precio' => 9.90, 'stock' => 14, 'descripcion' => 'Diadema ancha de tela con estampado paisley.', 'imagen' => 'uploads/productos/diadema-paisley-rojo.jpg'],
        ['categoria' => 'Diademas y moños', 'nombre' => 'Moño de cinta rosa con lunares', 'precio' => 6.50, 'stock' => 25, 'descripcion' => 'Moño de cinta tipo grosgrain con lunares blancos.', 'imagen' => 'uploads/productos/mono-lunares-rosa.jpg'],
        ['categoria' => 'Diademas y moños', 'nombre' => 'Balaca con moño fucsia', 'precio' => 8.90, 'stock' => 10, 'descripcion' => 'Moño grande fucsia con banda elástica suave.', 'imagen' => 'uploads/productos/mono-fucsia-elastico.jpg'],
        ['categoria' => 'Vasos y tazas', 'nombre' => 'Set de vasos de colores x6', 'precio' => 12.90, 'stock' => 16, 'descripcion' => 'Vasos reutilizables en seis colores.', 'imagen' => 'uploads/productos/vasos-plasticos-x6.jpg'],
        ['categoria' => 'Vasos y tazas', 'nombre' => 'Taza de cerámica roja', 'precio' => 11.90, 'stock' => 13, 'descripcion' => 'Taza grande con interior blanco.', 'imagen' => 'uploads/productos/taza-roja.jpg'],
        ['categoria' => 'Vasos y tazas', 'nombre' => 'Set de vasos de vidrio x4', 'precio' => 39.90, 'stock' => 6, 'descripcion' => 'Vasos bajos de vidrio con diseño grabado.', 'imagen' => 'uploads/productos/vasos-vidrio-x4.jpg'],
        ['categoria' => 'Limpieza', 'nombre' => 'Botella atomizadora rosada', 'precio' => 4.90, 'stock' => 30, 'descripcion' => 'Botella con gatillo para limpiadores o agua.', 'imagen' => 'uploads/productos/atomizador-rosado.jpg'],
        ['categoria' => 'Limpieza', 'nombre' => 'Lavavajilla en pasta Sapolio 800 g', 'precio' => 7.90, 'stock' => 22, 'descripcion' => 'Lavavajilla en pasta con esponja incluida.', 'imagen' => 'uploads/productos/lavavajilla-sapolio.jpg'],
        ['categoria' => 'Hogar', 'nombre' => 'Tacho de basura con tapa vaivén', 'precio' => 24.90, 'stock' => 9, 'descripcion' => 'Tacho de plástico con tapa vaivén fácil de limpiar.', 'imagen' => 'uploads/productos/tacho-vaiven-rojo.jpg'],
    ],

    'clientes' => [
        ['nombre' => 'Rosa Quispe Mamani', 'dni' => '45871236', 'telefono' => '987654321'],
        ['nombre' => 'Luis Ramos Huamán', 'dni' => '70231458', 'telefono' => '986112233'],
        ['nombre' => 'María Torres Salas', 'dni' => '41587963', 'telefono' => '955443322'],
        ['nombre' => 'Carlos Medina Paredes', 'dni' => '72904415', 'telefono' => '944001122'],
        ['nombre' => 'Ana Flores Ccori', 'dni' => null, 'telefono' => '933221100'],
        ['nombre' => 'Julia Vargas Rojas', 'dni' => '46120987', 'telefono' => null],
    ],

    'promociones' => [
        ['titulo' => 'Oferta de bienvenida', 'descripcion' => '10% de descuento en todos los productos de la tienda.', 'porcentaje' => 10, 'aplica_todos' => true, 'vence' => null, 'activa' => true, 'productos' => []],
        ['titulo' => 'Especial cocina', 'descripcion' => '15% de descuento en productos seleccionados de cocina.', 'porcentaje' => 15, 'aplica_todos' => false, 'vence' => '2027-01-31', 'activa' => true,
            'productos' => ['Juego de toallas de cocina x3', 'Set de tapers herméticos x5', 'Escurridor de platos plegable']],
        ['titulo' => 'Liquidación de verano', 'descripcion' => '20% de descuento (campaña finalizada).', 'porcentaje' => 20, 'aplica_todos' => true, 'vence' => '2026-03-31', 'activa' => false, 'productos' => []],
    ],

    'preguntas' => [
        ['¿Cómo hago mi pedido?', 'Agrega los productos al carrito y presiona «Enviar pedido por WhatsApp». Te confirmamos la disponibilidad y coordinamos contigo el pago y la entrega.'],
        ['¿Qué medios de pago aceptan?', 'El pago se coordina por WhatsApp una vez confirmado tu pedido. Escríbenos y te indicamos las opciones disponibles.'],
        ['¿Hacen envíos?', 'La entrega se coordina contigo por WhatsApp según tu zona.'],
        ['¿Los productos tienen stock?', 'El catálogo muestra el stock disponible de cada producto. Si algo aparece agotado, escríbenos y te avisamos cuando vuelva a haber.'],
    ],

    'ajustes' => [
        'nombre' => 'Global Market',
        'descripcion_corta' => 'Hogar, organizadores y accesorios',
        'whatsapp' => '51999999999',
        'moneda' => 'S/',
        'direccion' => 'Av. Principal 123, Lima',
        'horario' => 'Lunes a sábado de 9:00 a 20:00',
        'portada_titulo' => 'Todo para tu hogar en un solo lugar',
        'portada_texto' => 'Elige lo que necesitas del catálogo y haz tu pedido por WhatsApp. Confirmamos la disponibilidad y coordinamos el pago y la entrega contigo.',
    ],

    // Ventas de ejemplo. El IGV (18 %) está incluido en el precio: igv = total × 18 / 118.
    // «precio» es el precio unitario cobrado (en las ventas de la tienda ya lleva el 10 % de la promoción de bienvenida).
    'ventas' => [
        ['fecha' => '2026-11-15 11:20:00', 'cliente' => '45871236', 'usuario' => 'vendedor@globalmarket.com', 'estado' => 'confirmado',
            'items' => [['Canasta organizadora con tapa', 2, 24.90], ['Set de scrunchies de colores', 3, 9.90]]],
        ['fecha' => '2026-11-17 16:45:00', 'cliente' => '70231458', 'usuario' => 'admin@globalmarket.com', 'estado' => 'confirmado',
            'items' => [['Cesto de ropa tejido', 1, 39.90], ['Set de tapers herméticos x5', 2, 29.90]]],
        ['fecha' => '2026-11-19 09:10:00', 'cliente' => null, 'usuario' => 'vendedor@globalmarket.com', 'estado' => 'confirmado',
            'items' => [['Juego de toallas de cocina x3', 3, 14.90], ['Limpiador multiuso 1 L', 2, 8.90]]],
        ['fecha' => '2026-11-21 18:05:00', 'cliente' => '41587963', 'usuario' => null, 'estado' => 'pendiente',
            'items' => [['Espejo decorativo redondo', 1, 53.91], ['Cojín decorativo', 2, 19.80]]],
        ['fecha' => '2026-11-22 12:30:00', 'cliente' => '45871236', 'usuario' => null, 'estado' => 'cancelado',
            'items' => [['Diadema de spa', 1, 6.75]]],
    ],
];
