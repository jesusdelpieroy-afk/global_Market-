<?php

namespace App\Models;

use App\Support\Imagen;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'descripcion_corta', 'whatsapp', 'moneda', 'direccion', 'horario',
    'portada_titulo', 'portada_texto', 'portada_imagen1', 'portada_imagen2'])]
class Ajuste extends Model
{
    // Tabla de una sola fila: devuelve la configuración de la tienda
    public static function actual(): self
    {
        return static::first() ?? static::create([
            'nombre' => 'Global Market',
            'descripcion_corta' => 'Hogar, organizadores y accesorios',
            'whatsapp' => '51999999999',
            'moneda' => 'S/',
            'portada_titulo' => 'Todo para tu hogar en un solo lugar',
        ]);
    }

    public function portadaUrl(int $n): string
    {
        $ruta = $this->{'portada_imagen'.$n};

        return Imagen::url($ruta) ?? asset($n === 1 ? 'img/local-1.jpg' : 'img/local-2.jpg');
    }
}
