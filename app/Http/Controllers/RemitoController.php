<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\Client;
use App\Models\Menu;
use App\Models\OrdenEntrega; 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; 

class RemitoController extends Controller
{
    // 1. LISTADO MIXTO (REMITOS Y ENTREGAS)
    public function index(Request $request)
    {
        $clients = Client::orderBy('name')->get();

        $queryRemitos = Remito::with('client')->orderBy('date', 'desc');
        $queryEntregas = OrdenEntrega::with(['client', 'details'])->orderBy('date', 'desc');

        // Filtros
        if ($request->has('client_id') && $request->client_id != '') {
            $queryRemitos->where('client_id', $request->client_id);
            $queryEntregas->where('client_id', $request->client_id);
        }

        if ($request->has('date_search') && $request->date_search != '') {
            $queryRemitos->whereDate('date', $request->date_search);
            $queryEntregas->whereDate('date', $request->date_search);
        }

        $remitos = $queryRemitos->get();
        $entregas = $queryEntregas->get();

        return view('remitos.index', compact('remitos', 'entregas', 'clients'));
    }

    // 2. FORMULARIO PARA CREAR NUEVO REMITO
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        // Ordenamos por Tipo y luego por Nombre
        $menus = Menu::orderBy('type')->orderBy('name')->get(); 
        
        return view('remitos.create', compact('clients', 'menus'));
    }

    // 3. GUARDAR Y CALCULAR (CORREGIDO: Nombre seguro)
    public function store(Request $request)
    {
        // ... (Validaciones iniciales y creación del Remito igual que antes) ...
        $request->validate([
            'client_id' => 'required', 'date' => 'required', 'menus' => 'required|array'
        ]);

        $client = \App\Models\Client::findOrFail($request->client_id);
        
        $remito = \App\Models\Remito::create([
            'client_id' => $client->id,
            'date' => $request->date,
            'number' => 'REM-' . time(),
            'status' => 'Generado'
        ]);

        foreach ($request->menus as $menuId) {
            $menu = \App\Models\Menu::with('ingredients')->find($menuId);
            if (!$menu) continue;

            // 1. DETERMINAR QUÉ CUPO USAR SEGÚN EL TIPO DE MENÚ
            $cupos = 0;
            $tipo = strtolower($menu->type);

            if (str_contains($tipo, 'dmc alternativo')) $cupos = $client->quota_dmc_alt;
            elseif (str_contains($tipo, 'dmc')) $cupos = $client->quota_dmc;
            elseif (str_contains($tipo, 'comedor alternativo')) $cupos = $client->quota_comedor_alt;
            elseif (str_contains($tipo, 'comedor')) $cupos = $client->quota_comedor;
            // ... agregar resto de lógicas ...

            if ($cupos > 0) {
                foreach ($menu->ingredients as $ing) {
                    
                    // 2. DETERMINAR QUÉ CANTIDAD DE LA RECETA USAR SEGÚN EL NIVEL DE LA ESCUELA
                    // El campo 'level' debe existir en tu tabla clients (Jardin, Primaria, Secundaria)
                    $cantidadReceta = 0;
                    $nivelEscuela = strtolower($client->level); // Asegúrate que en Client.php tengas este campo

                    if (str_contains($nivelEscuela, 'jardin') || str_contains($nivelEscuela, 'inicial')) {
                        $cantidadReceta = $ing->pivot->qty_jardin;
                    } elseif (str_contains($nivelEscuela, 'secundaria')) {
                        $cantidadReceta = $ing->pivot->qty_secundaria;
                    } else {
                        // Por defecto asumimos Primaria si no aclara
                        $cantidadReceta = $ing->pivot->qty_primaria;
                    }

                    // 3. CÁLCULO FINAL
                    $total = $cantidadReceta * $cupos;

                    if ($total > 0) {
                        $remito->items()->create([
                            'name' => $ing->name,
                            'quantity' => $total,
                            'unit' => $ing->pivot->measure_unit, // Usamos la unidad declarada en el menú
                            'observation' => "Menú: {$menu->name} (Nivel: $client->level)"
                        ]);
                    }
                }
            }
        }
        
        return redirect()->route('remitos.index');
    }

    // 4. VER EL REMITO
    public function show(Remito $remito)
    {
        return view('remitos.show', compact('remito'));
    }

    // 5. IMPRIMIR PDF
    public function print(Remito $remito)
    {
        $pdf = Pdf::loadView('remitos.pdf', compact('remito'));
        return $pdf->stream('remito-'.$remito->number.'.pdf');
    }
}