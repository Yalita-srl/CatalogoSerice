<?php
// app/Models/CategoriaMenu.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CategoriaMenu",
 *     type="object",
 *     title="Categoría de Menú",
 *     description="Modelo de Categoría de Menú",
 *     required={"restaurante_id", "nombre"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID de la categoría"
 *     ),
 *     @OA\Property(
 *         property="restaurante_id",
 *         type="integer",
 *         description="ID del restaurante"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre de la categoría"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de creación"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de actualización"
 *     )
 * )
 */

class CategoriasMenu extends Model
{
    use HasFactory;

    protected $table = 'categorias_menus';

    protected $fillable = [
        'restaurante_id',
        'nombre'
    ];

    // Relaciones
    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}