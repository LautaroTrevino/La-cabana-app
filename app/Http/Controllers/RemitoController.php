<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoDetail;
use App\Models\Client;
use App\Models\Product; // <--- Esta es la que faltaba
use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RemitoController extends Controller
{
   public function index() {
    $remitos = Remito::with(['client', 'details'])->latest()->get();
    $clients = Client::orderBy('name')->get();
    $products = Product::orderBy('name')->get(); // Para otros usos
    $menus = Menu::orderBy('type')->orderBy('day_number')->get(); // Necesaria para el modal

    return view('remitos.index', compact('remitos', 'clients', 'products', 'menus'));
}

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        // Traemos menús ordenados
        $menus = Menu::orderBy('type')->orderBy('day_number')->get();
        
        return view('remitos.create', compact('clients', 'menus'));
    }

    /**
     * 🧠 EL CEREBRO DE LA OPERACIÓN
     * Genera un remito calculando ingredientes de MÚLTIPLES menús
     */
    public function storeRemitoOficial(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menu_ids'  => 'required|array', // Array con los IDs de los menús seleccionados
            'menu_ids.*'=> 'exists:menus,id',
        ]);

        try {
            DB::beginTransaction();

            // 1. Obtenemos el cliente para saber sus cupos y nivel
            $client = Client::findOrFail($request->client_id);
            $nivelCliente = $client->level; // 'jardin', 'primaria', 'secundaria'

            // 2. Preparamos un array para consolidar ingredientes
            // Clave: ID Ingrediente -> Valor: Cantidad Total
            $ingredientesConsolidados = [];

            // 3. Mapeo de Tipos de Menú a Columnas de Cupos en la tabla Clients
            $mapaCupos = [
                'Comedor'             => 'quota_comedor',
                'Comedor Alternativo' => 'quota_comedor_alt',
                'DMC'                 => 'quota_dmc',
                'DMC Alternativo'     => 'quota_dmc_alt',
                'Listo Consumo'       => 'quota_listo',
                'Maternal'            => 'quota_maternal',
            ];

            // 4. Recorremos cada menú seleccionado para hacer los cálculos
            foreach ($request->menu_ids as $menuId) {
                $menu = Menu::with('ingredients')->find($menuId);
                
                // Determinamos qué cupo usar según el tipo de menú
                $columnaCupo = $mapaCupos[$menu->type] ?? null;
                $cantidadCupos = $columnaCupo ? $client->$columnaCupo : 0;

                if ($cantidadCupos > 0) {
                    foreach ($menu->ingredients as $ingrediente) {
                        // Obtenemos la cantidad unitaria para el nivel de la escuela
                        // Ej: pivot->qty_primaria
                        $cantidadUnitaria = $ingrediente->pivot->{'qty_' . $nivelCliente};

                        // Cálculo: Unitaria * Cupos de ese servicio
                        $totalIngrediente = $cantidadUnitaria * $cantidadCupos;

                        // Sumamos al consolidado (Si ya existe aceite, le sumamos más aceite)
                        if (isset($ingredientesConsolidados[$ingrediente->id])) {
                            $ingredientesConsolidados[$ingrediente->id] += $totalIngrediente;
                        } else {
                            $ingredientesConsolidados[$ingrediente->id] = $totalIngrediente;
                        }
                    }
                }
            }

            // 5. Si no hay ingredientes (ej: cupos en 0), error.
            if (empty($ingredientesConsolidados)) {
                return back()->with('error', 'No se generaron ingredientes. Verifique que el cliente tenga cupos asignados para los menús seleccionados.');
            }

            // 6. Creamos el Remito
            $remito = Remito::create([
                'client_id'   => $client->id,
                'date'        => $request->date,
                'observation' => 'Generado automáticamente desde menús.',
                'number'      => 'REM-' . time(),
                'tipo'        => 'remito', 
            ]);

            // 7. Guardamos los detalles consolidados
            foreach ($ingredientesConsolidados as $ingredientId => $qtyTotal) {
                if ($qtyTotal > 0) {
                    // OJO: Aquí guardamos en una tabla que soporte "ingredient_id"
                    // Si tu tabla remito_details usa product_id, necesitaremos ajustar eso.
                    // Asumiré que quieres guardar texto o relacionar con ingredientes.
                    // Para simplificar, guardaremos el nombre del ingrediente en "observation" o 
                    // idealmente deberías tener un remito_detail_ingredients.
                    
                    // SOLUCIÓN RÁPIDA: Guardamos en remito_details vinculando al ingrediente
                    // Asegurate de que tu modelo RemitoDetail tenga 'ingredient_id'
                    
                    RemitoDetail::create([
                        'remito_id'     => $remito->id,
                        'ingredient_id' => $ingredientId, // <--- Nueva columna necesaria o usar product_id si son lo mismo
                        'quantity'      => $qtyTotal
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('remitos.index')->with('success', 'Remito generado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    // ... show y store (entrega) ...
    public function show(Remito $remito)
    {
        // Cargamos la relación con ingredientes
        $remito->load(['details.ingredient', 'client']);
        return view('remitos.show', compact('remito'));
    }

    public function print(Remito $remito)
    {
    // Carga los datos necesarios para que el PDF no de error
    $remito->load(['client', 'details.ingredient', 'details.product']);

    // Crea el PDF usando la plantilla que hicimos en el paso 2
    $pdf = Pdf::loadView('remitos.pdf', compact('remito'));

    // IMPORTANTE: Configurar tamaño CARTA
    $pdf->setPaper('letter', 'portrait');

    // Abre el PDF en una pestaña nueva
    return $pdf->stream("Remito_{$remito->number}.pdf");
    }
}