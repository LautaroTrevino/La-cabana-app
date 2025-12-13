<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController; // Importar tu controlador de Productos
use App\Http\Controllers\RemitoController;  // Importar tu controlador de Remitos (si lo usas)
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
    
    // RUTA DASHBOARD: Punto de entrada después del Login. Redirige a la lista de productos.
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');


    // --------------------------------------------------------
    // A. ZONA DE PRODUCTOS (Rutas de Recurso y Movimiento)
    // --------------------------------------------------------
    
    // 1. RUTA DE RECURSO: Define products.index, products.create, products.edit, products.destroy, etc.
    Route::resource('productos', ProductController::class)->names('products');
    
    // 2. RUTA DE MOVIMIENTO: Procesa las ENTRADAS/SALIDAS del modal
    // Esta ruta es la que el JavaScript de tu modal está esperando.
    // Usamos 'products.movement' como nombre para la ruta.
    Route::post('productos/{product}/movement', [ProductController::class, 'handleMovement'])->name('products.movement');


    // --------------------------------------------------------
    // B. ZONA DE REMITOS (Acceso restringido: Admin y Usuario General)
    // --------------------------------------------------------
    Route::middleware(['role:admin,usuario'])->group(function () {
        
        // Ruta de Remitos
        Route::get('/remitos', function () {
            return "<h1>Zona de Remitos</h1><p>Si lees esto, es porque NO eres empleado.</p>";
        })->name('remitos.index');
        
    });


    // --------------------------------------------------------
    // C. ZONA DE ADMINISTRADOR (Acceso restringido: Solo Admin)
Route::middleware(['role:admin'])->group(function () {
    
    // NUEVA RUTA PARA CLIENTES
    Route::get('/clients', function () {
        return "<h1>Gestión de Clientes</h1><p>Solo para el Administrador.</p>";
    })->name('clients.index'); // <-- Este nombre debe coincidir con la navbar

    Route::get('/admin/usuarios', function () {
        return "<h1>Gestión de Usuarios</h1><p>Solo para el Administrador.</p>";
    })->name('admin.usuarios');
    
});
    // D. Rutas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Incluye las rutas de autenticación de Breeze (Login, Register, Logout)
require __DIR__.'/auth.php';