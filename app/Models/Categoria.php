<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'descripcion'])]
class Categoria extends Model
{
    // Categoria 1 — 0..* Producto
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
