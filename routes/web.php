<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Este proyecto es una API pura. Las rutas funcionales viven en routes/api.php.
| Esta ruta raíz solo confirma que el servidor está activo.
|
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'API de E-commerce funcionando. Consulta la documentación en /api/documentation',
    ]);
});
