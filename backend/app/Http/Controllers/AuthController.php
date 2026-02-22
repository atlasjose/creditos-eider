<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario (solo para jefes)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula' => 'required|string|max:20|unique:usuarios',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:100|unique:usuarios',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:roles,id_rol',
            'porcentaje_comision' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::create([
            'cedula' => $request->cedula,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'clave_hash' => Hash::make($request->password),
            'id_rol' => $request->id_rol,
            'id_jefe_asociado' => $request->id_jefe_asociado,
            'porcentaje_comision' => $request->porcentaje_comision ?? 10.00,
        ]);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => $usuario->load('rol'),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->clave_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        if (!$usuario->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario inactivo. Contacte al administrador'
            ], 403);
        }

        // Eliminar tokens anteriores (opcional)
        $usuario->tokens()->delete();

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $usuario->load('rol'),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout de usuario
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('rol')
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = $request->user();

        if (!Hash::check($request->current_password, $usuario->clave_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 401);
        }

        $usuario->update([
            'clave_hash' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }
}