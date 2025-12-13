<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
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
    
    // 1. CRUD de Productos (Index, Create, Edit, Update, Destroy)
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
        
        // Gestión de Clientes (Coincide con tu navbar)
        Route::get('/clients', function () {
            // Futuro: [ClientController::class, 'index']
            return "<h1>Gestión de Clientes</h1><p>Zona exclusiva de Administrador.</p>";
        })->name('clients.index');

        // Gestión de Usuarios
        Route::get('/admin/usuarios', function () {
            return "<h1>Gestión de Usuarios</h1><p>Zona exclusiva de Administrador.</p>";
        })->name('admin.usuarios');
        
    });


    // D. Rutas de Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de autenticación
require __DIR__.'/auth.php';