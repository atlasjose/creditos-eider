<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo VENDEDOR o JEFE pueden crear ventas
        return $this->user() && 
               ($this->user()->isVendedor() || $this->user()->isJefe());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_tarjeta' => 'required|exists:tarjetas_deudor,id_tarjeta',
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad' => 'required|integer|min:1',
            'modalidad' => 'required|in:DIARIO,SEMANAL,QUINCENAL,MENSUAL',
            'cuotas_totales' => 'required|integer|min:1|max:36',
            'id_vendedor' => 'nullable|exists:usuarios,id_usuario',
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
            'id_tarjeta.required' => 'La tarjeta es requerida',
            'id_tarjeta.exists' => 'La tarjeta seleccionada no existe',
            'id_producto.required' => 'El producto es requerido',
            'id_producto.exists' => 'El producto seleccionado no existe',
            'cantidad.required' => 'La cantidad es requerida',
            'cantidad.min' => 'La cantidad debe ser al menos 1',
            'modalidad.required' => 'La modalidad de pago es requerida',
            'modalidad.in' => 'La modalidad debe ser DIARIO, SEMANAL, QUINCENAL o MENSUAL',
            'cuotas_totales.required' => 'El número de cuotas es requerido',
            'cuotas_totales.min' => 'Debe haber al menos 1 cuota',
            'cuotas_totales.max' => 'No se pueden crear más de 36 cuotas',
            'id_vendedor.exists' => 'El vendedor seleccionado no existe',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Si no se especifica vendedor, usar el usuario autenticado
        if (!$this->has('id_vendedor')) {
            $this->merge([
                'id_vendedor' => $this->user()->id_usuario
            ]);
        }
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
                'message' => 'No tienes permisos para crear ventas'
            ], 403)
        );
    }
}