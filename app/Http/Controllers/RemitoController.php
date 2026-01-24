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
        // Ordenamos por Tipo y luego por Nombre para agrupar visualmente
        $menus = Menu::orderBy('type')->orderBy('name')->get(); 
        
        return view('remitos.create', compact('clients', 'menus'));
    }

    // 3. GUARDAR Y CALCULAR (CORREGIDO PARA EVITAR ERRORES DE NOMBRE)
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menus'     => 'required|array', 
        ]);

        $client = Client::findOrFail($request->client_id);

        $remito = Remito::create([
            'client_id' => $client->id,
            'date'      => $request->date,
            'number'    => 'REM-' . time(),
            'status'    => 'Generado'
        ]);

        foreach ($request->menus as $menuId) {
            $menu = Menu::with('ingredients')->find($menuId);
            
            if (!$menu) continue;

            // Determinar cupos
            $cantidadAlumnos = 0;
            $tipoMenu = strtolower($menu->type); 

            if (str_contains($tipoMenu, 'comedor alternativo')) $cantidadAlumnos = $client->quota_comedor_alt;
            elseif (str_contains($tipoMenu, 'comedor')) $cantidadAlumnos = $client->quota_comedor;
            elseif (str_contains($tipoMenu, 'dmc alternativo')) $cantidadAlumnos = $client->quota_dmc_alt;
            elseif (str_contains($tipoMenu, 'dmc')) $cantidadAlumnos = $client->quota_dmc;
            elseif (str_contains($tipoMenu, 'listo') || str_contains($tipoMenu, 'lcb')) $cantidadAlumnos = $client->quota_lcb;
            elseif (str_contains($tipoMenu, 'maternal')) $cantidadAlumnos = $client->quota_maternal;

            if ($cantidadAlumnos > 0) {
                foreach ($menu->ingredients as $ingrediente) {
                    
                    // --- CORRECCIÓN DE SEGURIDAD ---
                    // Buscamos el nombre en varios campos para evitar el error "name cannot be null"
                    $nombreIngrediente = $ingrediente->name ?? $ingrediente->nombre ?? $ingrediente->descripcion ?? 'Ingrediente S/N';
                    $unidadIngrediente = $ingrediente->unit ?? $ingrediente->unidad ?? 'u.';

                    $cantidadTotal = $ingrediente->pivot->quantity * $cantidadAlumnos;

                    $remito->items()->create([
                        'name'     => $nombreIngrediente, // Usamos la variable segura
                        'quantity' => $cantidadTotal,
                        'unit'     => $unidadIngrediente, // Usamos la variable segura
                        'observation' => "Menú: {$menu->name} ($cantidadAlumnos cupos)"
                    ]);
                }
            }
        }

        return redirect()->route('remitos.index')->with('success', 'Remito generado correctamente.');
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