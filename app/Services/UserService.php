<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.user_service.url');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Validar token JWT con el servicio de usuarios (GraphQL)
     *
     * @param string $token
     * @return array|null Datos del usuario si el token es válido, null si no
     */
    public function validateToken(string $token): ?array
    {
        // Verificar en cache primero para optimizar
        $cacheKey = 'user_token_' . md5($token);
        $cachedUser = Cache::get($cacheKey);
        
        if ($cachedUser) {
            return $cachedUser;
        }

        try {
            // Verificar token usando GraphQL mutation verifyToken
            $query = [
                'query' => 'mutation { verifyToken(token: "' . $token . '") { payload } }'
            ];

            $response = $this->client->post('/graphql', [
                'json' => $query,
                'headers' => [
                    'Authorization' => 'JWT ' . $token,
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                
                // Verificar si hay errores en la respuesta GraphQL
                if (isset($result['errors'])) {
                    Log::error('Error en validación de token GraphQL: ' . json_encode($result['errors']));
                    return null;
                }
                
                // Extraer payload del token
                if (isset($result['data']['verifyToken']['payload'])) {
                    $payload = $result['data']['verifyToken']['payload'];
                    
                    // Formatear datos del usuario
                    $userData = [
                        'id' => $payload['user_id'] ?? null,
                        'email' => $payload['email'] ?? null,
                        'role' => $payload['role'] ?? null,
                    ];
                    
                    // Cachear por 5 minutos
                    Cache::put($cacheKey, $userData, 300);
                    
                    return $userData;
                }
            }
        } catch (GuzzleException $e) {
            Log::error("Error validando token: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Verificar si un usuario existe (GraphQL)
     *
     * @param int $userId
     * @param string|null $token Token JWT para autenticar la consulta
     * @return bool
     */
    public function userExists(int $userId, ?string $token = null): bool
    {
        $user = $this->getUser($userId, $token);
        return $user !== null;
    }

    /**
     * Obtener información de un usuario (GraphQL)
     *
     * @param int $userId
     * @param string|null $token Token JWT para autenticar la consulta
     * @return array|null
     */
    public function getUser(int $userId, ?string $token = null): ?array
    {
        try {
            $query = [
                'query' => sprintf(
                    'query { user(id: "%s") { id email name phone address role createdAt } }',
                    $userId
                )
            ];

            $options = ['json' => $query];
            
            // Añadir token si está disponible
            if ($token) {
                $options['headers'] = [
                    'Authorization' => 'JWT ' . $token,
                ];
            }

            $response = $this->client->post('/graphql', $options);

            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                
                // Verificar si hay errores
                if (isset($result['errors'])) {
                    Log::error("Error obteniendo usuario {$userId}: " . json_encode($result['errors']));
                    return null;
                }
                
                // Retornar datos del usuario
                if (isset($result['data']['user'])) {
                    return $result['data']['user'];
                }
            }
        } catch (GuzzleException $e) {
            Log::error("Error obteniendo usuario {$userId}: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Verificar si un usuario tiene rol de administrador
     *
     * @param int $userId
     * @return bool
     */
    public function isAdmin(int $userId): bool
    {
        try {
            $user = $this->getUser($userId);
            return $user && isset($user['role']) && $user['role'] === 'admin';
        } catch (\Exception $e) {
            Log::error("Error verificando rol admin del usuario {$userId}: " . $e->getMessage());
            return false;
        }
    }
}
