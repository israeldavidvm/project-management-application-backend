<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controladores V1 (Asumimos que existen)
use App\Http\Controllers\Api\V1\AuthController as AuthControllerV1;

// Controladores V2 (Nuestros nuevos controladores)
use App\Http\Controllers\Api\V1\AuthController as AuthControllerV2;
use App\Http\Controllers\Api\V1\ProjectController; // Añadido
use App\Http\Controllers\Api\V1\TaskController;    // Añadido
use App\Http\Controllers\Api\V1\UserController;    // Añadido para la lista de usuarios


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('/v1')->group(function () {

    #Begin of sandbox api endpoints
    
    
    #Begin of production api endpoints


    Route::middleware('auth:sanctum')->group(function () {
        // RUTAS GENERALES DE USUARIO (Acceso por Admin o Dev, usado en selectores)
        // El controlador internamente restringe lo que se devuelve según el rol del Auth::user()
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/developers', [UserController::class, 'getDevelopers']);
        Route::get('users/admins', [UserController::class, 'getAdmins']);

        Route::apiResource('users', UserController::class)->except(['index']); 

    });

    Route::prefix('/auth')->group(function () {

            Route::post('login', [AuthControllerV2::class, 'login']);
    
            Route::post('register',[
                AuthControllerV2::class,
                'register'
            ]);

            Route::middleware(['auth:sanctum'])->group(function () {
    
                Route::post('logout', [AuthControllerV2::class, 'logout']);
                Route::post('me', [AuthControllerV2::class, 'me']);
                    
            });
    
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        // --- Rutas de Proyectos (ProjectController) ---
        Route::apiResource('projects', ProjectController::class);

        Route::get('tasks/summary', [TaskController::class, 'taskSummary'])->name('tasks.summary');

        // --- Rutas de Tareas (TaskController) ---
        Route::apiResource('tasks', TaskController::class);
    
    });
    

});