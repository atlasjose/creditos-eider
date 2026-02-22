<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo COBRADOR o JEFE pueden registrar pagos
        return $this->user() && 
               ($this->user()->isCobrador() || $this->user()->isJefe());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_cuota' => 'required|exists:cuotas,id_cuota',
            'monto_abonado' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:EFECTIVO,TRANSFERENCIA,DATAFONO,OTRO',
            'id_cobrador' => 'nullable|exists:usuarios,id_usuario',
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
            'id_cuota.required' => 'La cuota es requerida',
            'id_cuota.exists' => 'La cuota seleccionada no existe',
            'monto_abonado.required' => 'El monto a abonar es requerido',
            'monto_abonado.min' => 'El monto debe ser mayor a 0',
            'metodo_pago.required' => 'El método de pago es requerido',
            'metodo_pago.in' => 'El método de pago no es válido',
            'id_cobrador.exists' => 'El cobrador seleccionado no existe',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Si no se especifica cobrador, usar el usuario autenticado
        if (!$this->has('id_cobrador')) {
            $this->merge([
                'id_cobrador' => $this->user()->id_usuario
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
                'message' => 'No tienes permisos para registrar pagos'
            ], 403)
        );
    }
}