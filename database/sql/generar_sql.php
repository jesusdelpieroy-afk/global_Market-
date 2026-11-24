<?php

/*
 * Genera database/sql/02_datos_prueba.sql y database/sql/global_market_completo.sql
 * a partir de database/seeders/data/datos_prueba.php (la misma fuente del seeder).
 *
 * Uso (desde la carpeta sistema):   php database/sql/generar_sql.php
 * No necesita MySQL ni Composer: solo PHP.
 */

$raiz = dirname(__DIR__, 2);
$d = require $raiz.'/database/seeders/data/datos_prueba.php';
$ahora = '2026-11-23 08:00:00';

// Escapa un valor para escribirlo en un INSERT
function q($v): string
{
    if ($v === null) {
        return 'NULL';
    }
    if (is_bool($v)) {
        return $v ? '1' : '0';
    }
    if (is_int($v) || is_float($v)) {
        return is_float($v) ? number_format($v, 2, '.', '') : (string) $v;
    }

    return "'".str_replace(["\\", "'"], ["\\\\", "''"], (string) $v)."'";
}

// INSERT de varias filas: $cols = nombres de columnas, $filas = arreglos de valores
function insert(string $tabla, array $cols, array $filas): string
{
    if (! $filas) {
        return '';
    }
    $lineas = array_map(fn ($f) => '  ('.implode(', ', array_map('q', $f)).')', $filas);

    return "INSERT INTO `{$tabla}` (`".implode('`, `', $cols)."`) VALUES\n".implode(",\n", $lineas).";\n\n";
}

$hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);

// Identificadores en el mismo orden en que el seeder crea los registros (1, 2, 3...)
$rolId = $catId = $cliId = $usrId = $prodId = [];
foreach ($d['roles'] as $i => $r) { $rolId[$r['nombre']] = $i + 1; }
foreach ($d['categorias'] as $i => $c) { $catId[$c['nombre']] = $i + 1; }
foreach ($d['clientes'] as $i => $c) { $cliId[$c['dni'] ?? "sin-dni-$i"] = $i + 1; }
foreach ($d['usuarios'] as $i => $u) { $usrId[$u['email']] = $i + 1; }
foreach ($d['productos'] as $i => $p) { $prodId[$p['nombre']] = $i + 1; }

$sql = "-- =============================================================================\n"
     ."-- Global Market · 02_datos_prueba.sql\n"
     ."-- Datos de prueba. Ejecutar DESPUÉS de 01_estructura.sql (o de php artisan migrate).\n"
     ."-- Generado por database/sql/generar_sql.php desde database/seeders/data/datos_prueba.php\n"
     ."-- Usuarios de prueba (contraseña de todos: {$d['password']}):\n";
foreach ($d['usuarios'] as $u) {
    $sql .= "--   {$u['email']}  ({$u['rol']})\n";
}
$sql .= "-- =============================================================================\n\n"
      ."USE `global_market`;\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n"
      ."-- Vacía las tablas para poder volver a cargar los datos sin duplicarlos\n"
      ."TRUNCATE TABLE `detalle_ventas`; TRUNCATE TABLE `ventas`; TRUNCATE TABLE `producto_promocion`;\n"
      ."TRUNCATE TABLE `promociones`; TRUNCATE TABLE `productos`; TRUNCATE TABLE `categorias`;\n"
      ."TRUNCATE TABLE `clientes`; TRUNCATE TABLE `users`; TRUNCATE TABLE `roles`; TRUNCATE TABLE `ajustes`; TRUNCATE TABLE `metodos_pago`;\n"
      ."TRUNCATE TABLE `preguntas_frecuentes`; TRUNCATE TABLE `sessions`;\n\n";

$sql .= "-- Roles\n".insert('roles', ['id', 'nombre', 'descripcion', 'created_at', 'updated_at'],
    array_map(fn ($r) => [$rolId[$r['nombre']], $r['nombre'], $r['descripcion'], $ahora, $ahora], $d['roles']));

$sql .= "-- Usuarios (la contraseña se guarda cifrada con bcrypt, igual que Laravel)\n".insert('users',
    ['id', 'rol_id', 'name', 'email', 'telefono', 'password', 'created_at', 'updated_at'],
    array_map(fn ($u) => [$usrId[$u['email']], $rolId[$u['rol']], $u['name'], $u['email'], $u['telefono'], $hash, $ahora, $ahora], $d['usuarios']));

$sql .= "-- Categorías\n".insert('categorias', ['id', 'nombre', 'descripcion', 'created_at', 'updated_at'],
    array_map(fn ($c) => [$catId[$c['nombre']], $c['nombre'], $c['descripcion'], $ahora, $ahora], $d['categorias']));

$sql .= "-- Clientes\n".insert('clientes', ['id', 'nombre', 'dni', 'telefono', 'created_at', 'updated_at'],
    array_map(fn ($c, $i) => [$i + 1, $c['nombre'], $c['dni'], $c['telefono'], $ahora, $ahora], $d['clientes'], array_keys($d['clientes'])));

