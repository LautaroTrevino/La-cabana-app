<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Models\Client; // <--- 1. IMPORTANTE: Importamos el Modelo de Clientes
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Página de Bienvenida (Pública)
Route::get('/', function () {
    return view('welcome');
});

// 2. GRUPO PRINCIPAL: Rutas que requieren que el usuario esté LOGUEADO
Route::middleware('auth')->group(function () {
    
    // RUTA DASHBOARD: Redirige a la lista de productos
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');


    // --------------------------------------------------------
    // A. ZONA DE PRODUCTOS (Acceso General)
    // --------------------------------------------------------
    
    // 1. CRUD de Productos
    Route::resource('productos', ProductController::class)->names('products');
    
    // 2. Ruta para el Modal de Movimientos de Stock
    Route::post('productos/{product}/movement', [ProductController::class, 'handleMovement'])->name('products.movement');


    // --------------------------------------------------------
    // B. ZONA DE REMITOS (Admin y Usuario)
    // --------------------------------------------------------
    Route::middleware(['role:admin,usuario'])->group(function () {
        
        Route::get('/remitos', function () {
            return "<h1>Zona de Remitos</h1><p>Aquí irá el listado de remitos.</p>";
        })->name('remitos.index');
        
    });


    // --------------------------------------------------------
    // C. ZONA DE ADMINISTRADOR (Solo Admin)
    // --------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        
        // 1. GESTIÓN DE CLIENTES (ESCUELAS)
        // ----------------------------------------------------
        
        // RUTA INDEX: Muestra la lista y soluciona el error "Undefined variable $clients"
        Route::get('/clients', function () {
            // Obtenemos todos los clientes de la BD
            $clients = Client::all(); 
            // Retornamos la vista pasando la variable compactada
            return view('clients.index', compact('clients')); 
        })->name('clients.index');

        // RUTAS ADICIONALES (Requeridas por tu vista para que no dé error de ruta no encontrada)
        // Por ahora solo muestran texto, luego deberás crear un ClientController para la lógica real.
        
        Route::post('/clients', function () {
            return "Aquí se guardará el nuevo cliente (Falta lógica en Controlador)";
        })->name('clients.store');

        Route::put('/clients/{client}', function () {
            return "Aquí se actualizará el cliente (Falta lógica en Controlador)";
        })->name('clients.update');

        Route::delete('/clients/{client}', function () {
            return "Aquí se borrará el cliente (Falta lógica en Controlador)";
        })->name('clients.destroy');


        // 2. GESTIÓN DE USUARIOS
        // ----------------------------------------------------
        Route::get('/admin/usuarios', function () {
            return "<h1>Gestión de Usuarios</h1>"; 
        })->name('admin.usuarios');
        
    });


    // D. Rutas de Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de autenticación
require __DIR__.'/auth.php';