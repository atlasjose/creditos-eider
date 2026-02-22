<?php

/**
 * ==========================================
 * FUNCIONES AUXILIARES DEL SISTEMA
 * Sistema de Gestión de Créditos Eider
 * ==========================================
 * 
 * Para usar estas funciones en cualquier parte del sistema,
 * asegúrate de cargar este archivo en composer.json:
 * 
 * "autoload": {
 *     "files": [
 *         "app/Helpers/helpers.php"
 *     ]
 * }
 * 
 * Luego ejecuta: composer dump-autoload
 */

use Illuminate\Support\Str;

if (!function_exists('formatMoney')) {
    /**
     * Formatear un número como moneda colombiana.
     *
     * @param  float  $amount
     * @param  bool  $includeSymbol
     * @return string
     */
    function formatMoney($amount, $includeSymbol = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        return $includeSymbol ? '$' . $formatted : $formatted;
    }
}

if (!function_exists('formatPercentage')) {
    /**
     * Formatear un número como porcentaje.
     *
     * @param  float  $number
     * @param  int  $decimals
     * @return string
     */
    function formatPercentage($number, $decimals = 2): string
    {
        return number_format($number, $decimals) . '%';
    }
}

if (!function_exists('calcularDistanciaGPS')) {
    /**
     * Calcular distancia entre dos puntos GPS usando fórmula Haversine.
     *
     * @param  float  $lat1  Latitud punto 1
     * @param  float  $lng1  Longitud punto 1
     * @param  float  $lat2  Latitud punto 2
     * @param  float  $lng2  Longitud punto 2
     * @return float  Distancia en kilómetros
     */
    function calcularDistanciaGPS($lat1, $lng1, $lat2, $lng2): float
    {
        $radioTierra = 6371; // Radio de la Tierra en km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distancia = $radioTierra * $c;

        return round($distancia, 2);
    }
}

if (!function_exists('generarCodigoDeudor')) {
    /**
     * Generar código único para deudor.
     *
     * @param  string|null  $prefix
     * @return string
     */
    function generarCodigoDeudor($prefix = 'DEU'): string
    {
        $timestamp = time();
        $random = rand(100, 999);
        return strtoupper($prefix) . '-' . $timestamp . $random;
    }
}

if (!function_exists('generarCodigoTarjeta')) {
    /**
     * Generar código único para tarjeta.
     *
     * @return string
     */
    function generarCodigoTarjeta(): string
    {
        return 'TC-' . strtoupper(Str::random(10));
    }
}

if (!function_exists('diasSemana')) {
    /**
     * Obtener array de días de la semana.
     *
     * @return array
     */
    function diasSemana(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
    }
}

if (!function_exists('getNombreDia')) {
    /**
     * Obtener nombre del día por número.
     *
     * @param  int  $dia
     * @return string
     */
    function getNombreDia($dia): string
    {
        $dias = diasSemana();
        return $dias[$dia] ?? 'Desconocido';
    }
}

if (!function_exists('calcularComision')) {
    /**
     * Calcular comisión basada en monto y porcentaje.
     *
     * @param  float  $monto
     * @param  float  $porcentaje
     * @return float
     */
    function calcularComision($monto, $porcentaje): float
    {
        return round(($monto * $porcentaje) / 100, 2);
    }
}

if (!function_exists('esVencida')) {
    /**
     * Verificar si una fecha está vencida.
     *
     * @param  string|\Carbon\Carbon  $fecha
     * @return bool
     */
    function esVencida($fecha): bool
    {
        return \Carbon\Carbon::parse($fecha)->isPast();
    }
}

if (!function_exists('diasVencidos')) {
    /**
     * Calcular días vencidos desde una fecha.
     *
     * @param  string|\Carbon\Carbon  $fecha
     * @return int
     */
    function diasVencidos($fecha): int
    {
        $fechaCarbon = \Carbon\Carbon::parse($fecha);
        
        if ($fechaCarbon->isFuture()) {
            return 0;
        }

        return $fechaCarbon->diffInDays(\Carbon\Carbon::now());
    }
}

if (!function_exists('formatearTelefono')) {
    /**
     * Formatear número telefónico colombiano.
     *
     * @param  string  $telefono
     * @return string
     */
    function formatearTelefono($telefono): string
    {
        // Remover espacios y guiones
        $telefono = preg_replace('/[\s\-]/', '', $telefono);

        // Si es celular (10 dígitos)
        if (strlen($telefono) === 10) {
            return substr($telefono, 0, 3) . ' ' . 
                   substr($telefono, 3, 3) . ' ' . 
                   substr($telefono, 6, 4);
        }

        // Si es teléfono fijo (7 dígitos)
        if (strlen($telefono) === 7) {
            return substr($telefono, 0, 3) . ' ' . 
                   substr($telefono, 3, 4);
        }

        return $telefono;
    }
}

