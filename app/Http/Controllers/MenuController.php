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

    // FIX: método create() faltante — evita el 404 en GET /menus/create
    public function create()
    {
        $tiposMenu = self::TIPOS_MENU;

        return view('menus.create', compact('tiposMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|string',
            'day_number' => 'required|integer|min:1|max:31',
        ]);

        $menu = Menu::create($request->only(['name', 'type', 'day_number']));

        return redirect()
            ->route('menus.edit', $menu->id)
            ->with('success', 'Menú creado. Ahora cargá los ingredientes.');
    }

    public function edit(Menu $menu)
    {
        // Cargamos los ingredientes ya asignados al menú (con pivot) y
        // la lista completa para el selector
        $menu->load('ingredients');
        $ingredients = Ingredient::orderBy('name')->get();

        return view('menus.edit', compact('menu', 'ingredients'));
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
        ]);

        // Sincronizar ingredientes con la tabla pivot
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

        $menu->ingredients()->sync($syncData);

        return redirect()
            ->route('menus.index')
            ->with('success', 'Receta actualizada correctamente.');
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
