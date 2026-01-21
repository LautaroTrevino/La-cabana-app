<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RemitoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ClientController; 
use App\Http\Controllers\BalanceController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');

    // 1. PRODUCTOS
    Route::resource('productos', ProductController::class)->names('products');
    Route::post('/products/quick-scan', [ProductController::class, 'quickScan'])->name('products.quickScan');
    Route::post('/products/{id}/movement', [ProductController::class, 'storeMovement'])->name('products.movement');
    Route::get('/historial', [ProductController::class, 'history'])->name('history.index');

    // 2. DEPÓSITO
    Route::get('/deposito/nuevo', [RemitoController::class, 'createEntrega'])->name('deposito.create');
    Route::post('/deposito/guardar', [RemitoController::class, 'storeEntrega'])->name('deposito.store');

    // 3. REMITOS
    Route::get('/remitos', [RemitoController::class, 'index'])->name('remitos.index');
    Route::get('/remitos/{remito}', [RemitoController::class, 'show'])->name('remitos.show');
    Route::get('/remitos/{remito}/print', [RemitoController::class, 'print'])->name('remitos.print');
    Route::post('/remitos/store-menu', [RemitoController::class, 'storeMenu'])->name('remitos.storeMenu');
    Route::get('/remitos/crear', [RemitoController::class, 'create'])->name('remitos.create');
    Route::post('/remitos', [RemitoController::class, 'store'])->name('remitos.store');

    // 4. MENÚS
    Route::resource('menus', MenuController::class);
    Route::resource('ingredients', IngredientController::class);
    Route::post('/ingredients/store-api', [MenuController::class, 'storeIngredient'])->name('ingredients.store_api');

    // 5. BALANCE
    Route::get('/balance', [BalanceController::class, 'index'])->name('balance.index');

    // 6. CLIENTES (ESCUELAS)
    // --- ESTA RUTA DEBE IR PRIMERO ---
    Route::put('/clients/update-global-prices', [ClientController::class, 'updateGlobalPrices'])
        ->name('clients.updateGlobalPrices');
    
    // --- LUEGO EL RESOURCE ---
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