<?php

namespace App\Http\Controllers;

use App\Models\OrdenEntrega;
use App\Models\Product;
use App\Models\Client; // <--- Agregado para poder buscar las escuelas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenEntregaController extends Controller
{
    // --- 1. GUARDAR ENTREGA REAL (Desde Escáner o Carga Manual) ---
    public function storeReal(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menu_type' => 'required|string', // VITAL: "Comedor", "DMC", etc.
            'items'     => 'required|array|min:1', 
        ]);

        return DB::transaction(function () use ($request) {
            
            // 1. Crear la Cabecera de la Orden
            $orden = OrdenEntrega::create([
                'client_id'   => $request->client_id,
                'number'      => 'ORD-' . time(),
                'date'        => $request->date,
                'menu_type'   => $request->menu_type, 
                'observation' => $request->observation,
            ]);

            // 2. Procesar los ítems FÍSICOS
            foreach ($request->items as $item) {
                if ($item['qty'] > 0) {
                    // A. Guardamos el detalle
                    $orden->details()->create([
                        'product_id' => $item['id'],
                        'quantity'   => $item['qty'],
                    ]);

                    // B. DESCONTAMOS STOCK
                    Product::where('id', $item['id'])->decrement('stock', $item['qty']);
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Orden guardada y stock descontado.']);
            }

            return back()->with('success', 'Entrega registrada y stock descontado.');
        });
    }

    // --- 2. MOSTRAR PANTALLA DE ESCÁNER ---
    public function create()
    {
        // Traemos las escuelas para el desplegable
        $clients = Client::orderBy('name')->get();
        
        // Traemos productos para el buscador (solo los que tengan stock positivo opcionalmente)
        // Si quieres ver todos aunque no haya stock, quita el ->where('stock', '>', 0)
        $products = Product::orderBy('name')->get();

        return view('deposito.create', compact('clients', 'products'));
    }
}