if (!function_exists('calcularProximaFechaCobro')) {
    /**
     * Calcular próxima fecha de cobro según modalidad.
     *
     * @param  string|\Carbon\Carbon  $fechaBase
     * @param  string  $modalidad  DIARIO, SEMANAL, QUINCENAL, MENSUAL
     * @param  int  $numeroCuota
     * @return \Carbon\Carbon
     */
    function calcularProximaFechaCobro($fechaBase, $modalidad, $numeroCuota = 1): \Carbon\Carbon
    {
        $fecha = \Carbon\Carbon::parse($fechaBase);

        switch (strtoupper($modalidad)) {
            case 'DIARIO':
                return $fecha->addDays($numeroCuota);
            case 'SEMANAL':
                return $fecha->addWeeks($numeroCuota);
            case 'QUINCENAL':
                return $fecha->addDays($numeroCuota * 15);
            case 'MENSUAL':
                return $fecha->addMonths($numeroCuota);
            default:
                return $fecha;
        }
    }
}

if (!function_exists('responseJson')) {
    /**
     * Crear respuesta JSON estandarizada.
     *
     * @param  bool  $success
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    function responseJson($success, $message, $data = null, $code = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }
}

if (!function_exists('sanitizarCedula')) {
    /**
     * Sanitizar número de cédula (remover puntos y espacios).
     *
     * @param  string  $cedula
     * @return string
     */
    function sanitizarCedula($cedula): string
    {
        return preg_replace('/[\s\.\-]/', '', $cedula);
    }
}

if (!function_exists('validarCoordenadasGPS')) {
    /**
     * Validar que las coordenadas GPS sean válidas.
     *
     * @param  float  $latitud
     * @param  float  $longitud
     * @return bool
     */
    function validarCoordenadasGPS($latitud, $longitud): bool
    {
        return ($latitud >= -90 && $latitud <= 90) && 
               ($longitud >= -180 && $longitud <= 180);
    }
}

if (!function_exists('obtenerEstadoCuota')) {
    /**
     * Obtener el estado visual de una cuota.
     *
     * @param  string  $estado
     * @param  string|\Carbon\Carbon  $fechaVencimiento
     * @return array
     */
    function obtenerEstadoCuota($estado, $fechaVencimiento): array
    {
        $estados = [
            'PENDIENTE' => [
                'label' => 'Pendiente',
                'color' => 'warning',
                'icon' => '⏳'
            ],
            'PAGADA' => [
                'label' => 'Pagada',
                'color' => 'success',
                'icon' => '✓'
            ],
            'VENCIDA' => [
                'label' => 'Vencida',
                'color' => 'danger',
                'icon' => '⚠'
            ]
        ];

        // Si está pendiente pero vencida
        if ($estado === 'PENDIENTE' && esVencida($fechaVencimiento)) {
            return $estados['VENCIDA'];
        }

        return $estados[$estado] ?? $estados['PENDIENTE'];
    }
}

if (!function_exists('generarPeriodoComision')) {
    /**
     * Generar fechas de inicio y fin para período de comisión.
     *
     * @param  string  $periodicidad  SEMANAL, QUINCENAL, MENSUAL
     * @return array
     */
    function generarPeriodoComision($periodicidad): array
    {
        $fechaFin = \Carbon\Carbon::today();

        switch (strtoupper($periodicidad)) {
            case 'SEMANAL':
                $fechaInicio = $fechaFin->copy()->subWeek();
                break;
            case 'QUINCENAL':
                $fechaInicio = $fechaFin->copy()->subDays(15);
                break;
            case 'MENSUAL':
                $fechaInicio = $fechaFin->copy()->subMonth();
                break;
            default:
                $fechaInicio = $fechaFin->copy()->subWeek();
        }

        return [
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'periodicidad' => strtoupper($periodicidad)
        ];
    }
}

if (!function_exists('logActivity')) {
    /**
     * Registrar actividad del usuario (opcional).
     *
     * @param  string  $action
     * @param  string  $description
     * @param  int|null  $userId
     * @return void
     */
    function logActivity($action, $description, $userId = null): void
    {
        // Aquí puedes implementar logging a base de datos o archivo
        \Log::info("Activity: $action", [
            'description' => $description,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now()
        ]);
    }
}

if (!function_exists('esHorarioLaboral')) {
    /**
     * Verificar si es horario laboral.
     *
     * @param  int  $horaInicio
     * @param  int  $horaFin
     * @return bool
     */
    function esHorarioLaboral($horaInicio = 8, $horaFin = 18): bool
    {
        $horaActual = \Carbon\Carbon::now()->hour;
        return $horaActual >= $horaInicio && $horaActual < $horaFin;
    }
}

if (!function_exists('calcularInteresMora')) {
    /**
     * Calcular interés de mora (opcional para futuras versiones).
     *
     * @param  float  $monto
     * @param  int  $diasVencidos
     * @param  float  $tasaDiaria
     * @return float
     */
    function calcularInteresMora($monto, $diasVencidos, $tasaDiaria = 0.1): float
    {
        if ($diasVencidos <= 0) {
            return 0;
        }

        return round($monto * ($tasaDiaria / 100) * $diasVencidos, 2);
    }
}