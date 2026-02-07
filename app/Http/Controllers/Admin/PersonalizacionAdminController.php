<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PersonalizacionAdminController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    // 1. LISTAR CATEGORÍAS (Anillos, Pulseras...)
    public function indexCategorias()
    {
        $categorias = $this->apiService->get('/categorias', [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        return view('admin.personalizacion.categorias.index', [
            'categorias' => $categorias ?? []
        ]);
    }

    // 2. LISTAR OPCIONES FILTRADAS POR CATEGORÍA
    public function indexOpciones(Request $request)
    {
        $catId = $request->query('catId');
        if (!$catId) return redirect()->route('admin.personalizacion.categorias.index');

        // Obtenemos las opciones de esa categoría específica
        $opciones = $this->apiService->get("/opciones?catId={$catId}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        // Necesitamos el nombre de la categoría para el título de la vista
        $categorias = $this->apiService->get('/categorias', [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);
        $categoriaActual = collect($categorias)->firstWhere('id', $catId);

        return view('admin.personalizacion.opciones.index', [
            'opciones' => $opciones ?? [],
            'categoria' => $categoriaActual,
            'catId' => $catId
        ]);
    }

    // 3. LISTAR VALORES FILTRADOS POR OPCIÓN
    public function indexValores(Request $request)
    {
        $opcId = $request->query('opcId');
        if (!$opcId) return redirect()->route('admin.personalizacion.categorias.index');

        // 1. Obtener la lista de valores (imágenes)
        $valores = $this->apiService->get("/valores?opcId={$opcId}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        // 2. Obtener los detalles de la Opción actual (para saber su nombre y su catId)
        $opcion = $this->apiService->get("/opciones/{$opcId}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        // 3. === LO QUE FALTABA: OBTENER EL SLUG DE LA CATEGORÍA ===
        // Necesitamos saber si es "anillos" o "pulseras" para la ruta de la imagen.
        // Traemos todas las categorías y buscamos la que coincida con el catId de la opción.
        $categorias = $this->apiService->get('/categorias', [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);
        
        // Buscamos la categoría padre usando Collections de Laravel
        $categoriaPadre = collect($categorias)->firstWhere('id', $opcion['catId'] ?? null);
        $catSlug = $categoriaPadre['slug'] ?? 'general'; // 'general' es un fallback por si falla

        // 4. Retornar la vista con TODAS las variables
        return view('admin.personalizacion.valores.index', [
            'valores' => $valores ?? [],
            'opcion'  => $opcion,
            'opcId'   => $opcId,
            'catSlug' => $catSlug // <--- ¡AQUÍ ESTÁ LA SOLUCIÓN!
        ]);
    }

    // GUARDAR NUEVA CATEGORÍA
    public function storeCategoria(Request $request)
    {
        // 1. Validación en Laravel
        $request->validate([
            'nombre' => 'required|string|max:100',
            'slug'   => 'required|string|max:100|regex:/^[a-z0-9-]+$/'
        ]);

        // 2. Enviar a Spring Boot
        // Nota: Asegúrate de que tu DTO en Spring (CategoriaProductoCreateDTO) espere 'nombre' y 'slug'
        // O si tu entidad los genera, ajusta esto. En tu SQL el slug era manual o automático?
        // Vamos a enviarlo manual porque el script JS ya lo limpió.
        
        $response = $this->apiService->post('/categorias', [
            'nombre' => $request->nombre,
            'slug'   => $request->slug
        ], [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        if ($response) {
            return redirect()->route('admin.personalizacion.categorias.index')
                             ->with('success', 'Categoría creada correctamente.');
        }

        return back()->with('error', 'Error al conectar con el servidor.');
    }

    public function eliminarCategoria($id)
    {
        // Llamamos al endpoint DELETE de Spring Boot
        $response = $this->apiService->delete("/categorias/{$id}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        if ($response) {
            return back()->with('success', 'Categoría y sus opciones eliminadas correctamente.');
        }

        return back()->with('error', 'No se pudo eliminar. Puede que tenga pedidos asociados.');
    }

    // GUARDAR NUEVA OPCIÓN (Ej: "Tipo de Cierre" para Pulseras)
    public function storeOpcion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'catId'  => 'required|integer' // Necesitamos saber de quién es hijo
        ]);

        // Enviamos a Spring Boot
        $response = $this->apiService->post('/opciones', [
            'nombre' => $request->nombre,
            'catId'  => $request->catId
        ], [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        if ($response) {
            return back()->with('success', 'Opción creada correctamente.');
        }

        return back()->with('error', 'Error al guardar la opción.');
    }

    // ELIMINAR OPCIÓN
    public function eliminarOpcion($id)
    {
        $response = $this->apiService->delete("/opciones/{$id}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        if ($response) {
            return back()->with('success', 'Opción eliminada correctamente.');
        }

        return back()->with('error', 'No se pudo eliminar la opción.');
        
    }

public function storeValor(Request $request)
    {
        // Validamos SOLO el nombre y el ID
        $request->validate([
            'nombre' => 'required|string|max:100',
            'opcId'  => 'required|integer'
        ]);

        // Enviamos a Spring Boot usando postMultipart pero SIN archivo
        // Esto funciona porque en Java pusimos required=false
        $response = $this->apiService->postMultipart('/valores', [
            'nombre' => $request->nombre,
            'opcId'  => $request->opcId
        ], null, 'archivo'); // null en el archivo

        if ($response) {
            return back()->with('success', 'Valor creado correctamente.');
        }

        return back()->with('error', 'Error al crear el valor.');
    }

    public function eliminarValor($id)
    {
        $response = $this->apiService->delete("/valores/{$id}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        if ($response) {
            return back()->with('success', 'Valor eliminado correctamente.');
        }

        return back()->with('error', 'No se pudo eliminar el valor.');
    }

    public function subirVista(Request $request)
    {
        $request->validate([
            'valorId' => 'required|integer',
            'tipo'    => 'required|string|in:frontal,superior,perfil',
            'archivo' => 'required|image|mimes:png,jpg,webp|max:3000'
        ]);

        // URL: /valores/{id}/vistas
        // Enviamos: tipo (text), archivo (file)
        $response = $this->apiService->postMultipart("/valores/{$request->valorId}/vistas", [
            'tipo' => $request->tipo
        ], $request->file('archivo'), 'archivo');

        if ($response) {
            return back()->with('success', "Vista '{$request->tipo}' subida correctamente.");
        }

        return back()->with('error', 'Error al subir la vista.');
    }



}