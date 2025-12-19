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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    
    // DASHBOARD: Redirige según el uso principal
    Route::get('/dashboard', function () {
        return redirect()->route('products.index');
    })->middleware('verified')->name('dashboard');

    // --------------------------------------------------------
    // A. SECCIÓN DEPÓSITO: PRODUCTOS Y ENTREGAS (Descuentan Stock)
    // --------------------------------------------------------
    // Lista de productos visible para todos los autenticados
    Route::resource('productos', ProductController::class)->names('products');
    
    // Rutas de Entrega: Estas se gestionan en RemitoController pero son para el Depósito
    Route::get('/entregas/escuela', [RemitoController::class, 'createEntrega'])
        ->name('entregas.escuela.form');
    Route::post('/entregas/escuela', [RemitoController::class, 'storeEntrega'])
        ->name('entregas.escuela.store');

    // --------------------------------------------------------
    // B. SECCIÓN ADMINISTRATIVA: REMITOS (No descuentan stock)
    // --------------------------------------------------------
    // Solo personal administrativo o admin tiene acceso aquí
    Route::middleware(['role:admin,usuario'])->group(function () {
        
        Route::get('/remitos', [RemitoController::class, 'index'])->name('remitos.index');
        Route::get('/remitos/crear', [RemitoController::class, 'create'])->name('remitos.create');
        
        // Guardar Remito Administrativo (Llamamos a store normal)
        Route::post('/remitos', [RemitoController::class, 'store'])->name('remitos.store');
        
        // Ver y PDF
        Route::get('/remitos/{remito}', [RemitoController::class, 'show'])->name('remitos.show');
        Route::get('/remitos/{remito}/print', [RemitoController::class, 'print'])->name('remitos.print');

        // Otras operaciones (edit, delete) si las necesitas
        Route::resource('remitos', RemitoController::class)->except(['index', 'create', 'show', 'store']);
    });

    // --------------------------------------------------------
    // C. INGREDIENTES Y MENÚS
    // --------------------------------------------------------
    Route::resource('menus', MenuController::class);
    Route::resource('ingredients', IngredientController::class);
    Route::post('/ingredients/store-api', [MenuController::class, 'storeIngredient'])->name('ingredients.store_api');

    // --------------------------------------------------------
    // D. GESTIÓN DE ESCUELAS Y USUARIOS (Solo Admin)
    // --------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        // Escuelas/Clientes
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

        // Usuarios
        Route::get('/admin/usuarios', function () {
            return view('admin.users.index', ['users' => User::all()]);
        })->name('admin.usuarios');
    });

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::post('/remitos/store-menu', [App\Http\Controllers\RemitoController::class, 'storeMenu'])->name('remitos.storeMenu');