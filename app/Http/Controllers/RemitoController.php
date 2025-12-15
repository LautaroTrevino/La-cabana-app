<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoDetail;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemitoController extends Controller
{
    // Listado de Remitos (Historial)
    public function index()
    {
        $remitos = Remito::with('client')->latest()->get();
        // Variables para los modales/filtros en remitos.index
        $clients = Client::orderBy('name')->get();
        $products = Product::all();
        
        return view('remitos.index', compact('remitos', 'clients', 'products'));
    }

    // Formulario para crear nuevo remito o entrega (Unificado)
    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $products = Product::where('stock', '>', 0)->orderBy('name')->get(); 
        
        // Determina si es 'remito' (oficial/menu) o 'entrega' (real/alternativo)
        $tipo = $request->get('tipo', 'remito');

        return view('remitos.create', compact('clients', 'products', 'tipo'));
    }

    // =======================================================
    // 🛑 FUNCIÓN 1: REMITO OFICIAL / MENÚ (NO DESCUENTA STOCK) 🛑
    // Esta función maneja la parte administrativa (menú).
    // =======================================================
    public function storeRemitoOficial(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1',
            // No necesitamos 'tipo_operacion' aquí, ya sabemos que es 'remito'
        ]);

        try {
            DB::transaction(function () use ($request) {
                
                // 1. Crear Encabezado (Identificación: REM)
                $remito = Remito::create([
                    'client_id' => $request->client_id,
                    'date' => $request->date,
                    'observation' => $request->observation,
                    'number' => 'REM-' . time(),
                    'tipo' => 'remito', // Tipo fijo: 'remito'
                ]);

                // 2. Recorrer productos y guardar detalles
                foreach ($request->products as $index => $productId) {
                    $qty = $request->quantities[$index];
                    
                    if ($qty > 0) {
                        // Guardar detalle
                        RemitoDetail::create([
                            'remito_id' => $remito->id,
                            'product_id' => $productId,
                            'quantity' => $qty
                        ]);
                        
                        // 🛑 NO SE DESCUENTA STOCK AQUÍ 🛑
                    }
                }
            });

            return redirect()->route('remitos.index')->with('success', 'Remito Oficial (Menú) generado. Stock NO afectado.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar Remito Oficial: ' . $e->getMessage())->withInput();
        }
    }


    // =======================================================
    // ✅ FUNCIÓN 2: ENTREGA POR ESCUELA (SÍ DESCUENTA STOCK) ✅
    // Esta es la función que ya modificamos antes para el depósito/realidad.
    // La renombramos a storeEntregaReal para que el flujo sea más claro.
    // =======================================================
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1',
            'tipo_operacion' => 'required|string|in:remito,entrega' 
        ]);

        try {
            DB::transaction(function () use ($request) {
                
                $tipo = $request->input('tipo_operacion');

                // Si por alguna razón esta función se llama con 'remito' la bloqueamos
                if ($tipo === 'remito') {
                    // DEBERÍAS ESTAR USANDO storeRemitoOficial, no esta función.
                    throw new \Exception("Función incorrecta. El remito oficial debe usar storeRemitoOficial.");
                }

                // 1. Crear Encabezado (Identificación: ENT)
                $remito = Remito::create([
                    'client_id' => $request->client_id,
                    'date' => $request->date,
                    'observation' => $request->observation,
                    'number' => 'ENT-' . time(), // Prefijo ENT
                    'tipo' => 'entrega', // Tipo fijo: 'entrega'
                ]);

                // 2. Recorrer productos y guardar detalles
                foreach ($request->products as $index => $productId) {
                    $qty = $request->quantities[$index];
                    
                    if ($qty > 0) {
                        // Guardar detalle
                        RemitoDetail::create([
                            'remito_id' => $remito->id,
                            'product_id' => $productId,
                            'quantity' => $qty
                        ]);

                        // 🛑 LÓGICA DE STOCK: SOLO DESCUENTA EN ENTREGAS REALES 🛑
                        $product = Product::find($productId);
                        if ($product) {
                            $product->stock -= $qty;
                            $product->save();
                        }
                    }
                }
            });

            return redirect()->route('remitos.index')->with('success', 'Entrega registrada correctamente (Stock actualizado).');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar Entrega Real: ' . $e->getMessage())->withInput();
        }
    }


    // Ver detalle de un remito/entrega
    public function show(Remito $remito)
    {
        $remito->load('details.product', 'client');
        return view('remitos.show', compact('remito'));
    }
}