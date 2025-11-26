<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserToken
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener token del header Authorization
        // Soporta tanto "JWT token" como "Bearer token"
        $authHeader = $request->header('Authorization');
        $token = null;

        if ($authHeader) {
            // Intentar extraer token con formato "JWT token" o "Bearer token"
            if (preg_match('/^(JWT|Bearer)\s+(\S+)$/', $authHeader, $matches)) {
                $token = $matches[2];
            }
        }

        if (!$token) {
            return response()->json([
                'message' => 'Token no proporcionado',
                'error' => 'Unauthorized'
            ], 401);
        }

        $userData = $this->userService->validateToken($token);

        if (!$userData) {
            return response()->json([
                'message' => 'Token inválido o expirado',
                'error' => 'Unauthorized'
            ], 401);
        }

        // Agregar datos del usuario al request para usarlos en los controladores
        $request->merge(['authenticated_user' => $userData]);

        return $next($request);
    }
}
