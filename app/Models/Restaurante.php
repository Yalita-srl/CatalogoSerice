<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Restaurante",
 *     type="object",
 *     title="Restaurante",
 *     description="Modelo de Restaurante",
 *     required={"usuario_admin_id", "nombre", "direccion", "telefono", "estado"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID del restaurante"
 *     ),
 *     @OA\Property(
 *         property="usuario_admin_id",
 *         type="integer",
 *         description="ID del usuario administrador"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre del restaurante"
 *     ),
 *     @OA\Property(
 *         property="direccion",
 *         type="string",
 *         description="Dirección del restaurante"
 *     ),
 *     @OA\Property(
 *         property="telefono",
 *         type="string",
 *         description="Teléfono del restaurante"
 *     ),
 *     @OA\Property(
 *         property="estado",
 *         type="string",
 *         enum={"Abierto", "Cerrado"},
 *         description="Estado del restaurante"
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
class Restaurante extends Model
{
    use HasFactory;

    protected $table = 'restaurantes';

    protected $fillable = [
        'usuario_admin_id',
        'nombre',
        'direccion',
        'telefono',
        'estado'
    ];

    protected $casts = [
    
        'estado' => 'string',
        'usuario_admin_id' => 'integer'
    ];

    // Relaciones

    public function categorias()
    {
        return $this->hasMany(CategoriasMenu::class, 'restaurante_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'restaurante_id');
    }

    

    
}