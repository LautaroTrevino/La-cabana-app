<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RemitoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\OrdenEntregaController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => redirect()->route('products.index'))
        ->middleware('verified')->name('dashboard');

    // Todos los roles
    Route::middleware(['role:admin,administrativo,usuario'])->group(function () {
        Route::resource('productos', ProductController::class)->names('products');
        Route::post('/products/quick-scan', [ProductController::class, 'quickScan'])->name('products.quickScan');
        Route::post('/products/{id}/movement', [ProductController::class, 'storeMovement'])->name('products.movement');
        Route::get('/historial', [ProductController::class, 'history'])->name('history.index');
    });

    // Admin + Administrativo
    Route::middleware(['role:admin,administrativo'])->group(function () {
        Route::get('/deposito/salida', [OrdenEntregaController::class, 'create'])->name('ordenes.create');
        Route::post('/deposito/guardar-real', [OrdenEntregaController::class, 'storeReal'])->name('ordenes.storeReal');

        Route::get('/remitos/crear', [RemitoController::class, 'create'])->name('remitos.create');
        Route::post('/remitos/store-menu', [RemitoController::class, 'storeMenu'])->name('remitos.storeMenu');
        Route::post('/remitos', [RemitoController::class, 'store'])->name('remitos.store');
        Route::get('/remitos', [RemitoController::class, 'index'])->name('remitos.index');
        Route::get('/remitos/{remito}', [RemitoController::class, 'show'])->name('remitos.show');
        Route::get('/remitos/{remito}/print', [RemitoController::class, 'print'])->name('remitos.print');
        Route::delete('/remitos/{remito}', [RemitoController::class, 'destroy'])->name('remitos.destroy');

        Route::resource('menus', MenuController::class);
        Route::resource('ingredients', IngredientController::class);
        Route::post('/ingredients/store-api', [MenuController::class, 'storeIngredient'])->name('ingredients.store_api');

        Route::get('/balance', [BalanceController::class, 'index'])->name('balance.index');
        Route::post('/balance/update-prices', [BalanceController::class, 'updatePrices'])->name('balance.updatePrices');

        Route::resource('clients', ClientController::class);
    });

    // Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/usuarios', [AdminUserController::class, 'index'])->name('admin.usuarios');
        Route::post('/admin/usuarios', [AdminUserController::class, 'store'])->name('admin.usuarios.store');
        Route::post('/admin/usuarios/{user}/rol', [AdminUserController::class, 'updateRole'])->name('admin.usuarios.rol');
        Route::delete('/admin/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('admin.usuarios.destroy');
    });

    // Verificación de contraseña admin (para borrados desde rol administrativo)
    Route::post('/admin/verify-password', [AdminUserController::class, 'verifyAdminPassword'])
        ->name('admin.verifyPassword');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
