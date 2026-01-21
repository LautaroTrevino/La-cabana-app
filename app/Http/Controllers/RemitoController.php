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
    /**
     * LISTADO PRINCIPAL
     */
    public function index(Request $request) 
    {
        // 1. Consulta base
        $query = Remito::with(['client', 'details.product', 'details.ingredient'])->latest();

        // 2. Filtros
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_search')) {
            $query->whereDate('date', $request->date_search);
        } elseif ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        }

        // 3. Separar colecciones para las pestañas de la vista
        $entregas = (clone $query)->where('tipo', 'entrega')->get();
        $remitosAdmin = (clone $query)->where('tipo', 'remito')->get();
        
        $clients = Client::orderBy('name')->get();
        $menus = Menu::orderBy('name')->get(); 
        
        return view('remitos.index', compact('entregas', 'remitosAdmin', 'clients', 'menus'));
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

    // --- FLUJO DEPÓSITO (Manual con descuento de stock) ---
    public function createEntrega()
    {
        $clients = Client::orderBy('name')->get();
        // Solo productos con stock > 0
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        return view('deposito.create', compact('clients', 'products'));
    }

    public function storeEntrega(Request $request)
    {
        $request->merge(['tipo' => 'entrega']);
        return $this->processSave($request, true);
    }

    /**
     * GENERAR DESDE MENÚ
     * Calcula: (Cantidad Base x Cupo) y guarda el total en base de datos.
     * DETECTA EL TIPO DE MENÚ PARA EL BALANCE.
     */
    public function storeMenu(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menus'     => 'required|array|min:1', 
        ]);

        return DB::transaction(function () use ($request) {
            $client = Client::findOrFail($request->client_id);
            
            // --- DETECTAR CATEGORÍA PARA BALANCE ---
            // Tomamos el primer menú seleccionado para saber el tipo (DMC, Comedor, etc.)
            $firstMenu = Menu::find($request->menus[0]);
            $categoriaMenu = $firstMenu ? $firstMenu->type : null; 
            // ---------------------------------------

            $numeroRemito = 'REM-MENU-' . time();

            $remito = Remito::create([
                'client_id'   => $request->client_id,
                'number'      => $numeroRemito,
                'date'        => $request->date,
                'tipo'        => 'remito', 
                
                // GUARDAMOS LA CATEGORÍA AQUÍ (Importante para Balance):
                'menu_type'   => $categoriaMenu, 
                
                'observation' => 'Generado desde Menú (' . ($categoriaMenu ?? 'Gral') . ')',
            ]);

            foreach ($request->menus as $menuId) {
                $menu = Menu::with('ingredients')->find($menuId); 

                if ($menu) {
                    foreach ($menu->ingredients as $ingredient) {
                        // Cálculo: Gramos/CC/Un base * Cupos de la escuela
                        $totalJardin    = ($ingredient->pivot->qty_jardin ?? 0) * ($client->cupo_jardin ?? 0);
                        $totalPrimaria  = ($ingredient->pivot->qty_primaria ?? 0) * ($client->cupo_primaria ?? 0);
                        $totalSecundaria = ($ingredient->pivot->qty_secundaria ?? 0) * ($client->cupo_secundaria ?? 0);

                        $cantidadCalculada = $totalJardin + $totalPrimaria + $totalSecundaria;

                        if ($cantidadCalculada > 0) {
                            $remito->details()->create([
                                'product_id'    => null, 
                                'ingredient_id' => $ingredient->id,
                                'quantity'      => $cantidadCalculada,
                                'measure_unit'  => $ingredient->pivot->measure_unit ?? 'grams'
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('remitos.index')->with('success', 'Remito generado con éxito.');
        });
    }

    /**
     * VISTA DEL REMITO (Conversión Visual)
     */
    public function show(Remito $remito)
    {
        $remito->load(['client', 'details.product', 'details.ingredient']);
        
        // Transformamos los detalles para mostrar Kilos/Litros en la vista
        $remito->details->transform(function ($detail) {
            return $this->convertUnits($detail);
        });

        return view('remitos.show', compact('remito'));
    }

    /**
     * PDF DEL REMITO (Conversión Visual)
     */
    public function print(Remito $remito)
    {
        $remito->load(['client', 'details.product', 'details.ingredient']);
        
        // Aplicamos la misma conversión para el PDF
        $remito->details->transform(function ($detail) {
            return $this->convertUnits($detail);
        });

        $pdf = Pdf::loadView('remitos.pdf', compact('remito'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream("Remito_{$remito->number}.pdf");
    }

    // --- FUNCIONES AUXILIARES PRIVADAS ---

    /**
     * Lógica común para guardar remitos manuales
     */
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

    /**
     * Transforma gramos a kilos y cc a litros solo para visualización
     */
    private function convertUnits($detail)
    {
        // Si no es un ingrediente (es un producto directo), no convertimos
        if (!$detail->ingredient_id) {
            $detail->display_quantity = $detail->quantity;
            $detail->display_unit = 'un.';
            return $detail;
        }

        $unit = $detail->measure_unit ?? 'grams';

        if ($unit === 'grams') {
            $detail->display_quantity = $detail->quantity / 1000;
            $detail->display_unit = 'kg.';
        } elseif ($unit === 'cc') {
            $detail->display_quantity = $detail->quantity / 1000;
            $detail->display_unit = 'lts.';
        } else {
            $detail->display_quantity = $detail->quantity;
            $detail->display_unit = 'un.';
        }

        return $detail;
    }
}