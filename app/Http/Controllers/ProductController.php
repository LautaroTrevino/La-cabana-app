<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Client; // <-- Importación agregada
use App\Models\Movement; // <-- Importación agregada (Asumiendo que tienes este modelo)
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 1. LISTADO + BUSCADOR INTELIGENTE
    public function index(Request $request)
    {
        $query = Product::latest();
        $search = $request->input('search');

        // Lógica de búsqueda (si la quieres implementar)
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        $products = $query->get();
        
        // Agregamos esta línea para traer todos los clientes
        $clients = Client::orderBy('name')->get(); 
        
        // Pasamos ambas variables a la vista
        return view('products.index', compact('products', 'clients'));
    }
    

    public function create()
    {
        return view('products.create');
    }

    // 2. GUARDAR NUEVO PRODUCTO
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:products',
            'package_code' => 'nullable|unique:products',
            'name' => 'required',
            'brand' => 'nullable|string',
            'presentation' => 'required|string', // <-- Agregada validación de presentación
            'units_per_package' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric',
            'price_per_package' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('success', '¡Producto creado exitosamente!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    // 3. ACTUALIZAR PRODUCTO
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:products,code,' . $id,
            'package_code' => 'nullable|unique:products,package_code,' . $id,
            'name' => 'required',
            'presentation' => 'required|string', // <-- Agregada validación de presentación
            'units_per_package' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric',
            'price_per_package' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return redirect()->route('products.index')->with('success', '¡Producto actualizado correctamente!');
    }

    // 4. CONTROL DE MOVIMIENTOS (ANTES LLAMADA 'MOVEMENT', AHORA 'handleMovement')
    public function handleMovement(Request $request, $id) // <-- Renombrada
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'type' => 'required|in:entry,exit',
            'quantity' => 'required|integer|min:1',
            'unit_type' => 'required|in:unit,package',
            // ESTA ES LA CLAVE: required_if si es salida, y debe existir en la tabla clients
            'client_id' => 'required_if:type,exit|nullable|exists:clients,id', 
        ]);

        // A. Cálculo de unidades reales
        $totalUnits = $request->quantity;
        if ($request->unit_type == 'package') {
            $totalUnits = $request->quantity * $product->units_per_package;
        }

        // B. Validación de stock disponible
        if ($request->type == 'exit' && $product->stock < $totalUnits) {
            return back()->with('error', "Stock insuficiente. Intentas sacar $totalUnits unidades y tienes {$product->stock}.");
        }

        // C. Guardar historial (Asegúrate que el modelo Product tenga la relación 'movements')
        $product->movements()->create([
            'type' => $request->type,
            'quantity' => $totalUnits,
            'client_id' => $request->client_id, // Guarda el ID del cliente
            'created_at' => now()
        ]);

        // D. Actualizar el stock
        if ($request->type == 'entry') {
            $product->stock += $totalUnits;
        } else {
            $product->stock -= $totalUnits;
        }
        
        $product->save();

        // E. Mensaje de éxito
        $unitLabel = $request->unit_type == 'package' ? 'Cajas' : 'Unidades';
        return back()->with('success', "Movimiento registrado: {$request->quantity} {$unitLabel} (Total: {$totalUnits} u).");
    }
    
    // 5. BORRAR PRODUCTO
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', '¡Producto eliminado correctamente!');
    }

    // 6. HISTORIAL DE MOVIMIENTOS
    public function history(Request $request)
    {
        // Traemos movimientos y cargamos la relación 'product' para saber el nombre
        $query = Movement::with('product', 'client')->latest(); // <-- Agregada carga de cliente

        // Filtro por Tipo (Entrada/Salida)
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Filtro por Cliente (Necesitarías un campo de búsqueda por cliente_id en la vista)
        // Este filtro usa el campo 'client_id' en la tabla movements, no el nombre
        if ($request->has('client_id') && $request->client_id != '') {
             $query->where('client_id', $request->client_id);
        }

        // Filtro por Fecha
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', $request->date);
        }

        $movements = $query->paginate(20); // Paginamos de a 20
        $clients = Client::all(); // Necesario para el filtro de clientes
        
        return view('history.index', compact('movements', 'clients')); // Pasamos clientes
    }
}