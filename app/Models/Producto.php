<?php
// app/Models/Producto.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Producto",
 *     type="object",
 *     title="Producto",
 *     description="Modelo de Producto del catálogo",
 *     required={"restaurante_id", "categoria_id", "nombre", "precio", "disponible"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID único del producto"
 *     ),
 *     @OA\Property(
 *         property="restaurante_id",
 *         type="integer",
 *         description="ID del restaurante al que pertenece el producto"
 *     ),
 *     @OA\Property(
 *         property="categoria_id",
 *         type="integer",
 *         description="ID de la categoría del producto"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         maxLength=255,
 *         description="Nombre del producto"
 *     ),
 *     @OA\Property(
 *         property="descripcion",
 *         type="string",
 *         nullable=true,
 *         description="Descripción detallada del producto"
 *     ),
 *     @OA\Property(
 *         property="precio",
 *         type="number",
 *         format="float",
 *         minimum=0,
 *         description="Precio del producto"
 *     ),
 *     @OA\Property(
 *         property="disponible",
 *         type="boolean",
 *         description="Estado de disponibilidad del producto"
 *     ),
 *     @OA\Property(
 *         property="imagen",
 *         type="string",
 *         nullable=true,
 *         description="Ruta de la imagen del producto"
 *     ),
 *     @OA\Property(
 *         property="imagen_url",
 *         type="string",
 *         nullable=true,
 *         description="URL completa de la imagen del producto"
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
 *         description="Fecha de última actualización"
 *     )
 * )
 */

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'restaurante_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'disponible',
        'imagen'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean'
    ];
    protected $appends = ['imagen_url'];
    // Accesor para obtener la URL completa de la imagen
    public function getImagenUrlAttribute()
{
    if ($this->imagen) {
        return asset('storage/' . $this->imagen);
    }
    return null;
}

    // Relaciones
    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriasMenu::class, 'categoria_id');
    }

    

    
}