<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     * Por ahora true, luego aquí pondremos la seguridad (roles).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
{
    return [
        // 'pedCodigo' => 'required...',  <-- ¡BORRA O COMENTA ESTA LÍNEA!
        'pedComentarios' => 'required|string',
        'estId' => 'nullable|integer',
        'perId' => 'nullable|integer',
        'usuId' => 'nullable|integer',
    ];
}

    /**
     * Preparar los datos antes de validar.
     * Aquí convertimos tus inputs del HTML (pedCodigo) a columnas de BD (ped_codigo).
     */
    protected function prepareForValidation()
    {
        // Solo intentamos mezclar si los datos existen en la petición
        $mergeData = [];
        
        if ($this->has('pedCodigo')) {
            $mergeData['ped_codigo'] = $this->input('pedCodigo');
        }
        if ($this->has('pedComentarios')) {
            $mergeData['ped_comentarios'] = $this->input('pedComentarios');
        }
        if ($this->has('estId')) {
            $mergeData['est_id'] = $this->input('estId');
        }
        if ($this->has('perId')) {
            $mergeData['per_id'] = $this->input('perId');
        }
        if ($this->has('usuId')) {
            $mergeData['usu_id_empleado'] = $this->input('usuId');
        }

        $this->merge($mergeData);
    }
}