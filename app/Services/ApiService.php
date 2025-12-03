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
     * @param array $options Opciones adicionales (headers, etc)
     * @return array|null Respuesta del API o null si falla
     */
    public function request(string $method, string $endpoint, ?array $data = null, array $options = []): ?array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            // Construir petición con headers
            $request = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json']);

            // Agregar Authorization header si está en las opciones
            if (isset($options['headers']['Authorization'])) {
                $request->withHeaders([
                    'Authorization' => $options['headers']['Authorization']
                ]);
            }

            // Ejecutar según método HTTP
            $response = match(strtoupper($method)) {
                'GET' => $request->get($url), // ← SIN parámetros adicionales
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

            // Si la respuesta fue exitosa, retornar los datos
            if ($response->successful()) {
                return $response->json() ?? [];
            }

            // Si hay error, loguearlo y retornar null
            Log::warning("API Request Failed", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error("API Request Failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * GET request simplificado
     */
    public function get(string $endpoint, array $options = []): ?array
    {
        return $this->request('GET', $endpoint, null, $options);
    }

    /**
     * POST request simplificado
     */
    public function post(string $endpoint, array $data, array $options = []): ?array
    {
        return $this->request('POST', $endpoint, $data, $options);
    }

    /**
     * PUT request simplificado
     */
    public function put(string $endpoint, array $data, array $options = []): ?array
    {
        return $this->request('PUT', $endpoint, $data, $options);
    }

    /**
     * DELETE request simplificado
     */
    public function delete(string $endpoint, array $options = []): ?array
    {
        return $this->request('DELETE', $endpoint, null, $options);
    }
}