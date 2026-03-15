<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Tipos canónicos de menú — fuente única de verdad
    public const TIPOS_MENU = [
        'Comedor',
        'Comedor Alternativo',
        'DMC',
        'DMC Alternativo',
        'Listo Consumo',
        'Maternal',
    ];

    public function index()
    {
        // FIX N+1: cargar ingredients junto con los menús para que
        // $menu->ingredients->count() en la vista no dispare una query por fila
        $menus     = Menu::with('ingredients')->orderBy('day_number')->get();
        $tiposMenu = self::TIPOS_MENU;

        return view('menus.index', compact('menus', 'tiposMenu'));
    }

    public function create()
    {
        $tiposMenu   = self::TIPOS_MENU;
        $ingredients = Ingredient::orderBy('name')->get();
        $ingredientsJson = $ingredients->map(fn($i) => [
            'id'        => $i->id,
            'name'      => $i->name,
            'unit_type' => $i->unit_type ?? 'grams',
        ])->toJson();

        return view('menus.create', compact('tiposMenu', 'ingredients', 'ingredientsJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|string',
            'day_number' => 'required|integer|min:1|max:31',
        ]);

        $menu = Menu::create($request->only(['name', 'type', 'day_number']));

        // Procesar ingredientes enviados desde la vista create
        // (misma lógica que update para mantener consistencia)
        $syncData = [];

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $item) {
                if (! empty($item['ingredient_id'])) {
                    $syncData[(int) $item['ingredient_id']] = [
                        'qty_jardin'     => $item['qty_jardin']     ?? 0,
                        'qty_primaria'   => $item['qty_primaria']   ?? 0,
                        'qty_secundaria' => $item['qty_secundaria'] ?? 0,
                        'measure_unit'   => $item['measure_unit']   ?? 'grams',
                    ];
                }
            }
        }

        if (! empty($syncData)) {
            $menu->ingredients()->sync($syncData);
            return redirect()
                ->route('menus.index')
                ->with('success', 'Menú "' . $menu->name . '" creado con ' . count($syncData) . ' ingrediente(s).');
        }

        // Si no se eligió ningún ingrediente, redirigir a editar para cargarlos
        return redirect()
            ->route('menus.edit', $menu->id)
            ->with('success', 'Menú creado. Ahora cargá los ingredientes y sus cantidades.');
    }

    public function edit(Menu $menu)
    {
        $menu->load('ingredients');
        $ingredients = Ingredient::orderBy('name')->get();
        $tiposMenu   = self::TIPOS_MENU;

        return view('menus.edit', compact('menu', 'ingredients', 'tiposMenu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'day_number' => 'required|integer|min:1|max:31',
        ]);

        $menu->update([
            'name'       => $request->name,
            'day_number' => $request->day_number,
            'type'       => $request->type ?? $menu->type,
        ]);

        $syncData = [];

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $item) {
                // Solo incluir filas donde el checkbox estaba tildado (_active = 1)
                if (empty($item['ingredient_id'])) continue;
                if (($item['_active'] ?? '0') !== '1') continue;

                $syncData[(int) $item['ingredient_id']] = [
                    'qty_jardin'     => $item['qty_jardin']     ?? 0,
                    'qty_primaria'   => $item['qty_primaria']   ?? 0,
                    'qty_secundaria' => $item['qty_secundaria'] ?? 0,
                    'measure_unit'   => $item['measure_unit']   ?? 'grams',
                ];
            }
        }

        // sync() agrega los nuevos, actualiza los existentes y elimina los destildados
        $menu->ingredients()->sync($syncData);

        $cantidad = count($syncData);

        return redirect()
            ->route('menus.index')
            ->with('success', "Receta actualizada: {$cantidad} ingrediente(s) guardado(s).");
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menú eliminado correctamente.');
    }

    // Crear ingrediente rápido desde el modal en la vista de edición
    public function storeIngredient(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:ingredients,name',
            'description' => 'nullable|string',
            'unit_type'   => 'required|in:grams,cc,units',
        ]);

        Ingredient::create($request->only(['name', 'description', 'unit_type']));

        return back()->with('success', 'Ingrediente "' . $request->name . '" creado correctamente.');
    }
}
