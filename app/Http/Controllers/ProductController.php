<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Client; 
use App\Models\Movement; 
use App\Models\Remito;
use App\Models\RemitoDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // 1. LISTADO + BUSCADOR
    public function index(Request $request)
    {
        $query = Product::latest();
        $search = $request->input('search');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        $products = $query->get();
        $clients = Client::orderBy('name')->get(); 
        
        return view('products.index', compact('products', 'clients'));
    }

    public function create() { return view('products.create'); }

    // 2. GUARDAR PRODUCTO
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:products',
            'package_code' => 'nullable|unique:products',
            'name' => 'required',
            'presentation' => 'required',
            'units_per_package' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric',
            'price_per_package' => 'required|numeric',
            'stock' => 'required|numeric',
        ]);

        Product::create($request->all());
        return redirect()->route('products.index')->with('success', '¡Producto creado!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    // 3. ACTUALIZAR
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:products,code,' . $id,
            'name' => 'required',
            'stock' => 'required|numeric',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());
        return redirect()->route('products.index')->with('success', '¡Producto actualizado!');
    }

    // 4. MOVIMIENTOS RÁPIDOS (Entrada/Salida Manual)
    public function handleMovement(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'type' => 'required|in:entry,exit',
            'quantity' => 'required|numeric|min:0.1',
            'unit_type' => 'required|in:unit,package',
            'client_id' => 'required_if:type,exit|nullable|exists:clients,id', 
        ]);

        $totalUnits = $request->quantity;
        if ($request->unit_type == 'package') {
            $totalUnits = $request->quantity * $product->units_per_package;
        }

        if ($request->type == 'exit' && $product->stock < $totalUnits) {
            return back()->with('error', "Stock insuficiente. Tienes {$product->stock}.");
        }

        $product->movements()->create([
            'type' => $request->type,
            'quantity' => $totalUnits,
            'client_id' => ($request->type == 'exit') ? $request->client_id : null,
            'observation' => 'Ajuste manual desde inventario'
        ]);

        if ($request->type == 'entry') {
            $product->increment('stock', $totalUnits);
        } else {
            $product->decrement('stock', $totalUnits);
        }

        return back()->with('success', "Movimiento registrado correctamente.");
    }

    // 5. BORRAR
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', '¡Producto eliminado!');
    }

    // 6. HISTORIAL
    public function history(Request $request)
    {
        $query = Movement::with('product', 'client')->latest(); 

        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('date')) $query->whereDate('created_at', $request->date);

        $movements = $query->paginate(20); 
        $clients = Client::all();
        
        return view('history.index', compact('movements', 'clients')); 
    }

    // 7. FORMULARIO ENTREGA (ESCÁNER)
    public function entregaEscuelaForm()
    {
        $clients = Client::orderBy('name')->get();
        $products = Product::where('stock', '>', 0)->get(); 
        return view('products.entrega_escuela', compact('clients', 'products'));
    }

    // 8. PROCESAR ENTREGA (Lógica de Negocio Principal)
    public function procesarEntregaEscuela(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'items' => 'required|array|min:1', // Asegura que haya al menos un producto
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        try {
            DB::beginTransaction();

            $remito = Remito::create([
                'client_id' => $request->client_id,
                'date' => now(),
                'number' => 'ENT-' . strtoupper(uniqid()), // Genera un número único
                'tipo' => 'entrega', 
                'status' => 'active'
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: {$product->name}. Disponible: {$product->stock}");
                }

                // A. Descontar Stock
                $product->decrement('stock', $item['quantity']);

                // B. Registrar detalle del Remito
                RemitoDetail::create([
                    'remito_id' => $remito->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity']
                ]);

                // C. Historial de Movimientos
                $product->movements()->create([
                    'type' => 'exit',
                    'quantity' => $item['quantity'],
                    'client_id' => $request->client_id,
                    'observation' => 'Entrega por Escuela - Remito ' . $remito->number
                ]);
            }

            DB::commit();
            return redirect()->route('remitos.index')->with('success', '¡Entrega realizada con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}