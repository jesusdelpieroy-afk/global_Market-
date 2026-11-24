<?php

/*
 * Genera el catálogo de la página pública de GitHub Pages (prototipo/) desde la MISMA fuente
 * que la base de datos MySQL: database/seeders/data/datos_prueba.php.
 *
 *   js/catalogo.js      categorías, productos, promociones y ajustes
 *   img/productos/*.jpg copia de las fotos de sistema/public/uploads/productos
 *
 * Uso (desde la carpeta sistema):   php database/sql/generar_catalogo_web.php
 * Hay que repetirlo cuando cambies datos_prueba.php (productos, precios, categorías, fotos).
 */

$raiz = dirname(__DIR__, 2);
$proto = dirname($raiz); // raíz del repositorio
$d = require $raiz.'/database/seeders/data/datos_prueba.php';
$a = $d['ajustes'];

$ids = [];
foreach ($d['productos'] as $i => $p) {
    $ids[$p['nombre']] = 'p'.($i + 1);
}

@mkdir($proto.'/img/productos', 0777, true);
$productos = [];
foreach ($d['productos'] as $p) {
    $img = '';
    if (! empty($p['imagen'])) {
        $archivo = basename($p['imagen']);
        copy($raiz.'/public/'.$p['imagen'], $proto.'/img/productos/'.$archivo);
        $img = 'img/productos/'.$archivo;
    }
    $productos[] = ['id' => $ids[$p['nombre']], 'name' => $p['nombre'], 'price' => (float) $p['precio'],
        'category' => $p['categoria'], 'desc' => $p['descripcion'], 'image' => $img, 'stock' => $p['stock']];
}

$promos = [];
foreach ($d['promociones'] as $i => $x) {
    if (! $x['activa']) {
        continue;
    }
    $promos[] = ['id' => 'pr'.($i + 1), 'title' => $x['titulo'], 'desc' => $x['descripcion'], 'percent' => $x['porcentaje'],
        'all' => $x['aplica_todos'], 'productIds' => array_map(fn ($n) => $ids[$n], $x['productos']),
        'until' => $x['vence'] ?? '', 'image' => '', 'active' => true];
}

$catalogo = [
    'config' => ['name' => $a['nombre'], 'tagline' => $a['descripcion_corta'], 'whatsapp' => $a['whatsapp'], 'currency' => $a['moneda'],
        'heroTitle' => $a['portada_titulo'], 'heroText' => $a['portada_texto'], 'heroImage1' => '', 'heroImage2' => '',
        'address' => $a['direccion'], 'hours' => $a['horario'],
        'faqs' => array_map(fn ($f) => ['q' => $f[0], 'a' => $f[1]], $d['preguntas'])],
    'categories' => array_column($d['categorias'], 'nombre'),
    'products' => $productos,
    'promos' => $promos,
];

file_put_contents($proto.'/js/catalogo.js',
    "/* Catálogo público de Global Market (GENERADO: no editar a mano).\n"
    ." Sale de la misma fuente que la base MySQL (sistema/database/seeders/data/datos_prueba.php).\n"
    ." Regenerar: cd sistema && php database/sql/generar_catalogo_web.php */\n"
    .'window.GM_CATALOGO = '.json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).";\n");

echo 'Listo: js/catalogo.js ('.count($productos).' productos, '.count($catalogo['categories'])." categorías)\n";
