<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Models\Client;
use App\Models\Remito;
use App\Models\RemitoDetail; // <--- IMPRESCINDIBLE para guardar los detalles
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // <--- Necesario para procesar el formulario

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
    // B. ZONA DE REMITOS (Acceso: Admin y Usuario)
    // --------------------------------------------------------
    Route::middleware(['role:admin,usuario'])->group(function () {
        
        // 1. Mostrar lista (Index)
        Route::get('/remitos', function () {
            // Traemos los remitos con sus detalles para contar items en la tabla
            $remitos = Remito::with('details')->orderBy('created_at', 'desc')->get();
            $clients = Client::all(); 
            $products = Product::all(); // Necesario para el modal de creación
            return view('remitos.index', compact('remitos', 'clients', 'products'));
        })->name('remitos.index');

        // 2. Guardar nuevo remito (Store)
        Route::post('/remitos', function (Request $request) {
            
            // Validaciones
            $request->validate([
                'cliente' => 'required',
                'fecha' => 'required|date',
                'productos' => 'required|array',
                'cantidades' => 'required|array',
            ]);

            // Generamos número único (Ej: REM-20231213-5849)
            $numero = 'REM-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // 1. Crear Cabecera
            $remito = Remito::create([
                'numero_remito' => $numero,
                'fecha' => $request->fecha,
                'cliente' => $request->cliente,
                'estado' => 'pendiente'
            ]);

            // 2. Crear Detalles (Loop por los productos)
            foreach ($request->productos as $index => $productId) {
                // Solo guardamos si hay producto y cantidad válida
                if (!empty($productId) && !empty($request->cantidades[$index])) {
                    RemitoDetail::create([
                        'remito_id' => $remito->id,
                        'product_id' => $productId,
                        'quantity' => $request->cantidades[$index]
                    ]);
                }
            }

            return redirect()->route('remitos.index')->with('success', 'Remito generado correctamente.');
        })->name('remitos.store');

        // --- NUEVAS RUTAS DE VISUALIZACIÓN ---

        // 3. Ver Detalle (Botón Ojo)
        Route::get('/remitos/{remito}', function (Remito $remito) {
            // Cargamos la relación 'details' y dentro de ella 'product' para ver nombres
            $remito->load('details.product');
            return view('remitos.show', compact('remito'));
        })->name('remitos.show');

        // 4. Imprimir (Botón Impresora)
        Route::get('/remitos/{remito}/print', function (Remito $remito) {
            $remito->load('details.product');
            return view('remitos.print', compact('remito'));
        })->name('remitos.print');
        
    });


    // --------------------------------------------------------
    // C. ZONA DE ADMINISTRADOR (Solo Admin)
    // --------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        
        // 1. Gestión de Clientes
        Route::get('/clients', function () {
            $clients = Client::all(); 
            return view('clients.index', compact('clients')); 
        })->name('clients.index');
        
        // Rutas básicas para Clientes (evitan error de ruta faltante en la vista)
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