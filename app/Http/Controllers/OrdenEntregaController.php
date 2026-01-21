<?php

namespace App\Http\Controllers;

use App\Models\OrdenEntrega;
use App\Models\OrdenEntregaDetail;
use App\Models\Client;
use App\Models\Product;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenEntregaController extends Controller
{
    // --- 1. ENTREGA MANUAL (Sale mercadería suelta del depósito) ---
    public function storeManual(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'items'     => 'required|array|min:1', // Array de productos y cantidades
            'menu_type' => 'nullable|string', // Opcional: si es una entrega extra para Comedor
        ]);

        return DB::transaction(function () use ($request) {
            $numero = 'ORD-' . time();

            $orden = OrdenEntrega::create([
                'client_id'   => $request->client_id,
                'number'      => $numero,
                'date'        => $request->date,
                'menu_type'   => $request->menu_type ?? 'Extra', // Si no especifica, es Extra
                'observation' => $request->observation,
            ]);

            foreach ($request->items as $item) {
                // Registrar detalle
                $orden->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);

                // DESCONTAR STOCK DEL DEPÓSITO
                Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
            }

            return back()->with('success', 'Entrega registrada y stock descontado.');
        });
    }

    // --- 2. GENERAR DESDE MENÚ (Calcula ingredientes y descuenta stock) ---
    public function storeFromMenu(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menu_id'   => 'required|exists:menus,id',
        ]);

        return DB::transaction(function () use ($request) {
            $client = Client::findOrFail($request->client_id);
            $menu = Menu::with('ingredients.product')->findOrFail($request->menu_id);

            // Determinamos el tipo de menú para el Balance (Comedor, DMC, etc)
            $tipoMenu = $menu->type; 

            // Creamos la Orden
            $orden = OrdenEntrega::create([
                'client_id'   => $client->client_id ?? $request->client_id,
                'number'      => 'ORD-MENU-' . time(),
                'date'        => $request->date,
                'menu_type'   => $tipoMenu, 
                'observation' => 'Generado desde Menú: ' . $menu->name,
            ]);

            // Recorremos los ingredientes del menú
            foreach ($menu->ingredients as $ingredient) {
                // Solo nos interesa si el ingrediente está vinculado a un PRODUCTO de stock
                // (Si es "Agua" y no controlas stock de agua, no hacemos nada)
                if ($ingredient->product_id) {
                    
                    // Lógica de cálculo: Cantidad base * Cupo total de la escuela
                    // (Asumimos que el cupo es general para el servicio, ej: 100 chicos en Comedor)
                    
                    $cupoTotal = 0;
                    if (in_array($tipoMenu, ['Comedor', 'Comedor Alternativo'])) {
                        $cupoTotal = $client->quota_comedor + $client->quota_comedor_alt;
                    } elseif (in_array($tipoMenu, ['DMC', 'DMC Alternativo'])) {
                        $cupoTotal = $client->quota_dmc + $client->quota_dmc_alt;
                    } else {
                        // Si no coincide, usamos la suma general o 0
                        $cupoTotal = $client->quota_comedor + $client->quota_dmc; 
                    }

                    // Cantidad Total a sacar del depósito
                    // La tabla pivot (ingredient_menu) debe tener la cantidad por ración
                    // Ajusta 'qty_jardin' etc. a 'quantity_per_ration' si simplificaste tu modelo de Menú
                    // Aquí asumo un promedio o suma de las cantidades base configuradas en el menú
                    
                    $cantidadBase = $ingredient->pivot->qty_primaria ?? 0; // Usamos primaria como base ref
                    $cantidadTotal = $cantidadBase * $cupoTotal;

                    if ($cantidadTotal > 0) {
                        // Guardamos detalle
                        $orden->details()->create([
                            'product_id' => $ingredient->product_id,
                            'quantity'   => $cantidadTotal,
                        ]);

                        // DESCONTAMOS STOCK
                        Product::where('id', $ingredient->product_id)->decrement('stock', $cantidadTotal);
                    }
                }
            }

            return back()->with('success', 'Orden de entrega generada y stock descontado.');
        });
    }
}