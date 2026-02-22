<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Verifica que el usuario autenticado tenga uno de los roles especificados.
     * 
     * Uso en rutas:
     * - Route::middleware('role:JEFE')->get('/admin', ...);
     * - Route::middleware('role:COBRADOR,JEFE')->post('/pagos', ...);
     * - Route::middleware('role:VENDEDOR,JEFE')->post('/ventas', ...);
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Uno o más roles permitidos (JEFE, COBRADOR, VENDEDOR)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado. Por favor inicia sesión.',
                'required_action' => 'login'
            ], 401);
        }

        $user = $request->user();

        // Verificar que el usuario tenga un rol asignado
        if (!$user->rol) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario sin rol asignado. Contacta al administrador.'
            ], 403);
        }

        // Verificar que el usuario esté activo
        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador.'
            ], 403);
        }

        $userRole = $user->rol->nombre_rol;

        // Verificar si el rol del usuario está en la lista de roles permitidos
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder a este recurso',
                'required_roles' => $roles,
                'your_role' => $userRole,
                'hint' => $this->getRoleHint($roles, $userRole)
            ], 403);
        }

        return $next($request);
    }

    /**
     * Obtener mensaje de ayuda según el rol requerido.
     *
     * @param  array  $requiredRoles
     * @param  string  $userRole
     * @return string
     */
    private function getRoleHint(array $requiredRoles, string $userRole): string
    {
        $hints = [
            'JEFE' => 'Esta acción solo puede ser realizada por un administrador (JEFE).',
            'COBRADOR' => 'Esta acción solo puede ser realizada por cobradores.',
            'VENDEDOR' => 'Esta acción solo puede ser realizada por vendedores.'
        ];

        // Si solo se requiere un rol, dar hint específico
        if (count($requiredRoles) === 1) {
            return $hints[$requiredRoles[0]] ?? 'Rol no autorizado.';
        }

        // Si se requieren múltiples roles
        $rolesText = implode(', ', $requiredRoles);
        return "Esta acción requiere uno de los siguientes roles: {$rolesText}. Tu rol actual es: {$userRole}.";
    }
}