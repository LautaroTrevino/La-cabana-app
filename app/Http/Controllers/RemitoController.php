<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoDetail;
use App\Models\Client;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Menu; 

class RemitoController extends Controller
{
    public function index() 
    {
        $remitos = Remito::with(['client', 'details.product', 'details.ingredient'])->latest()->get();
        $clients = Client::orderBy('name')->get();
        $menus = Menu::orderBy('name')->get(); 
        
        return view('remitos.index', compact('remitos', 'clients', 'menus'));
    }

    // --- FLUJO ADMINISTRATIVO (Manual) ---
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('remitos.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $request->merge(['tipo' => 'remito']);
        return $this->processSave($request, false);
    }

    // --- FLUJO DEPÓSITO (Manual) ---
    public function createEntrega()
    {
        $clients = Client::orderBy('name')->get();
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        return view('deposito.create', compact('clients', 'products'));
    }

    public function storeEntrega(Request $request)
    {
        $request->merge(['tipo' => 'entrega']);
        return $this->processSave($request, true);
    }

    // --- GENERAR DESDE MENÚ (Corregido para Ingredients) ---
    public function storeMenu(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menus'     => 'required|array|min:1', 
        ]);

        return DB::transaction(function () use ($request) {
            
            $numeroRemito = 'REM-MENU-' . time();

            $remito = Remito::create([
                'client_id'   => $request->client_id,
                'number'      => $numeroRemito,
                'date'        => $request->date,
                'tipo'        => 'remito', 
                'observation' => 'Generado automáticamente desde Menú',
            ]);

            foreach ($request->menus as $menuId) {
                // CORRECCIÓN: Tu modelo usa 'ingredients', no 'details'
                $menu = Menu::with('ingredients')->find($menuId); 

                if ($menu) {
                    foreach ($menu->ingredients as $ingredient) {
                        // Cálculo de cantidad sumando los 3 niveles del pivot
                        $cantidadTotal = ($ingredient->pivot->qty_jardin ?? 0) + 
                                         ($ingredient->pivot->qty_primaria ?? 0) + 
                                         ($ingredient->pivot->qty_secundaria ?? 0);

                        // Solo agregamos si la cantidad es mayor a 0
                        if ($cantidadTotal > 0) {
                            $remito->details()->create([
                                'product_id'    => null, 
                                'ingredient_id' => $ingredient->id,
                                'quantity'      => $cantidadTotal,
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('remitos.index')->with('success', 'Remito generado con los ingredientes del menú correctamente.');
        });
    }

    // --- LÓGICA COMÚN PARA MANUAL ---
    private function processSave(Request $request, $shouldDiscountStock)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'items'     => 'required|array|min:1',
        ]);

        return DB::transaction(function () use ($request, $shouldDiscountStock) {
            
            $numeroRemito = $request->number ?? 'ENT-' . time();

            $remito = Remito::create([
                'client_id'   => $request->client_id,
                'number'      => $numeroRemito,
                'date'        => $request->date,
                'tipo'        => $request->tipo,
                'observation' => $request->observation,
            ]);

            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                $remito->details()->create([
                    'product_id'    => $productId,
                    'ingredient_id' => null,
                    'quantity'      => $quantity,
                ]);

                if ($shouldDiscountStock) {
                    Product::where('id', $productId)->decrement('stock', $quantity);
                }
            }

            $route = $shouldDiscountStock ? 'products.index' : 'remitos.index';
            $message = $shouldDiscountStock ? 'Stock descontado y entrega registrada.' : 'Remito creado exitosamente.';

            return redirect()->route($route)->with('success', $message);
        });
    }

    // --- SHOW Y PDF ---
    public function show(Remito $remito)
    {
        $remito->load(['client', 'details.product', 'details.ingredient']);
        return view('remitos.show', compact('remito'));
    }

    public function print(Remito $remito)
    {
        $remito->load(['client', 'details.product', 'details.ingredient']);
        $pdf = Pdf::loadView('remitos.pdf', compact('remito'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream("Documento_{$remito->number}.pdf");
    }
}