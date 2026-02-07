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

    // 3. LISTAR VALORES FILTRADOS POR OPCIÓN (Aquí es donde se ven las imágenes)
    public function indexValores(Request $request)
    {
        $opcId = $request->query('opcId');
        if (!$opcId) return redirect()->route('admin.personalizacion.categorias.index');

        $valores = $this->apiService->get("/valores?opcId={$opcId}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        // Obtenemos los detalles de la opción para el contexto
        $opcion = $this->apiService->get("/opciones/{$opcId}", [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        return view('admin.personalizacion.valores.index', [
            'valores' => $valores ?? [],
            'opcion' => $opcion,
            'opcId' => $opcId
        ]);
    }

    // 4. GUARDAR VALOR CON IMAGEN (EL MULTIPART)
    public function storeValor(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'opcId' => 'required',
            'archivo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Preparamos el envío Multipart para Spring Boot
        $response = $this->apiService->postMultipart('/valores', [
            'nombre' => $request->nombre,
            'opcId' => $request->opcId
        ], $request->file('archivo'), 'archivo');

        if ($response) {
            return back()->with('success', 'Valor y archivo guardados correctamente en el servidor.');
        }

        return back()->with('error', 'No se pudo guardar el valor.');
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

}