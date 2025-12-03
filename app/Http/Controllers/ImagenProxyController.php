<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Controlador Proxy de Imágenes
 * 
 * Sirve imágenes desde Spring Boot sin duplicarlas en Laravel
 * Incluye caché para optimizar rendimiento
 */
class ImagenProxyController extends Controller
{
    private string $springBootUrl;

    public function __construct()
    {
        $this->springBootUrl = config('services.spring_api.url', 'http://localhost:8080/api');
        // Remover /api del final si existe, ya que las imágenes están en /assets
        $this->springBootUrl = str_replace('/api', '', $this->springBootUrl);
    }

    /**
     * Proxy para vistas de anillos
     * GET /imagen/vista-anillo
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function vistaAnillo(Request $request)
    {
        try {
            // Validar parámetros
            $gema = $request->query('gema', 'diamante');
            $forma = $request->query('forma', 'redonda');
            $material = $request->query('material', 'oro-amarillo');
            $vista = $request->query('vista', 'frontal');

            // Normalizar valores (lowercase, sin espacios)
            $gema = $this->normalizarSlug($gema);
            $forma = $this->normalizarSlug($forma);
            $material = $this->normalizarSlug($material);
            $vista = $this->normalizarSlug($vista);

            // Construir ruta de la imagen en Spring Boot
            $rutaImagen = "/assets/img/personalizacion/vistas-anillos/{$gema}/{$forma}/{$material}/{$vista}.jpg";

            // Intentar obtener de caché (caché por 1 hora)
            $cacheKey = "imagen_anillo_{$gema}_{$forma}_{$material}_{$vista}";
            
            $imagenData = Cache::remember($cacheKey, 3600, function () use ($rutaImagen) {
                return $this->obtenerImagenDeSpringBoot($rutaImagen);
            });

            if ($imagenData === null) {
                // Si no se encuentra, retornar imagen placeholder
                return $this->retornarPlaceholder();
            }

            // Retornar la imagen con headers correctos
            return response($imagenData)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('X-Proxy-Source', 'spring-boot');

        } catch (\Exception $e) {
            Log::error('ImagenProxyController: Error al obtener imagen', [
                'error' => $e->getMessage(),
                'params' => $request->query()
            ]);

            return $this->retornarPlaceholder();
        }
    }

    /**
     * Proxy para iconos de opciones
     * GET /imagen/icono-opcion
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function iconoOpcion(Request $request)
    {
        try {
            $categoria = $request->query('categoria', 'forma');
            $archivo = $request->query('archivo', '');

            if (empty($archivo)) {
                return $this->retornarPlaceholder();
            }

            // Mapear categorías
            $categoriaMap = [
                'forma' => 'forma',
                'gema' => 'gemas',
                'material' => 'material'
            ];

            $carpetaCategoria = $categoriaMap[$categoria] ?? $categoria;

            // Construir ruta
            $rutaImagen = "/assets/img/personalizacionproductos/opciones/{$carpetaCategoria}/{$archivo}";

            // Caché por 1 día (los iconos cambian menos)
            $cacheKey = "icono_opcion_{$categoria}_{$archivo}";
            
            $imagenData = Cache::remember($cacheKey, 86400, function () use ($rutaImagen) {
                return $this->obtenerImagenDeSpringBoot($rutaImagen);
            });

            if ($imagenData === null) {
                return $this->retornarPlaceholder();
            }

            // Detectar tipo de imagen
            $extension = pathinfo($archivo, PATHINFO_EXTENSION);
            $mimeType = $this->obtenerMimeType($extension);

            return response($imagenData)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=86400')
                ->header('X-Proxy-Source', 'spring-boot');

        } catch (\Exception $e) {
            Log::error('ImagenProxyController: Error al obtener icono', [
                'error' => $e->getMessage(),
                'params' => $request->query()
            ]);

            return $this->retornarPlaceholder();
        }
    }

    /**
     * Obtiene la imagen desde Spring Boot
     * 
     * @param string $ruta
     * @return string|null
     */
    private function obtenerImagenDeSpringBoot(string $ruta): ?string
    {
        try {
            $url = $this->springBootUrl . $ruta;

            Log::debug('ImagenProxyController: Solicitando imagen', [
                'url' => $url
            ]);

            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            Log::warning('ImagenProxyController: Imagen no encontrada', [
                'url' => $url,
                'status' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('ImagenProxyController: Error HTTP', [
                'ruta' => $ruta,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Normaliza un slug (lowercase, sin espacios, sin acentos)
     * 
     * @param string $texto
     * @return string
     */
    private function normalizarSlug(string $texto): string
    {
        return strtolower(
            str_replace(
                [' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'],
                ['-', 'a', 'e', 'i', 'o', 'u', 'n'],
                trim($texto)
            )
        );
    }

    /**
     * Obtiene el MIME type según la extensión
     * 
     * @param string $extension
     * @return string
     */
    private function obtenerMimeType(string $extension): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml'
        ];

        return $mimeTypes[strtolower($extension)] ?? 'image/jpeg';
    }

    /**
     * Retorna una imagen placeholder cuando falla la carga
     * 
     * @return \Illuminate\Http\Response
     */
    private function retornarPlaceholder()
    {
        // Generar un placeholder SVG simple
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f1f5f9"/>
            <circle cx="200" cy="180" r="60" fill="#cbd5e1"/>
            <path d="M140 260 L260 260 L200 320 Z" fill="#cbd5e1"/>
            <text x="200" y="360" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#64748b">
                Imagen no disponible
            </text>
        </svg>';

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache');
    }

    /**
     * Limpiar caché de imágenes (útil para desarrollo)
     * GET /imagen/limpiar-cache
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function limpiarCache()
    {
        try {
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Caché de imágenes limpiado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar el caché: ' . $e->getMessage()
            ], 500);
        }
    }
}