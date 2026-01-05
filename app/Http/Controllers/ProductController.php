<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Client; 
use App\Models\Movement; // Asegúrate de tener el modelo Movement creado
use App\Models\Remito;
use App\Models\RemitoDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * 1. LISTADO DE PRODUCTOS
     * Muestra la tabla principal con paginación y buscador.
     */
    public function index(Request $request)
    {
        // Ordenamos por los más nuevos primero
        $query = Product::latest();
        $search = $request->input('search');

        // Filtro del buscador (Nombre, Marca o Código)
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        // Paginamos de a 10 para que la tabla no sea gigante
        $products = $query->paginate(10);
        
        // Mantenemos los clientes por si los usas en otros filtros
        $clients = Client::orderBy('name')->get(); 
        
        return view('products.index', compact('products', 'clients'));
    }

    // Vista simple para crear producto
    public function create() { return view('products.create'); }

    /**
     * 2. GUARDAR NUEVO PRODUCTO
     */
    public function store(Request $request)
    {
        // Validamos que los campos obligatorios estén presentes
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

    // Vista para editar
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    /**
     * 3. ACTUALIZAR PRODUCTO
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:products,code,' . $id, // Ignora el propio ID para no dar error de duplicado
            'name' => 'required',
            'stock' => 'required|numeric',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());
        return redirect()->route('products.index')->with('success', '¡Producto actualizado!');
    }

    /**
     * 4. MOVIMIENTOS MANUALES (MODAL)
     * Maneja Entradas (Compras/Ajustes) y Salidas (Roturas/Mal estado).
     * NO pide cliente para las salidas (se asume rotura).
     */
    public function storeMovement(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'type' => 'required|in:entry,exit',
            'quantity' => 'required|numeric|min:0.1',
            'unit_type' => 'required|in:unit,package',
        ]);

        // Cálculo de unidades totales (si eligió cajas, multiplicamos)
        $totalUnits = $request->quantity;
        if ($request->unit_type == 'package') {
            $unitsPerPack = $product->units_per_package > 0 ? $product->units_per_package : 1;
            $totalUnits = $request->quantity * $unitsPerPack;
        }

        // Verificamos si hay stock suficiente antes de restar
        if ($request->type == 'exit' && $product->stock < $totalUnits) {
            return back()->with('error', "Stock insuficiente. Tienes {$product->stock} y quieres sacar {$totalUnits}.");
        }

        // Definimos la observación y el cliente según el tipo de movimiento
        $observation = '';
        $clientId = null;

        if ($request->type == 'entry') {
            $observation = 'Entrada manual (Ajuste de Stock)';
        } else {
            // SI ES SALIDA: Se marca automáticamente como Rotura/Mal Estado
            $observation = 'Baja por mercadería rota o en mal estado';
        }

        // Registramos en el historial
        try {
            $product->movements()->create([
                'type' => $request->type,
                'quantity' => $totalUnits,
                'client_id' => $clientId, 
                'observation' => $observation
            ]);
        } catch (\Exception $e) {}

        // Actualizamos el Stock real del producto
        if ($request->type == 'entry') {
            $product->increment('stock', $totalUnits);
            $msg = "Entrada registrada: +{$totalUnits} unidades.";
        } else {
            $product->decrement('stock', $totalUnits);
            $msg = "Baja por rotura registrada: -{$totalUnits} unidades.";
        }

        return back()->with('success', $msg);
    }

    /**
     * 5. ESCANEO RÁPIDO (NUEVO)
     * Procesa la entrada/salida desde la barra superior escaneando códigos.
     * Detecta automáticamente si es Caja o Unidad según el código escaneado.
     */
    public function quickScan(Request $request)
    {
        $request->validate([
            'scan_code' => 'required',
            'scan_mode' => 'required|in:entry,exit',
            'scan_quantity' => 'required|numeric|min:0.1'
        ]);

        $code = $request->scan_code;
        $qty = $request->scan_quantity;
        $mode = $request->scan_mode;

        // 1. Buscar el producto (por código unitario O código de bulto)
        $product = Product::where('code', $code)
                          ->orWhere('package_code', $code)
                          ->first();

        if (!$product) {
            return back()->with('error', "❌ Código no encontrado: $code")->withInput();
        }

        // 2. Detectar si es CAJA o UNIDAD comparando con los códigos guardados
        $isPackage = ($code === $product->package_code && $code !== $product->code);
        
        $multiplier = 1;

        if ($isPackage) {
            $multiplier = $product->units_per_package > 0 ? $product->units_per_package : 1;
        }

        $totalChange = $qty * $multiplier;

        // 3. Procesar Movimiento según el modo
        if ($mode === 'exit') {
            // VALIDAR STOCK
            if ($product->stock < $totalChange) {
                return back()->with('error', "⚠️ Stock insuficiente para sacar $totalChange unidades de {$product->name}.");
            }

            $product->decrement('stock', $totalChange);
            
            // Historial automático de rotura
            try {
                $product->movements()->create([
                    'type' => 'exit',
                    'quantity' => $totalChange,
                    'client_id' => null, 
                    'observation' => 'Escáner Rápido: Baja por Rotura/Mal Estado'
                ]);
            } catch (\Exception $e) {}

            $msg = "🔴 SALIDA (Rotura): -{$totalChange} ({$product->name})";

        } else {
            // ENTRADA
            $product->increment('stock', $totalChange);
            
            // Historial automático
            try {
                $product->movements()->create([
                    'type' => 'entry',
                    'quantity' => $totalChange,
                    'observation' => 'Escáner Rápido: Entrada Stock'
                ]);
            } catch (\Exception $e) {}

            $msg = "🟢 ENTRADA: +{$totalChange} ({$product->name})";
        }

        return back()->with('success', $msg);
    }

    /**
     * 6. ELIMINAR PRODUCTO
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', '¡Producto eliminado!');
    }

    /**
     * 7. HISTORIAL DE MOVIMIENTOS
     */
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

    // --- FUNCIONES LEGACY (Mantenidas por compatibilidad) ---
    
    public function entregaEscuelaForm()
    {
        $clients = Client::orderBy('name')->get();
        $products = Product::where('stock', '>', 0)->get(); 
        return view('products.entrega_escuela', compact('clients', 'products'));
    }

    public function procesarEntregaEscuela(Request $request)
    {
        return redirect()->route('deposito.create'); 
    }
}