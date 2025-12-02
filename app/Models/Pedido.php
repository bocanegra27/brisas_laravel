<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    // 1. Nombre de la tabla
    protected $table = 'pedido';

    // 2. Llave primaria
    protected $primaryKey = 'ped_id';

    // 3. Configuración de fechas (Timestamps)
    // Mapeamos 'created_at' a tu columna real
    const CREATED_AT = 'ped_fecha_creacion';
    
    // Como tu tabla no tiene columna para 'updated_at', lo desactivamos
    const UPDATED_AT = null; 

    protected $fillable = [
        'ped_codigo',
        'ped_comentarios',
        'est_id',
        'per_id',
        'usu_id_empleado'
    ];

    // --- RELACIONES ---

    // Relación con Estado
    public function estado()
    {
        // params: Modelo, Foreign Key local, Primary Key foránea
        return $this->belongsTo(EstadoPedido::class, 'est_id', 'est_id');
    }
}