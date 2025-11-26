<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Restaurantes",
 *     description="Operaciones para la gestión de restaurantes"
 * )
 */

class RestauranteController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/restaurantes",
     *     summary="Obtener lista de todos los restaurantes",
     *     tags={"Restaurantes"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de restaurantes obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Restaurante")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $restaurantes = Restaurante::with(['categorias', 'productos'])->get();
        return response()->json($restaurantes);
    }

    /**
     * @OA\Post(
     *     path="/api/restaurantes",
     *     summary="Crear un nuevo restaurante",
     *     tags={"Restaurantes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"usuario_admin_id", "nombre", "direccion", "telefono", "estado", "imagen"},
     *                 @OA\Property(property="usuario_admin_id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Mi Restaurante"),
     *                 @OA\Property(property="direccion", type="string", example="Calle Principal 123"),
     *                 @OA\Property(property="telefono", type="string", example="555-1234"),
     *                 @OA\Property(property="estado", type="string", enum={"Abierto", "Cerrado"}, example="Abierto"),
     *                 @OA\Property(
     *                     property="imagen",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen del restaurante (JPEG, PNG, JPG, GIF, máximo 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Restaurante creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Restaurante")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'usuario_admin_id' => 'required|integer|min:1',
                'nombre' => 'required|string|max:255',
                'direccion' => 'required|string',
                'telefono' => 'required|string|max:20',
                'estado' => 'required|in:Abierto,Cerrado',
                'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'imagen.required' => 'La imagen es obligatoria',
                'imagen.image' => 'El archivo debe ser una imagen válida',
                'imagen.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg o gif',
                'imagen.max' => 'La imagen no debe pesar más de 2MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Procesar la imagen
            $imagenPath = $request->file('imagen')->store('restaurantes', 'public');

            $restaurante = Restaurante::create([
                'usuario_admin_id' => $request->usuario_admin_id,
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'estado' => $request->estado,
                'imagen' => $imagenPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Restaurante creado exitosamente',
                'data' => $restaurante
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al crear restaurante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el restaurante',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/restaurantes/{id}",
     *     summary="Obtener un restaurante específico",
     *     tags={"Restaurantes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del restaurante",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurante encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Restaurante")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurante no encontrado"
     *     )
     * )
     */

    public function show($id)
    {
        $restaurante = Restaurante::with(['categorias', 'productos'])->findOrFail($id);
        return response()->json($restaurante);
    }

     /**
     * @OA\Post(
     *     path="/api/restaurantes/{id}",
     *     summary="Actualizar un restaurante existente",
     *     tags={"Restaurantes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del restaurante",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="usuario_admin_id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Nuevo Nombre"),
     *                 @OA\Property(property="direccion", type="string", example="Nueva Dirección 123"),
     *                 @OA\Property(property="telefono", type="string", example="555-5678"),
     *                 @OA\Property(property="estado", type="string", enum={"Abierto", "Cerrado"}, example="Abierto"),
     *                 @OA\Property(
     *                     property="imagen",
     *                     type="string",
     *                     format="binary",
     *                     description="Nueva imagen del restaurante (JPEG, PNG, JPG, GIF, máximo 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurante actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Restaurante")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurante no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $restaurante = Restaurante::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'usuario_admin_id' => 'sometimes|required|integer|min:1',
                'nombre' => 'sometimes|required|string|max:255',
                'direccion' => 'sometimes|required|string',
                'telefono' => 'sometimes|required|string|max:20',
                'estado' => 'sometimes|required|in:Abierto,Cerrado',
                'imagen' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'imagen.image' => 'El archivo debe ser una imagen válida',
                'imagen.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg o gif',
                'imagen.max' => 'La imagen no debe pesar más de 2MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('imagen');

            // Si se sube una nueva imagen
            if ($request->hasFile('imagen')) {
                // Eliminar la imagen anterior si existe
                if ($restaurante->imagen && Storage::disk('public')->exists($restaurante->imagen)) {
                    Storage::disk('public')->delete($restaurante->imagen);
                }
                // Guardar la nueva imagen
                $data['imagen'] = $request->file('imagen')->store('restaurantes', 'public');
            }

            $restaurante->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Restaurante actualizado exitosamente',
                'data' => $restaurante
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar restaurante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el restaurante',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/restaurantes/{id}",
     *     summary="Eliminar un restaurante",
     *     tags={"Restaurantes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del restaurante",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Restaurante eliminado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurante no encontrado"
     *     )
     * )
     */

    public function destroy($id)
    {
        try {
            $restaurante = Restaurante::findOrFail($id);
            
            // Eliminar la imagen si existe
            if ($restaurante->imagen && Storage::disk('public')->exists($restaurante->imagen)) {
                Storage::disk('public')->delete($restaurante->imagen);
            }
            
            $restaurante->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Restaurante eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al eliminar restaurante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el restaurante',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/restaurantes/usuario/{usuarioAdminId}",
     *     summary="Obtener restaurantes por usuario administrador",
     *     tags={"Restaurantes"},
     *     @OA\Parameter(
     *         name="usuarioAdminId",
     *         in="path",
     *         required=true,
     *         description="ID del usuario administrador",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de restaurantes obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Restaurante")
     *         )
     *     )
     * )
     */

    // Método adicional para obtener restaurantes por usuario_admin_id
    public function porUsuarioAdmin($usuarioAdminId)
    {
        $restaurantes = Restaurante::where('usuario_admin_id', $usuarioAdminId)
            ->with(['categorias', 'productos'])
            ->get();
        return response()->json($restaurantes);
    }

    
}