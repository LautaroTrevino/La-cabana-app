<?php

namespace App\Http\Controllers;

use App\Models\OrdenEntrega;
use App\Models\Product;
use App\Models\Client; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenEntregaController extends Controller
{
    // --- 1. GUARDAR ENTREGA REAL (Desde Escáner del Depósito) ---
    public function storeReal(Request $request)
    {
        // 1. VALIDACIÓN ROBUSTA
        // Agregamos validación interna del array 'items' para evitar errores de base de datos
        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'date'        => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.id'  => 'required|exists:products,id', // Verifica que el producto exista
            'items.*.qty' => 'required|numeric|min:0',      // Verifica que la cantidad sea positiva
        ]);

        return DB::transaction(function () use ($request) {
            
            // 2. CREAR CABECERA (La Orden)
            // Esto servirá de "testigo" para que el Balance sepa que ese día hubo servicio.
            $orden = OrdenEntrega::create([
                'client_id'   => $request->client_id,
                'number'      => 'ORD-' . time(), // Genera un número único basado en la hora
                'date'        => $request->date,
                'menu_type'   => 'Entrega General', // Etiqueta fija para identificar salidas de depósito
                'observation' => $request->observation,
            ]);

            // 3. PROCESAR ÍTEMS FÍSICOS (Stock y Costos)
            foreach ($request->items as $item) {
                // Solo procesamos si la cantidad es mayor a 0
                if ($item['qty'] > 0) {
                    
                    // A. Guardamos el detalle en la orden (queda registro de qué se llevó)
                    $orden->details()->create([
                        'product_id' => $item['id'],
                        'quantity'   => $item['qty'],
                    ]);

                    // B. DESCONTAMOS STOCK DEL INVENTARIO
                    Product::where('id', $item['id'])->decrement('stock', $item['qty']);
                }
            }

            // 4. RESPUESTA (JSON para el Javascript del Escáner)
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Entrega registrada y stock descontado correctamente.']);
            }

            return back()->with('success', 'Entrega registrada y stock descontado.');
        });
    }

    // --- 2. MOSTRAR PANTALLA DE ESCÁNER ---
    public function create()
    {
        // Enviamos la lista de Escuelas y Productos para el formulario
        $clients = Client::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('deposito.create', compact('clients', 'products'));
    }
}