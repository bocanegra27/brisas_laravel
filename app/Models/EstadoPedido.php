<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    // 1. Nombre exacto de la tabla en tu BD
    protected $table = 'estado_pedido';

    // 2. Tu llave primaria personalizada
    protected $primaryKey = 'est_id';

    // 3. Desactivar timestamps (tu tabla no tiene created_at)
    public $timestamps = false;

    // 4. Campos que se pueden llenar masivamente
    protected $fillable = ['est_nombre'];

    // Relación: Un estado tiene muchos pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'est_id', 'est_id');
    }
}