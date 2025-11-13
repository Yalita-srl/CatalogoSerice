<?php
// app/Http/Controllers/CategoriaMenuController.php

namespace App\Http\Controllers;

use App\Models\CategoriasMenu;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Categorías Menú",
 *     description="Operaciones para la gestión de categorías de menú"
 * )
 */

class CategoriasMenuController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/categorias-menu",
     *     summary="Obtener lista de todas las categorías de menú",
     *     tags={"Categorías Menú"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías obtenida exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */

    public function index()
    {
        $categorias = CategoriasMenu::with(['restaurante', 'productos'])->get();
        return response()->json($categorias);
    }

    /**
     * @OA\Post(
     *     path="/api/categorias-menu",
     *     summary="Crear una nueva categoría de menú",
     *     tags={"Categorías Menú"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurante_id", "nombre"},
     *             @OA\Property(property="restaurante_id", type="integer", example=1),
     *             @OA\Property(property="nombre", type="string", example="Entradas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoría creada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
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

    public function store(Request $request)
    {
        $request->validate([
            'restaurante_id' => 'required|exists:restaurantes,id',
            'nombre' => 'required|string|max:255'
        ]);

        $categoria = CategoriasMenu::create($request->all());
        return response()->json($categoria, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/categorias-menu/{id}",
     *     summary="Obtener una categoría específica",
     *     tags={"Categorías Menú"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la categoría",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */

    public function show($id)
    {
        $categoria = CategoriasMenu::with(['restaurante', 'productos'])->findOrFail($id);
        return response()->json($categoria);
    }

    /**
     * @OA\Put(
     *     path="/api/categorias-menu/{id}",
     *     summary="Actualizar una categoría existente",
     *     tags={"Categorías Menú"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la categoría",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="restaurante_id", type="integer", example=1),
     *             @OA\Property(property="nombre", type="string", example="Entradas Actualizadas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría actualizada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
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
        $categoria = CategoriasMenu::findOrFail($id);

        $request->validate([
            'restaurante_id' => 'sometimes|exists:restaurantes,id',
            'nombre' => 'sometimes|string|max:255'
        ]);

        $categoria->update($request->all());
        return response()->json($categoria);
    }

    /**
     * @OA\Delete(
     *     path="/api/categorias-menu/{id}",
     *     summary="Eliminar una categoría",
     *     tags={"Categorías Menú"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la categoría",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría eliminada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
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
        $categoria = CategoriasMenu::findOrFail($id);
        $categoria->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/categorias-menu/restaurante/{restauranteId}",
     *     summary="Obtener categorías por restaurante",
     *     tags={"Categorías Menú"},
     *     @OA\Parameter(
     *         name="restauranteId",
     *         in="path",
     *         required=true,
     *         description="ID del restaurante",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías del restaurante obtenida exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */

    // Obtener categorías por restaurante
    public function porRestaurante($restauranteId)
    {
        $categorias = CategoriasMenu::where('restaurante_id', $restauranteId)
            ->with('productos')
            ->get();
        return response()->json($categorias);
    }
}