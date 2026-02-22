<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeudorController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ComisionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema de Créditos Eider
|--------------------------------------------------------------------------
|
| Todas las rutas de la API REST.
| Base URL: /api/
| 
| Autenticación: Laravel Sanctum (tokens)
| Roles: JEFE, COBRADOR, VENDEDOR
|
*/

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================

Route::post('/login',[AuthController::class,'login']);

//Rutas Protegidas (si requieren autenticacion)
Route::middleware('auth:sanctum')->group(function(){
    //AUTH-Autenticaioin
    //todos los usuarios autenticados 
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/me',[AuthController::class,'me']);
    Route::post('/change-password',[AuthController::class,'change-password']);

//rutas solo par jefes 
Route::middleware('role:JEFE')->group(function(){
//usuarios
Route::post('/register',[AuthController::class,'register']);
//deudores
Route::post('/Deudores',[DeudorController::class,'store']);
Route::delete('deudores/{id}',[DeudorController::class,'destroy']);
//comiciones 
Route::post('/comisiones/calcular',[ComisionController::class,'calcular']);
Route::post('/comisiones/{id}/pagar',[ComisionController::class,'marcarPagada']);
Route::post('/comisiones/calcular-automatico',[ComisionController::class,'calcularAutomatico']);

//PAGOS
Route::delete('/pagos/{id}',[PagoController::class,'destroy']);

//PRODUCTOS
Route::post('/productos',[ProductoController::class,'store']);
Route::put('/productos/{id}',[ProductoController::class,'update']);
Route::delete('/productos/{id}',[ProductoController::class,'destroy']);
Route::post('/productos/{id}/activar',[ProductoController::class,'activar']);
//Rutas 
Route::post('/rutas',[RutaController::class,'store']);
Route::put('/rutas/{id}',[RutaController::class,'update']);
Route::post('/rutas/{id}/asignar-cobrador',[RutaController::class,'asignarCobrador']);
Route::post('/rutas/{id}/desactivar',[RutaController::class,'desactivar']);
Route::post('/rutas/{id}/activar',[RutaController::class,'activar']);

//ventas (cancelar)
Route::post('/ventas/{id}/cancelar',[VentaController::class,'cancelar']);

});

//rutas para cobrador o jefe 
Route::middleware('role:COBRADOR,JEFE')->group(function(){
    //pagos
    Route::get('/pagos',[PagoController::class,'index']);
    Route::post('/pagos',[PagoController::class,'store']);
    Route::get('/pagos/{id}',[PagoController::class,'show']);
    Route::get('/pagos/cobrador/{id}', [PagoController::class, 'pagosDelCobrador']);
    Route::get('/pagos/resumen-dia', [PagoController::class, 'resumenDia']);

    // COMISIONES (ver propias)
        Route::get('/comisiones/cobrador/{id}', [ComisionController::class, 'comisionesDelCobrador']);
});
// ============================================
    // RUTAS PARA VENDEDOR O JEFE
    // ============================================
    
    Route::middleware('role:VENDEDOR,JEFE')->group(function () {
        
        // VENTAS
        Route::get('/ventas', [VentaController::class, 'index']);
        Route::post('/ventas', [VentaController::class, 'store']);
        Route::get('/ventas/{id}', [VentaController::class, 'show']);
        Route::get('/ventas/vendedor/{id}', [VentaController::class, 'ventasDelVendedor']);
    });
    
    
    // ============================================
    // RUTAS PARA TODOS LOS USUARIOS AUTENTICADOS
    // ============================================
    
    // DEUDORES (ver y actualizar ubicación)
    Route::get('/deudores', [DeudorController::class, 'index']);
    Route::get('/deudores/{id}', [DeudorController::class, 'show']);
    Route::put('/deudores/{id}', [DeudorController::class, 'update']);
    Route::put('/deudores/{id}/ubicacion', [DeudorController::class, 'actualizarUbicacion']);
    Route::post('/deudores/cercanos', [DeudorController::class, 'cercanos']);
    
    // PRODUCTOS (ver)
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::get('/productos/mas-vendidos', [ProductoController::class, 'masVendidos']);
    
    // RUTAS (ver)
    Route::get('/rutas', [RutaController::class, 'index']);
    Route::get('/rutas/{id}', [RutaController::class, 'show']);
    Route::get('/rutas/{id}/deudores', [RutaController::class, 'deudoresDeRuta']);
    Route::get('/rutas/cobrador/{id}', [RutaController::class, 'rutasDelCobrador']);
    
    // COMISIONES (ver)
    Route::get('/comisiones', [ComisionController::class, 'index']);
    Route::get('/comisiones/{id}', [ComisionController::class, 'show']);
});


// ============================================
// RUTA DE PRUEBA
// ============================================

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString(),
    ]);
});