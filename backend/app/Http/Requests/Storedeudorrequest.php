<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDeudorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo JEFE puede crear deudores
        return $this->user() && $this->user()->isJefe();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo_deudor' => 'required|string|max:20|unique:deudores,codigo_deudor',
            'cedula' => 'required|string|max:20|unique:deudores,cedula',
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'direccion' => 'required|string|max:200',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'telefono_principal' => 'required|string|max:20',
            'telefono_secundario' => 'nullable|string|max:20',
            'id_ruta' => 'required|exists:rutas_cobro,id_ruta',
            'dia_cobro_preferido' => 'nullable|integer|between:1,7',
            'recordatorio_diario' => 'nullable|boolean',
            'limite_credito' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'codigo_deudor.required' => 'El código del deudor es requerido',
            'codigo_deudor.unique' => 'Este código de deudor ya existe',
            'cedula.required' => 'La cédula es requerida',
            'cedula.unique' => 'Esta cédula ya está registrada',
            'nombres.required' => 'Los nombres son requeridos',
            'apellidos.required' => 'Los apellidos son requeridos',
            'direccion.required' => 'La dirección es requerida',
            'latitud.between' => 'La latitud debe estar entre -90 y 90',
            'longitud.between' => 'La longitud debe estar entre -180 y 180',
            'telefono_principal.required' => 'El teléfono principal es requerido',
            'id_ruta.required' => 'La ruta es requerida',
            'id_ruta.exists' => 'La ruta seleccionada no existe',
            'dia_cobro_preferido.between' => 'El día de cobro debe estar entre 1 (Lunes) y 7 (Domingo)',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear deudores'
            ], 403)
        );
    }
}