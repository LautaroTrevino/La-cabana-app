<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RemitoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ClientController; 
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\OrdenEntregaController; 
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web - La Cabaña App
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    
    // DASHBOARD
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');

    // 1. PRODUCTOS (Depósito / Stock Real)
    Route::resource('productos', ProductController::class)->names('products');
    Route::post('/products/quick-scan', [ProductController::class, 'quickScan'])->name('products.quickScan');
    Route::post('/products/{id}/movement', [ProductController::class, 'storeMovement'])->name('products.movement');
    Route::get('/historial', [ProductController::class, 'history'])->name('history.index');

    // 2. DEPÓSITO (Orden de Entrega / Salida de Mercadería)
    Route::get('/deposito/salida', [OrdenEntregaController::class, 'create'])->name('ordenes.create');
    Route::post('/deposito/guardar-real', [OrdenEntregaController::class, 'storeReal'])->name('ordenes.storeReal');

    // 3. REMITOS (Documentación Administrativa)
    Route::get('/remitos/crear', [RemitoController::class, 'create'])->name('remitos.create');
    Route::post('/remitos/store-menu', [RemitoController::class, 'storeMenu'])->name('remitos.storeMenu');
    Route::post('/remitos', [RemitoController::class, 'store'])->name('remitos.store');
    Route::get('/remitos', [RemitoController::class, 'index'])->name('remitos.index');
    Route::get('/remitos/{remito}', [RemitoController::class, 'show'])->name('remitos.show');
    Route::get('/remitos/{remito}/print', [RemitoController::class, 'print'])->name('remitos.print');
    Route::delete('/remitos/{remito}', [RemitoController::class, 'destroy'])->name('remitos.destroy');

    // 4. MENÚS Y RECETAS
    Route::resource('menus', MenuController::class);
    
    // Gestión completa de ingredientes (desde panel lateral)
    Route::resource('ingredients', IngredientController::class);
    
    // --- ESTA ES LA RUTA NUEVA PARA EL BOTÓN RÁPIDO EN "CREAR MENÚ" ---
    Route::post('/ingredients/store-api', [MenuController::class, 'storeIngredient'])->name('ingredients.store_api');

    // 5. BALANCE
    Route::get('/balance', [BalanceController::class, 'index'])->name('balance.index');
    Route::post('/balance/update-prices', [BalanceController::class, 'updatePrices'])->name('balance.updatePrices');

    // 6. CLIENTES (ESCUELAS)
    Route::resource('clients', ClientController::class);

    // 7. ADMIN USUARIOS
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/usuarios', function () {
            return view('admin.users.index', ['users' => User::all()]);
        })->name('admin.usuarios');
    });

    // 8. PERFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';