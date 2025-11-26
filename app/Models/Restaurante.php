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
 *     @OA\Property(property="id", type="integer", format="int64", description="ID del restaurante"),
 *     @OA\Property(property="usuario_admin_id", type="integer", description="ID del usuario administrador"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del restaurante"),
 *     @OA\Property(property="direccion", type="string", description="Dirección del restaurante"),
 *     @OA\Property(property="telefono", type="string", description="Teléfono del restaurante"),
 *     @OA\Property(property="estado", type="string", enum={"Abierto", "Cerrado"}, description="Estado del restaurante"),
 *     @OA\Property(property="imagen", type="string", description="Ruta de la imagen del restaurante", nullable=true),
 *     @OA\Property(property="imagen_url", type="string", description="URL completa de la imagen del restaurante", readOnly=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de actualización"),
 *     @OA\Property(property="categorias", type="array", @OA\Items(ref="#/components/schemas/CategoriaMenu")),
 *     @OA\Property(property="productos", type="array", @OA\Items(ref="#/components/schemas/Producto"))
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
        'estado',
        'imagen'
    ];
    
    protected $appends = ['imagen_url'];

    protected $casts = [
        'estado' => 'string',
        'usuario_admin_id' => 'integer'
    ];
    
    // Accesor para obtener la URL completa de la imagen
    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }
        return null;
    }

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