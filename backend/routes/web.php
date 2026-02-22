<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Este archivo NO se usa en este proyecto.
| Toda la aplicación funciona como SPA (Single Page Application) con React.
| 
| Las rutas web son manejadas por React Router en el frontend.
| Las rutas de API están en routes/api.php
|
*/

// Redireccionar todo a la app React
Route::get('/{any}', function () {
    // En producción, esto servirá el index.html de React desde public/build/
    return file_get_contents(public_path('build/index.html'));
})->where('any', '.*');