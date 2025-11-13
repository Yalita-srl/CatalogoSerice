<?php


namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Productos",
 *     description="Operaciones para la gestión de productos"
 * )
 */

class ProductoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/productos",
     *     summary="Obtener lista de todos los productos",
     *     tags={"Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de productos obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Producto")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function index()
    {
        $productos = Producto::with(['restaurante', 'categoria'])->get();
        
        // Incluir la URL completa de la imagen en la respuesta
        $productos->each(function ($producto) {
            if ($producto->imagen) {
                $producto->imagen_url = $producto->imagen_url;
            }
        });
        
        return response()->json($productos);
    }

    /**
     * @OA\Post(
     *     path="/api/productos",
     *     summary="Crear un nuevo producto",
     *     tags={"Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"restaurante_id", "categoria_id", "nombre", "precio", "disponible"},
     *                 @OA\Property(property="restaurante_id", type="integer", example=1),
     *                 @OA\Property(property="categoria_id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Hamburguesa Especial"),
     *                 @OA\Property(property="descripcion", type="string", example="Deliciosa hamburguesa con queso y vegetales"),
     *                 @OA\Property(property="precio", type="number", format="float", example=12.50),
     *                 @OA\Property(property="disponible", type="boolean", example=true),
     *                 @OA\Property(
     *                     property="imagen",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen del producto (JPEG, PNG, JPG, GIF, máximo 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Producto creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Producto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Errores de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "restaurante_id": {"El ID del restaurante es obligatorio"},
     *                     "precio": {"El precio debe ser mayor a 0"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            // Validación con mensajes personalizados
            $validator = Validator::make($request->all(), [
                'restaurante_id' => 'required|integer|exists:restaurantes,id',
                'categoria_id' => 'required|integer|exists:categorias_menus,id',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'required|numeric|min:0',
                'disponible' => 'required|in:true,false,1,0',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'restaurante_id.required' => 'El ID del restaurante es obligatorio',
                'restaurante_id.exists' => 'El restaurante seleccionado no existe',
                'categoria_id.required' => 'El ID de la categoría es obligatorio',
                'categoria_id.exists' => 'La categoría seleccionada no existe',
                'nombre.required' => 'El nombre del producto es obligatorio',
                'precio.required' => 'El precio es obligatorio',
                'precio.numeric' => 'El precio debe ser un número válido',
                'precio.min' => 'El precio debe ser mayor a 0',
                'disponible.required' => 'La disponibilidad es obligatoria',
                'disponible.in' => 'La disponibilidad debe ser true o false',
                'imagen.image' => 'El archivo debe ser una imagen válida',
                'imagen.mimes' => 'La imagen debe ser JPEG, PNG, JPG o GIF',
                'imagen.max' => 'La imagen no debe pesar más de 2MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Procesar el campo disponible
            $disponible = $request->disponible;
            if ($disponible === 'true' || $disponible === '1') {
                $disponible = true;
            } else {
                $disponible = false;
            }

            $data = [
                'restaurante_id' => $request->restaurante_id,
                'categoria_id' => $request->categoria_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => floatval($request->precio),
                'disponible' => $disponible,
            ];

            // Procesar la imagen si se envió
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('productos', 'public');
                $data['imagen'] = $imagenPath;
            }

            $producto = Producto::create($data);
            
            // Cargar relaciones para la respuesta
            $producto->load(['restaurante', 'categoria']);
            
            // Incluir la URL completa en la respuesta
            if ($producto->imagen) {
                $producto->imagen_url = $producto->imagen_url;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => $producto
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Contacte al administrador'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/productos/{id}",
     *     summary="Obtener un producto específico",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Producto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Producto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */

    public function show($id)
    {
        $producto = Producto::with(['restaurante', 'categoria'])->findOrFail($id);
        
        if ($producto->imagen) {
            $producto->imagen_url = $producto->imagen_url;
        }
        
        return response()->json($producto);
    }

    /**
 * @OA\Put(
 *     path="/api/productos/{id}",
 *     summary="Actualizar un producto existente",
 *     tags={"Productos"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del producto",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="restaurante_id", type="integer", example=1),
 *                 @OA\Property(property="categoria_id", type="integer", example=1),
 *                 @OA\Property(property="nombre", type="string", example="Hamburguesa Especial Actualizada"),
 *                 @OA\Property(property="descripcion", type="string", example="Deliciosa hamburguesa actualizada"),
 *                 @OA\Property(property="precio", type="number", format="float", example=15.50),
 *                 @OA\Property(property="disponible", type="boolean", example=false),
 *                 @OA\Property(
 *                     property="imagen",
 *                     type="string",
 *                     format="binary",
 *                     description="Nueva imagen del producto"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Producto actualizado exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Producto no encontrado",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Errores de validación",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'restaurante_id' => 'sometimes|exists:restaurantes,id',
            'categoria_id' => 'sometimes|exists:categorias_menu,id',
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'disponible' => 'boolean',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Procesar nueva imagen si se envió
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            
            $imagenPath = $request->file('imagen')->store('productos', 'public');
            $data['imagen'] = $imagenPath;
        }

        $producto->update($data);
        
        if ($producto->imagen) {
            $producto->imagen_url = $producto->imagen_url;
        }
        
        return response()->json($producto);
    }

    /**
 * @OA\Delete(
 *     path="/api/productos/{id}",
 *     summary="Eliminar un producto",
 *     tags={"Productos"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del producto",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Producto eliminado exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Producto no encontrado",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        
        // Eliminar imagen si existe
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }
        
        $producto->delete();
        return response()->json(null, 204);
    }

    /**
 * @OA\Get(
 *     path="/api/productos/restaurante/{restauranteId}",
 *     summary="Obtener productos por restaurante",
 *     tags={"Productos"},
 *     @OA\Parameter(
 *         name="restauranteId",
 *         in="path",
 *         required=true,
 *         description="ID del restaurante",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Productos del restaurante obtenidos exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */

    // Obtener productos por restaurante
    public function porRestaurante($restauranteId)
    {
        $productos = Producto::where('restaurante_id', $restauranteId)
            ->with('categoria')
            ->get();
            
        $productos->each(function ($producto) {
            if ($producto->imagen) {
                $producto->imagen_url = $producto->imagen_url;
            }
        });
        
        return response()->json($productos);
    }

    /**
 * @OA\Get(
 *     path="/api/productos/categoria/{categoriaId}",
 *     summary="Obtener productos por categoría",
 *     tags={"Productos"},
 *     @OA\Parameter(
 *         name="categoriaId",
 *         in="path",
 *         required=true,
 *         description="ID de la categoría",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Productos de la categoría obtenidos exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */

    // Obtener productos por categoría
    public function porCategoria($categoriaId)
    {
        $productos = Producto::where('categoria_id', $categoriaId)
            ->with(['restaurante', 'categoria'])
            ->get();
            
        $productos->each(function ($producto) {
            if ($producto->imagen) {
                $producto->imagen_url = $producto->imagen_url;
            }
        });
        
        return response()->json($productos);
    }
}