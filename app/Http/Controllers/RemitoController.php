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
    // ─────────────────────────────────────────────────────────────
    // 1. LISTADO MIXTO (REMITOS ADMINISTRATIVOS + ENTREGAS REALES)
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $clients = Client::orderBy('name')->get();

        $queryRemitos  = Remito::with('client')->orderBy('date', 'desc');
        $queryEntregas = OrdenEntrega::with(['client', 'details'])->orderBy('date', 'desc');

        // FIX: usar filled() en lugar de has() para ignorar strings vacíos
        if ($request->filled('client_id')) {
            $queryRemitos->where('client_id', $request->client_id);
            $queryEntregas->where('client_id', $request->client_id);
        }

        if ($request->filled('date_search')) {
            $queryRemitos->whereDate('date', $request->date_search);
            $queryEntregas->whereDate('date', $request->date_search);
        }

        $remitos  = $queryRemitos->get();
        $entregas = $queryEntregas->get();

        return view('remitos.index', compact('remitos', 'entregas', 'clients'));
    }

    // ─────────────────────────────────────────────────────────────
    // 2. FORMULARIO DE CREACIÓN
    //    FIX N+1: eager loading de ingredients para evitar N+1 queries
    //    al llamar $menu->ingredients->count() en la vista
    // ─────────────────────────────────────────────────────────────
    public function create()
    {
        $clients = Client::orderBy('name')->get();

        $menus = Menu::with('ingredients')
                     ->orderBy('type')
                     ->orderBy('day_number')
                     ->orderBy('name')
                     ->get();

        return view('remitos.create', compact('clients', 'menus'));
    }

    // ─────────────────────────────────────────────────────────────
    // 3. GUARDAR Y CALCULAR — MOTOR DE CÁLCULO CORREGIDO
    //
    //  BUG #1 (CRÍTICO): pivot->quantity NO EXISTE en la tabla.
    //          Las columnas reales son qty_jardin, qty_primaria,
    //          qty_secundaria. Se selecciona según client->level.
    //
    //  BUG #2: measure_unit del pivot no estaba incluido en
    //          withPivot(), por lo que siempre era null.
    //
    //  BUG #3: $ingrediente->unit no existe en el modelo Ingredient.
    //          El campo correcto es unit_type.
    //
    //  BUG #4: Se generaban filas con cantidad 0 cuando la escuela
    //          no tenía cupo para ese tipo de menú.
    //
    //  BUG #5 (storeMenu): Método referenciado en routes/web.php
    //          pero inexistente en el controlador → 500 error.
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menus'     => 'required|array|min:1',
        ]);

        $client = Client::findOrFail($request->client_id);

        // ── BUG #1: Resolver columna de cantidad según nivel educativo ──
        $nivel    = strtolower(trim($client->level ?? ''));
        $qtyField = match (true) {
            str_contains($nivel, 'jardin') || str_contains($nivel, 'jardín') => 'qty_jardin',
            str_contains($nivel, 'secundari')                                 => 'qty_secundaria',
            default                                                           => 'qty_primaria',
        };

        $remito = Remito::create([
            'client_id' => $client->id,
            'date'      => $request->date,
            'number'    => 'REM-' . strtoupper(uniqid()),   // más seguro que time()
            'status'    => 'Generado',
        ]);

        foreach ($request->menus as $menuId) {
            $menu = Menu::with('ingredients')->find($menuId);

            if (! $menu) {
                continue;
            }

            // ── Resolver cupo según tipo de menú ──────────────────────
            $tipoMenu        = strtolower(trim($menu->type ?? ''));
            $cantidadAlumnos = 0;

            // Orden: alternativo ANTES que el genérico para evitar falsos matches
            if (str_contains($tipoMenu, 'comedor alternativo')) {
                $cantidadAlumnos = (int) ($client->quota_comedor_alt ?? 0);
            } elseif (str_contains($tipoMenu, 'comedor')) {
                $cantidadAlumnos = (int) ($client->quota_comedor ?? 0);
            } elseif (str_contains($tipoMenu, 'dmc alternativo')) {
                $cantidadAlumnos = (int) ($client->quota_dmc_alt ?? 0);
            } elseif (str_contains($tipoMenu, 'dmc')) {
                $cantidadAlumnos = (int) ($client->quota_dmc ?? 0);
            } elseif (str_contains($tipoMenu, 'listo') || str_contains($tipoMenu, 'lcb')) {
                $cantidadAlumnos = (int) ($client->quota_lcb ?? 0);
            } elseif (str_contains($tipoMenu, 'maternal')) {
                $cantidadAlumnos = (int) ($client->quota_maternal ?? 0);
            }

            // BUG #4: omitir menús sin cupo en esta escuela
            if ($cantidadAlumnos <= 0) {
                continue;
            }

            foreach ($menu->ingredients as $ingrediente) {

                // BUG #1: usar la columna correcta del pivot
                $qtdBase       = (float) ($ingrediente->pivot->{$qtyField} ?? 0);
                $cantidadTotal = $qtdBase * $cantidadAlumnos;

                // BUG #4: no guardar filas vacías
                if ($cantidadTotal <= 0) {
                    continue;
                }

                // BUG #2: measure_unit del pivot (unidad propia de la receta)
                // BUG #3: fallback a unit_type (campo correcto del modelo Ingredient)
                $unidadRaw = $ingrediente->pivot->measure_unit ?? $ingrediente->unit_type ?? 'grams';

                $unidadLabel = match ($unidadRaw) {
                    'grams'  => 'g.',
                    'cc'     => 'cc.',
                    'units'  => 'un.',
                    default  => $unidadRaw,
                };

                $remito->items()->create([
                    'name'        => $ingrediente->name ?? 'Ingrediente S/N',
                    'quantity'    => $cantidadTotal,
                    'unit'        => $unidadLabel,
                    'observation' => "Menú: {$menu->name} ({$cantidadAlumnos} cupos · " . ucfirst($nivel) . ')',
                ]);
            }
        }

        // Redirigir al detalle del remito recién creado en lugar del listado
        return redirect()
            ->route('remitos.show', $remito->id)
            ->with('success', 'Remito generado correctamente.');
    }

    // ─────────────────────────────────────────────────────────────
    // 4. VER DETALLE DEL REMITO
    // ─────────────────────────────────────────────────────────────
    public function show(Remito $remito)
    {
        $remito->load(['client', 'items']);

        return view('remitos.show', compact('remito'));
    }

    // ─────────────────────────────────────────────────────────────
    // 5. GENERAR PDF
    // ─────────────────────────────────────────────────────────────
    public function print(Remito $remito)
    {
        $remito->load(['client', 'items']);

        $pdf = Pdf::loadView('remitos.pdf', compact('remito'));

        return $pdf->stream('remito-' . $remito->number . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────
    // 6. storeMenu — BUG #5: método faltante referenciado en routes
    //    Delega al flujo estándar de store() para no romper la ruta.
    // ─────────────────────────────────────────────────────────────
    public function storeMenu(Request $request)
    {
        return $this->store($request);
    }

    public function destroy(Remito $remito)
    {
        $remito->items()->delete();
        $remito->delete();

        return redirect()
            ->route('remitos.index')
            ->with('success', 'Remito ' . $remito->number . ' eliminado correctamente.');
    }
}
