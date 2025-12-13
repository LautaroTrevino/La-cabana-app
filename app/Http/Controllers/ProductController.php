<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 1. LISTADO + BUSCADOR INTELIGENTE
    
       public function index()
    {
        $products = \App\Models\Product::latest()->get();
        // Agregamos esta línea para traer todos los clientes
        $clients = \App\Models\Client::orderBy('name')->get(); 
        
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
            'units_per_package' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric',
            'price_per_package' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return redirect()->route('products.index')->with('success', '¡Producto actualizado correctamente!');
    }

    // 4. CONTROL DE MOVIMIENTOS (Aquí estaba el error)
   // CONTROL DE MOVIMIENTOS (Stock por unidad o caja)
  // CONTROL DE MOVIMIENTOS
    public function movement(Request $request, $id)
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

        // C. Guardar historial
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

    public function history(Request $request)
{
    // Traemos movimientos y cargamos la relación 'product' para saber el nombre
    $query = \App\Models\Movement::with('product')->latest();

    // Filtro por Tipo (Entrada/Salida)
    if ($request->has('type') && $request->type != '') {
        $query->where('type', $request->type);
    }

    // Filtro por Cliente
    if ($request->has('client') && $request->client != '') {
        $query->where('client', 'LIKE', "%{$request->client}%");
    }

    // Filtro por Fecha
    if ($request->has('date') && $request->date != '') {
        $query->whereDate('created_at', $request->date);
    }

    $movements = $query->paginate(20); // Paginamos de a 20

    return view('history.index', compact('movements'));
}
}