<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Ingredient; // Importante: Usar el modelo Ingredient
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index() {
        $menus = Menu::orderBy('day_number')->get();
        $tiposMenu = ['Comedor', 'Comedor Alternativo', 'DMC', 'DMC Alternativo', 'Listo Consumo', 'Maternal'];
        return view('menus.index', compact('menus', 'tiposMenu'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'day_number' => 'required|integer',
        ]);
        $menu = Menu::create($request->all());
        return redirect()->route('menus.edit', $menu->id)->with('success', 'Menú creado. Carga los ingredientes.');
    }

    public function edit(Menu $menu) {
        // Traemos los ingredientes para el selector
        $ingredients = Ingredient::orderBy('name')->get(); 
        return view('menus.edit', compact('menu', 'ingredients'));
    }

    public function update(Request $request, Menu $menu) {
        $syncData = [];
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $item) {
                if (isset($item['ingredient_id']) && $item['ingredient_id']) {
                    $syncData[$item['ingredient_id']] = [
                        'qty_jardin'     => $item['qty_jardin'] ?? 0,
                        'qty_primaria'   => $item['qty_primaria'] ?? 0,
                        'qty_secundaria' => $item['qty_secundaria'] ?? 0,
                    ];
                }
            }
        }
        $menu->ingredients()->sync($syncData);
        return redirect()->route('menus.index')->with('success', 'Receta actualizada.');
    }

    // ✅ FUNCIÓN NUEVA: Crear Ingrediente Rápido (con descripción)
    public function storeIngredient(Request $request) {
    $request->validate([
        'name' => 'required|unique:ingredients,name',
        'description' => 'nullable|string',
        'unit_type' => 'required|string' // <--- Agregamos esto
    ]);

    Ingredient::create([
        'name' => $request->name,
        'description' => $request->description,
        'unit_type' => $request->unit_type // <--- Y esto
    ]);

    return back()->with('success', 'Ingrediente creado correctamente.');
}
}