$sql .= "-- Productos (stock actual: ya descontadas las ventas confirmadas)\n".insert('productos',
    ['id', 'categoria_id', 'nombre', 'descripcion', 'precio', 'stock', 'imagen', 'created_at', 'updated_at'],
    array_map(fn ($p) => [$prodId[$p['nombre']], $catId[$p['categoria']], $p['nombre'], $p['descripcion'], (float) $p['precio'], $p['stock'], $p['imagen'] ?? null, $ahora, $ahora], $d['productos']));

$sql .= "-- Promociones y su relación con productos\n".insert('promociones',
    ['id', 'titulo', 'descripcion', 'porcentaje', 'aplica_todos', 'vence', 'activa', 'created_at', 'updated_at'],
    array_map(fn ($p, $i) => [$i + 1, $p['titulo'], $p['descripcion'], $p['porcentaje'], $p['aplica_todos'], $p['vence'], $p['activa'], $ahora, $ahora], $d['promociones'], array_keys($d['promociones'])));
$pivote = [];
foreach ($d['promociones'] as $i => $p) {
    foreach ($p['productos'] as $nombre) {
        $pivote[] = [$prodId[$nombre], $i + 1];
    }
}
$sql .= insert('producto_promocion', ['producto_id', 'promocion_id'], $pivote);

// Ventas y detalles: total = suma de subtotales; igv = total × 18 / 118
$ventas = $detalles = [];
$detId = 0;
foreach ($d['ventas'] as $i => $v) {
    $total = 0;
    foreach ($v['items'] as [$nombre, $cant, $precio]) {
        $sub = round($cant * $precio, 2);
        $total += $sub;
        $detalles[] = [++$detId, $i + 1, $prodId[$nombre], $cant, (float) $precio, $sub, $v['fecha'], $v['fecha']];
    }
    $total = round($total, 2);
    $ventas[] = [$i + 1, $v['cliente'] ? $cliId[$v['cliente']] : null, $v['usuario'] ? $usrId[$v['usuario']] : null,
        $v['fecha'], $v['estado'], $total, round($total * 18 / 118, 2), $v['fecha'], $v['fecha']];
}
$sql .= "-- Ventas\n".insert('ventas', ['id', 'cliente_id', 'user_id', 'fecha', 'estado', 'total', 'igv', 'created_at', 'updated_at'], $ventas);
$sql .= "-- Detalle de las ventas\n".insert('detalle_ventas', ['id', 'venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'created_at', 'updated_at'], $detalles);

$a = $d['ajustes'];
$sql .= "-- Ajustes de la tienda (una sola fila)\n".insert('ajustes',
    ['id', 'nombre', 'descripcion_corta', 'whatsapp', 'moneda', 'direccion', 'horario', 'portada_titulo', 'portada_texto', 'created_at', 'updated_at'],
    [[1, $a['nombre'], $a['descripcion_corta'], $a['whatsapp'], $a['moneda'], $a['direccion'], $a['horario'], $a['portada_titulo'], $a['portada_texto'], $ahora, $ahora]]);

$sql .= "-- Preguntas frecuentes (alimentan la tienda y el asistente virtual)\n".insert('preguntas_frecuentes',
    ['id', 'pregunta', 'respuesta', 'orden', 'created_at', 'updated_at'],
    array_map(fn ($p, $i) => [$i + 1, $p[0], $p[1], $i + 1, $ahora, $ahora], $d['preguntas'], array_keys($d['preguntas'])));

$sql .= "-- Método de pago: QR de Yape (public/uploads/pagos/qr-yape.png); se edita desde el panel\n".insert('metodos_pago',
    ['id', 'nombre', 'instrucciones', 'imagen', 'activo', 'orden', 'created_at', 'updated_at'],
    [[1, 'Yape', 'Escanea el QR con Yape y paga el total de tu pedido.', 'uploads/pagos/qr-yape.png', 1, 1, $ahora, $ahora]]);

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

// Registro de migraciones (para que `php artisan migrate` no intente crear de nuevo las tablas)
$migraciones = array_map(fn ($f) => basename($f, '.php'), glob($raiz.'/database/migrations/*.php'));
sort($migraciones);
$regMig = "-- Registro de migraciones: `php artisan migrate` responderá «Nothing to migrate»\n"
    .insert('migrations', ['migration', 'batch'], array_map(fn ($m) => [$m, 1], $migraciones));

$estructura = file_get_contents(__DIR__.'/01_estructura.sql');
// Reemplaza lo que haya entre los marcadores (así el script se puede ejecutar varias veces)
$estructuraConMig = preg_replace_callback(
    '/-- >>> MIGRACIONES.*?-- <<< MIGRACIONES\n/s',
    fn () => "-- >>> MIGRACIONES (lo rellena generar_sql.php)\n".$regMig."-- <<< MIGRACIONES\n",
    $estructura
);

file_put_contents(__DIR__.'/01_estructura.sql', $estructuraConMig);
file_put_contents(__DIR__.'/02_datos_prueba.sql', $sql);
file_put_contents(__DIR__.'/global_market_completo.sql',
    "-- Global Market · script completo (estructura + datos de prueba) en un solo archivo.\n"
    ."-- Importar con HeidiSQL o: mysql -u root < database/sql/global_market_completo.sql\n\n"
    .$estructuraConMig."\n".preg_replace('/^-- =+\n(?:--.*\n)+-- =+\n\n/', '', $sql));

echo "Listo: 01_estructura.sql, 02_datos_prueba.sql y global_market_completo.sql\n";
