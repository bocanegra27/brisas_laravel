<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio centralizado para comunicación con la API de Spring Boot
 * 
 * Reemplaza el antiguo ApiClient.php usando Laravel HTTP Client (Guzzle)
 * Maneja autenticación JWT y errores de forma consistente
 */
class ApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.spring_api.url', 'http://localhost:8080/api');
    }

    /**
     * Realiza petición HTTP a la API Spring Boot
     *
     * @param string $method GET, POST, PUT, PATCH, DELETE
     * @param string $endpoint Ruta del endpoint ej: /auth/login
     * @param array|null $data Datos para enviar en el body
     * @param string|null $token JWT token para autenticación
     * @return array ['success' => bool, 'data' => array, 'code' => int]
     */
    public function request(string $method, string $endpoint, ?array $data = null, ?string $token = null): array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            // Construir petición con headers
            $request = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json']);

            // Agregar Authorization header si hay token
            if ($token) {
                $request->withToken($token);
            }

            // Ejecutar según método HTTP
            $response = match(strtoupper($method)) {
                'GET' => $request->get($url, $data ?? []),
                'POST' => $request->post($url, $data ?? []),
                'PUT' => $request->put($url, $data ?? []),
                'PATCH' => $request->patch($url, $data ?? []),
                'DELETE' => $request->delete($url, $data ?? []),
                default => throw new \InvalidArgumentException("Método HTTP no soportado: {$method}")
            };

            // Log solo en desarrollo
            if (config('app.debug')) {
                Log::info("API Request", [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status()
                ]);
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json() ?? [],
                'code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error("API Request Failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => ['message' => 'Error de conexión con el servidor'],
                'code' => 500
            ];
        }
    }

    /**
     * GET request simplificado
     */
    public function get(string $endpoint, ?string $token = null): array
    {
        return $this->request('GET', $endpoint, null, $token);
    }

    /**
     * POST request simplificado
     */
    public function post(string $endpoint, array $data, ?string $token = null): array
    {
        return $this->request('POST', $endpoint, $data, $token);
    }
}