<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RemitoController; // <--- Importación correcta
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    
    // RUTA DASHBOARD: Redirige a productos
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');


    // --------------------------------------------------------
    // A. ZONA DE PRODUCTOS (Acceso General)
    // --------------------------------------------------------
    Route::resource('productos', ProductController::class)->names('products');
    Route::post('productos/{product}/movement', [ProductController::class, 'handleMovement'])->name('products.movement');


    // --------------------------------------------------------
    // B. ZONA DE REMITOS Y ENTREGAS (Acceso: Admin y Usuario)
    // --------------------------------------------------------
    Route::middleware(['role:admin,usuario'])->group(function () {
        
        // 1. Listado (Historial)
        Route::get('/remitos', [RemitoController::class, 'index'])->name('remitos.index');

        // 2. Formulario de Creación (Maneja ?tipo=entrega o ?tipo=remito)
        Route::get('/remitos/crear', [RemitoController::class, 'create'])->name('remitos.create');

        // 3. RUTAS DE GUARDADO SEPARADAS:

        // 3a. GUARDAR ENTREGA REAL (Alternativo/Depósito) - SÍ DESCUENTA STOCK
        // Esta ruta apunta a la función 'store' en el controlador
        Route::post('/remitos/store/entrega', [RemitoController::class, 'store'])->name('remitos.store');

        // 3b. GUARDAR REMITO OFICIAL (Menú/Administrativo) - NO DESCUENTA STOCK
        // Esta ruta apunta a la nueva función 'storeRemitoOficial' en el controlador
        Route::post('/remitos/store/oficial', [RemitoController::class, 'storeRemitoOficial'])->name('remitos.store_oficial');

        // 4. Ver Detalle (Para imprimir o ver historial)
        Route::get('/remitos/{remito}', [RemitoController::class, 'show'])->name('remitos.show');

        // 5. Imprimir
        Route::get('/remitos/{remito}/print', [RemitoController::class, 'show'])->name('remitos.print');
    });


    // --------------------------------------------------------
    // C. ZONA DE ADMINISTRADOR (Solo Admin)
    // --------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        
        // 1. Gestión de Clientes (Mantenemos la lógica inline)
        Route::get('/clients', function () {
            $clients = Client::all(); 
            return view('clients.index', compact('clients')); 
        })->name('clients.index');
        
        Route::post('/clients', function (Request $request) {
            Client::create($request->all());
            return redirect()->route('clients.index')->with('success', 'Escuela agregada.');
        })->name('clients.store');

        Route::put('/clients/{client}', function (Request $request, Client $client) {
            $client->update($request->all());
            return redirect()->route('clients.index')->with('success', 'Escuela actualizada.');
        })->name('clients.update');
        
        Route::delete('/clients/{client}', function (Client $client) {
            $client->delete();
            return redirect()->route('clients.index')->with('success', 'Escuela eliminada.');
        })->name('clients.destroy');


        // 2. Gestión de Usuarios
        Route::get('/admin/usuarios', function () {
            $users = User::all();
            return view('admin.users.index', compact('users'));
        })->name('admin.usuarios');
        
    });


    // D. Rutas de Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de autenticación
require __DIR__.'/auth.php';