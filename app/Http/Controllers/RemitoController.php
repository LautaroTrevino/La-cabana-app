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
    // Listado de Remitos
    public function index()
    {
        $remitos = Remito::with('client')->latest()->get();
        return view('remitos.index', compact('remitos'));
    }

    // Formulario para crear nuevo remito
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $products = Product::where('stock', '>', 0)->orderBy('name')->get(); // Solo productos con stock
        return view('remitos.create', compact('clients', 'products'));
    }

    // Guardar el remito
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Crear Encabezado
                $remito = Remito::create([
                    'client_id' => $request->client_id,
                    'date' => $request->date,
                    'observation' => $request->observation,
                    'number' => 'REM-' . time(), // Generamos un número simple por ahora
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

                        // DESCONTAR STOCK (Importante)
                        $product = Product::find($productId);
                        $product->stock -= $qty;
                        $product->save();
                        
                        // Opcional: Registrar también en la tabla movements para historial
                        // $product->movements()->create([...]);
                    }
                }
            });

            return redirect()->route('remitos.index')->with('success', 'Remito generado y stock descontado.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear remito: ' . $e->getMessage());
        }
    }

    // Ver/Imprimir Remito
    public function show(Remito $remito)
    {
        $remito->load('details.product', 'client');
        return view('remitos.show', compact('remito'));
    }
}