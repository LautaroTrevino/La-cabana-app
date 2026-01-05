<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RemitoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\IngredientController;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

// Página de bienvenida (Pública)
Route::get('/', function () {
    return view('welcome');
});

// GRUPO PROTEGIDO (Requiere Login)
Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD ---
    // Redirige al listado de productos como pantalla principal
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');


    // =================================================================
    // 1. GESTIÓN DE PRODUCTOS E INVENTARIO (ProductController)
    // =================================================================
    
    // CRUD completo de productos (Listar, Crear, Editar, Eliminar)
    Route::resource('productos', ProductController::class)->names('products');

    // [NUEVO] Escáner Rápido (Barra superior): Procesa entradas y roturas por código de barras
    Route::post('/products/quick-scan', [ProductController::class, 'quickScan'])
        ->name('products.quickScan');

    // [NUEVO] Movimiento Manual (Modal): Botones +/- en la lista de productos
    Route::post('/products/{id}/movement', [ProductController::class, 'storeMovement'])
        ->name('products.movement');

    // [NUEVO] Historial de Movimientos: Ver tabla de entradas, salidas y roturas
    Route::get('/historial', [ProductController::class, 'history'])
        ->name('history.index');


    // =================================================================
    // 2. SECCIÓN DEPÓSITO - ENTREGAS REALES (RemitoController)
    // =================================================================
    // Estas rutas manejan la salida física de mercadería y DESCUENTAN STOCK.

    // Formulario para nueva entrega (limpio, solo productos con stock)
    Route::get('/deposito/nuevo', [RemitoController::class, 'createEntrega'])
        ->name('deposito.create');

    // Procesar y guardar la entrega (Restar stock y generar remito)
    Route::post('/deposito/guardar', [RemitoController::class, 'storeEntrega'])
        ->name('deposito.store');


    // =================================================================
    // 3. SECCIÓN ADMINISTRATIVA - REMITOS DE PAPEL (RemitoController)
    // =================================================================
    // Estas rutas son para lo administrativo (Menús). NO DESCUENTAN STOCK (generalmente).

    // Listado general de remitos (mezcla entregas reales y administrativas)
    Route::get('/remitos', [RemitoController::class, 'index'])->name('remitos.index');
    
    // Ver detalle de un remito específico
    Route::get('/remitos/{remito}', [RemitoController::class, 'show'])->name('remitos.show');
    
    // Imprimir PDF
    Route::get('/remitos/{remito}/print', [RemitoController::class, 'print'])->name('remitos.print');

    // [NUEVO] Generar Remito desde Menú (Modal en el índice de remitos)
    // Toma los ingredientes de los menús seleccionados y crea un remito.
    Route::post('/remitos/store-menu', [RemitoController::class, 'storeMenu'])
        ->name('remitos.storeMenu');

    // Rutas legacy para crear remitos manuales administrativos (si aún se usan)
    Route::get('/remitos/crear', [RemitoController::class, 'create'])->name('remitos.create');
    Route::post('/remitos', [RemitoController::class, 'store'])->name('remitos.store');


    // =================================================================
    // 4. MENÚS E INGREDIENTES
    // =================================================================
    Route::resource('menus', MenuController::class);
    Route::resource('ingredients', IngredientController::class);
    // API interna para guardar ingredientes desde el creador de menús (si aplica)
    Route::post('/ingredients/store-api', [MenuController::class, 'storeIngredient'])
        ->name('ingredients.store_api');


    // =================================================================
    // 5. CONFIGURACIÓN (Solo Admin)
    // =================================================================
    Route::middleware(['role:admin'])->group(function () {
        
        // --- Gestión de Clientes / Escuelas ---
        Route::get('/clients', function () {
            return view('clients.index', ['clients' => Client::all()]); 
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

        // --- Gestión de Usuarios ---
        Route::get('/admin/usuarios', function () {
            return view('admin.users.index', ['users' => User::all()]);
        })->name('admin.usuarios');
    });


    // =================================================================
    // 6. PERFIL DE USUARIO (Breeze Defaults)
    // =================================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';