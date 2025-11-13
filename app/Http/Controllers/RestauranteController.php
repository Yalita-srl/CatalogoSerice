<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use Illuminate\Http\Request;

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
     *         @OA\JsonContent(
     *             required={"usuario_admin_id", "nombre", "direccion", "telefono", "estado"},
     *             @OA\Property(property="usuario_admin_id", type="integer", example=1),
     *             @OA\Property(property="nombre", type="string", example="Mi Restaurante"),
     *             @OA\Property(property="direccion", type="string", example="Calle Principal 123"),
     *             @OA\Property(property="telefono", type="string", example="555-1234"),
     *             @OA\Property(property="estado", type="string", enum={"Abierto", "Cerrado"}, example="Abierto")
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
        $request->validate([
            'usuario_admin_id' => 'required|integer|min:1', // Validación básica como integer
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'estado' => 'required|in:Abierto,Cerrado'
        ]);

        $restaurante = Restaurante::create($request->all());
        return response()->json($restaurante, 201);
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
     * @OA\Put(
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
     *         @OA\JsonContent(
     *             @OA\Property(property="usuario_admin_id", type="integer", example=1),
     *             @OA\Property(property="nombre", type="string", example="Mi Restaurante Actualizado"),
     *             @OA\Property(property="direccion", type="string", example="Calle Principal 456"),
     *             @OA\Property(property="telefono", type="string", example="555-5678"),
     *             @OA\Property(property="estado", type="string", enum={"Abierto", "Cerrado"}, example="Cerrado")
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
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $restaurante = Restaurante::findOrFail($id);

        $request->validate([
            'usuario_admin_id' => 'sometimes|integer|min:1',
            'nombre' => 'sometimes|string|max:255',
            'direccion' => 'sometimes|string',
            'telefono' => 'sometimes|string|max:20',
            'estado' => 'sometimes|in:Abierto,Cerrado'
        ]);

        $restaurante->update($request->all());
        return response()->json($restaurante);
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
        $restaurante = Restaurante::findOrFail($id);
        $restaurante->delete();
        return response()->json(null, 204);
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