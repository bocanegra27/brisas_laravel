<?php

namespace App\Services\Pedido;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    /**
     * Obtener todos los pedidos con su estado cargado.
     * Reemplaza a tu antigua función 'listarPedidos'.
     */
    public function getAllPedidos(): Collection
    {
        // 'with' evita hacer cientos de consultas SQL (problema N+1)
        return Pedido::with('estado')
            ->orderBy('ped_fecha_creacion', 'desc')
            ->get();
    }

    /**
     * Obtener un pedido por ID.
     */
    public function getPedidoById(int $id): ?Pedido
    {
        return Pedido::with('estado')->find($id);
    }

    /**
     * Crear un nuevo pedido en la base de datos.
     */
    public function createPedido(array $data): Pedido
    {
        // Usamos transacciones: si algo falla, no se guarda nada (integridad de datos)
        return DB::transaction(function () use ($data) {
            return Pedido::create($data);
        });
    }

    /**
     * Actualizar un pedido existente.
     */
    public function updatePedido(int $id, array $data): Pedido
    {
        return DB::transaction(function () use ($id, $data) {
            $pedido = Pedido::findOrFail($id); // Si no existe, lanza error 404 automáticamente
            $pedido->update($data);
            return $pedido;
        });
    }

    /**
     * Eliminar un pedido.
     */
    public function deletePedido(int $id): bool
    {
        $pedido = Pedido::findOrFail($id);
        return $pedido->delete();
    }

    /**
     * Buscar pedidos por nombre de estado (ej: 'diseño').
     * Reemplaza tu lógica manual de filtrado.
     */
    public function getPedidosByEstado(string $nombreEstado): Collection
    {
        // Eloquent avanzado: Buscamos pedidos DONDE su relación 'estado' tenga el nombre X
        return Pedido::whereHas('estado', function($query) use ($nombreEstado) {
            $query->where('est_nombre', $nombreEstado);
        })->with('estado')->get();
    }
}