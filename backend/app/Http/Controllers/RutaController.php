<?php

namespace App\Http\Controllers;

use App\Models\RutaCobro;
use App\Models\Pueblo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RutaController extends Controller
{
    /**
     * Listar todas las rutas
     */
    public function index(Request $request)
    {
        $query = RutaCobro::with(['pueblo', 'cobrador', 'deudores']);

        // Filtro por pueblo
        if ($request->has('id_pueblo')) {
            $query->where('id_pueblo', $request->id_pueblo);
        }

        // Filtro por cobrador
        if ($request->has('id_cobrador')) {
            $query->where('id_cobrador_asignado', $request->id_cobrador);
        }

        // Filtro por estado
        if ($request->has('activa')) {
            $query->where('activa', $request->activa === 'true' || $request->activa === '1');
        }

        // Solo rutas activas por defecto
        if (!$request->has('todas')) {
            $query->activas();
        }

        $rutas = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $rutas
        ]);
    }

    /**
     * Crear una nueva ruta
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_ruta' => 'required|string|max:50',
            'id_pueblo' => 'required|exists:pueblos,id_pueblo',
            'id_cobrador_asignado' => 'nullable|exists:usuarios,id_usuario',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el usuario asignado sea cobrador
        if ($request->id_cobrador_asignado) {
            $cobrador = Usuario::findOrFail($request->id_cobrador_asignado);
            if (!$cobrador->isCobrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario asignado debe tener rol de COBRADOR'
                ], 400);
            }
        }

        $ruta = RutaCobro::create([
            'nombre_ruta' => $request->nombre_ruta,
            'id_pueblo' => $request->id_pueblo,
            'id_cobrador_asignado' => $request->id_cobrador_asignado,
            'activa' => true,
            'fecha_asignacion' => $request->id_cobrador_asignado ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ruta creada exitosamente',
            'data' => $ruta->load(['pueblo', 'cobrador'])
        ], 201);
    }

    /**
     * Obtener una ruta específica
     */
    public function show($id)
    {
        $ruta = RutaCobro::with([
            'pueblo',
            'cobrador',
            'deudores' => function($query) {
                $query->with(['tarjetaActiva']);
            }
        ])->findOrFail($id);

        // Estadísticas de la ruta
        $estadisticas = [
            'total_deudores' => $ruta->deudores->count(),
            'deudores_con_ubicacion' => $ruta->deudores->filter(function($d) {
                return !is_null($d->latitud) && !is_null($d->longitud);
            })->count(),
            'total_credito_activo' => $ruta->deudores->sum(function($deudor) {
                return $deudor->tarjetaActiva ? $deudor->tarjetaActiva->saldo_actual : 0;
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'ruta' => $ruta,
                'estadisticas' => $estadisticas
            ]
        ]);
    }

    /**
     * Actualizar una ruta
     */
    public function update(Request $request, $id)
    {
        $ruta = RutaCobro::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre_ruta' => 'sometimes|string|max:50',
            'id_pueblo' => 'sometimes|exists:pueblos,id_pueblo',
            'id_cobrador_asignado' => 'nullable|exists:usuarios,id_usuario',
            'activa' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el usuario asignado sea cobrador
        if ($request->has('id_cobrador_asignado') && $request->id_cobrador_asignado) {
            $cobrador = Usuario::findOrFail($request->id_cobrador_asignado);
            if (!$cobrador->isCobrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario asignado debe tener rol de COBRADOR'
                ], 400);
            }
        }

        // Si se asigna un nuevo cobrador, actualizar fecha
        if ($request->has('id_cobrador_asignado')) {
            $request->merge(['fecha_asignacion' => now()]);
        }

        $ruta->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Ruta actualizada exitosamente',
            'data' => $ruta->load(['pueblo', 'cobrador'])
        ]);
    }

    /**
     * Obtener deudores de una ruta
     */
    public function deudores($id, Request $request)
    {
        $ruta = RutaCobro::findOrFail($id);

        $query = $ruta->deudores()->with(['tarjetaActiva']);

        // Filtro por día de cobro
        if ($request->has('dia_cobro')) {
            $query->where('dia_cobro_preferido', $request->dia_cobro);
        }

        // Solo deudores con ubicación GPS
        if ($request->has('con_ubicacion') && $request->con_ubicacion) {
            $query->conUbicacion();
        }

        $deudores = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'ruta' => $ruta->load(['pueblo', 'cobrador']),
                'deudores' => $deudores
            ]
        ]);
    }

    /**
     * Asignar cobrador a una ruta
     */
    public function asignarCobrador(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_cobrador' => 'required|exists:usuarios,id_usuario',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ruta = RutaCobro::findOrFail($id);
        $cobrador = Usuario::findOrFail($request->id_cobrador);

        if (!$cobrador->isCobrador()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario debe tener rol de COBRADOR'
            ], 400);
        }

        $ruta->update([
            'id_cobrador_asignado' => $request->id_cobrador,
            'fecha_asignacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cobrador asignado exitosamente',
            'data' => $ruta->load(['cobrador', 'pueblo'])
        ]);
    }

    /**
     * Obtener rutas de un cobrador específico
     */
    public function porCobrador($id)
    {
        $cobrador = Usuario::findOrFail($id);

        if (!$cobrador->isCobrador()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es un cobrador'
            ], 400);
        }

        $rutas = RutaCobro::with(['pueblo', 'deudores'])
                         ->where('id_cobrador_asignado', $id)
                         ->activas()
                         ->get();

        // Estadísticas
        $estadisticas = [
            'total_rutas' => $rutas->count(),
            'total_deudores' => $rutas->sum(function($ruta) {
                return $ruta->deudores->count();
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'rutas' => $rutas,
                'estadisticas' => $estadisticas,
                'cobrador' => $cobrador
            ]
        ]);
    }

    /**
     * Desactivar una ruta
     */
    public function desactivar($id)
    {
        $ruta = RutaCobro::findOrFail($id);

        $ruta->update(['activa' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Ruta desactivada exitosamente'
        ]);
    }

    /**
     * Activar una ruta
     */
    public function activar($id)
    {
        $ruta = RutaCobro::findOrFail($id);

        $ruta->update(['activa' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Ruta activada exitosamente'
        ]);
    }
}