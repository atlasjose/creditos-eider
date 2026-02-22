<?php

namespace App\Http\Controllers;

use App\Models\Deudor;
use App\Models\TarjetaDeudor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DeudorController extends Controller
{
    /**
     * Listar todos los deudores
     */
    public function index(Request $request)
    {
        $query = Deudor::with(['ruta.pueblo', 'tarjetaActiva']);

        // Filtros opcionales
        if ($request->has('id_ruta')) {
            $query->where('id_ruta', $request->id_ruta);
        }

        if ($request->has('dia_cobro')) {
            $query->where('dia_cobro_preferido', $request->dia_cobro);
        }

        if ($request->has('con_ubicacion')) {
            $query->conUbicacion();
        }

        $deudores = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $deudores
        ]);
    }

    /**
     * Crear un nuevo deudor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_deudor' => 'required|string|max:20|unique:deudores',
            'cedula' => 'required|string|max:20|unique:deudores',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'direccion' => 'required|string',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'telefono_principal' => 'required|string|max:20',
            'id_ruta' => 'required|exists:rutas_cobro,id_ruta',
            'dia_cobro_preferido' => 'nullable|integer|between:1,7',
            'limite_credito' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Crear deudor
            $deudor = Deudor::create([
                'codigo_deudor' => $request->codigo_deudor,
                'cedula' => $request->cedula,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'direccion' => $request->direccion,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'telefono_principal' => $request->telefono_principal,
                'id_ruta' => $request->id_ruta,
                'dia_cobro_preferido' => $request->dia_cobro_preferido,
                'recordatorio_diario' => is_null($request->dia_cobro_preferido),
                'id_creado_por' => auth()->id(),
            ]);

            // Crear tarjeta automáticamente
            $tarjeta = TarjetaDeudor::create([
                'codigo_tarjeta' => 'CARD-' . $request->codigo_deudor,
                'id_deudor' => $deudor->id_deudor,
                'fecha_emision' => now(),
                'limite_credito' => $request->limite_credito,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deudor creado exitosamente',
                'data' => $deudor->load(['ruta', 'tarjetaActiva'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear deudor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un deudor específico
     */
    public function show($id)
    {
        $deudor = Deudor::with(['ruta.pueblo', 'tarjetas', 'creador'])
                        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $deudor
        ]);
    }

    /**
     * Actualizar un deudor (incluye coordenadas GPS)
     */
    public function update(Request $request, $id)
    {
        $deudor = Deudor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'direccion' => 'sometimes|string',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'telefono_principal' => 'sometimes|string|max:20',
            'dia_cobro_preferido' => 'nullable|integer|between:1,7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deudor->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Deudor actualizado exitosamente',
            'data' => $deudor->load('ruta')
        ]);
    }

    /**
     * Actualizar solo las coordenadas GPS de un deudor
     */
    public function updateUbicacion(Request $request, $id)
    {
        $deudor = Deudor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deudor->update([
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación GPS actualizada exitosamente',
            'data' => $deudor
        ]);
    }

    /**
     * Buscar deudores cercanos a una ubicación
     */
    public function cercanos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'radio_km' => 'nullable|numeric|min:0.1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deudores = Deudor::cercanos(
            $request->latitud,
            $request->longitud,
            $request->radio_km ?? 5
        )
        ->with(['ruta', 'tarjetaActiva'])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $deudores,
            'total' => $deudores->count()
        ]);
    }

    /**
     * Eliminar un deudor (soft delete)
     */
    public function destroy($id)
    {
        $deudor = Deudor::findOrFail($id);
        
        // En lugar de eliminar, desactivar las tarjetas
        $deudor->tarjetas()->update(['estado_tarjeta' => 'CANCELADA']);

        return response()->json([
            'success' => true,
            'message' => 'Deudor eliminado exitosamente'
        ]);
    }
}