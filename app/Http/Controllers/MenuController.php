<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Ingredient; 
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index() {
        $menus = Menu::orderBy('day_number')->get();
        // Tipos de menú disponibles
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
        
        return redirect()->route('menus.edit', $menu->id)
            ->with('success', 'Menú creado. Ahora carga los ingredientes.');
    }

    public function edit(Menu $menu) {
        // Ordenamos ingredientes alfabéticamente para el selector
        $ingredients = Ingredient::orderBy('name')->get(); 
        return view('menus.edit', compact('menu', 'ingredients'));
    }

    public function update(Request $request, Menu $menu) {
        // 1. VALIDACIÓN (Evita el error de datos nulos)
        $request->validate([
            'name' => 'required|string|max:255',
            'day_number' => 'required|integer',
        ]);

        // 2. Actualizamos datos básicos del Menú
        $menu->update([
            'name' => $request->name,
            'day_number' => $request->day_number,
        ]);

        // 3. Sincronización de ingredientes (Tabla Pivot)
        $syncData = [];
        
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $item) {
                // Solo procesamos si hay un ID de ingrediente válido
                if (isset($item['ingredient_id']) && $item['ingredient_id']) {
                    
                    $syncData[$item['ingredient_id']] = [
                        'qty_jardin'     => $item['qty_jardin'] ?? 0,
                        'qty_primaria'   => $item['qty_primaria'] ?? 0,
                        'qty_secundaria' => $item['qty_secundaria'] ?? 0,
                        
                        // Guardamos la unidad seleccionada (grams, cc, units)
                        // Si falla o viene vacío, por defecto 'grams'
                        'measure_unit'   => $item['measure_unit'] ?? 'grams', 
                    ];
                }
            }
        }

        // Guardamos las relaciones
        $menu->ingredients()->sync($syncData);

        return redirect()->route('menus.index')->with('success', 'Receta actualizada correctamente.');
    }

    // Eliminar Menú
    public function destroy(Menu $menu) {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menú eliminado correctamente.');
    }

    // Crear Ingrediente Rápido (Modal en la vista de edición)
    public function storeIngredient(Request $request) {
        $request->validate([
            'name' => 'required|unique:ingredients,name',
            'description' => 'nullable|string',
            // Forzamos a que sean unidades válidas
            'unit_type' => 'required|in:grams,cc,units' 
        ]);

        Ingredient::create([
            'name' => $request->name,
            'description' => $request->description,
            'unit_type' => $request->unit_type 
        ]);

        return back()->with('success', 'Ingrediente creado correctamente.');
    }
}