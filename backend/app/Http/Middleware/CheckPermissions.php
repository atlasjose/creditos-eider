<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
 class CheckPermissions{
    /**
     * Handel an incoming request
     * verifica si el usuar tiene permisos especificos en su rol 
     * 
     * uso de rutas: 
     * Route:middeware('permission:deudores.crear')->post(/deudores',..);
     * Route:middeware('permission:pagos.registrar')->post('/pagos',..);
     * * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Permiso requerido (ej: 'deudores.crear')
     */
     

    public function handle(Request $request,Closure $next,string $permission):Response{
        $user=$request->user();
        //verifica que el usuario este autenticado 
        if(!$user){
            return response()->json([
                'success'=>false,
                'message'=>'No autenticado'
            ],401);
        }
        //verifica que el usuario tenga un rol 
        if(!$user->rol){
            return response()->json([
                'success'=>false,
                'message'=>'Usuario sin rol asignado'
            ],403);
        }
        //obtener permisos del rol (json)
        $permisos=$user->rol->permisos ??[];

        //verifica qel permiso en especifico
        if(!$this->checkPermission($permisos,$permission)){
            return response()->json([
                'success'=>false,
                'message'=>'no tienes permiso para realizar esta accion',
                'permiso_requerido'=>$permission
            ],403);

        }
        return $next($request);

    }

    /**
     * verificar si el usuario tiene un permiso especifico
     * @param  array  $permisos  Array de permisos del rol
     * @param  string  $permission  Permiso a verificar (ej: 'deudores.crear')
     * @return bool
     */
    private function checkPermission(array $permisos,string $permission):bool{
        //si el permiso contine un punto, verificar anidado
        if(str_contains($permission,'.')){
            $parts =explode('.',$permission,2);
            $module =$parts[0];
            $action =$parts[1];
        }
        // Verificar acceso al módulo
            if (!isset($permisos[$module])) {
                return false;
            }

            // Si el valor es booleano, retornar directamente
            if (is_bool($permisos[$module])) {
                return $permisos[$module];
            }

            // Si es un array, verificar la acción específica
            if (is_array($permisos[$module])) {
                return $permisos[$module][$action] ?? false;
            }

            return false;
        }

        // Permiso simple (sin punto)
        return $permisos[$permission] ?? false;
    }
    


