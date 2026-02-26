<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    private $tiposPermitidos = [
        'Comedor', 
        'Comedor Alternativo', 
        'DMC', 
        'DMC Alternativo', 
        'Maternal', 
        'Listo Consumo'
    ];

    public function index()
    {
        $menus = Menu::orderBy('day_number')->orderBy('name')->get();
        $tiposMenu = $this->tiposPermitidos;
        return view('menus.index', compact('menus', 'tiposMenu'));
    }

    public function create()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        $tiposMenu = $this->tiposPermitidos;
        return view('menus.create', compact('ingredients', 'tiposMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
            'day_number' => 'required|integer|min:1' 
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'type' => $request->type,
            'day_number' => $request->day_number 
        ]);

        // Guardamos ingredientes (detecta checkboxes O cantidades escritas)
        $this->syncIngredients($menu, $request);

        return redirect()->route('menus.index')->with('success', 'Menú creado correctamente.');
    }

    public function edit(Menu $menu)
    {
        $ingredients = Ingredient::orderBy('name')->get();
        $tiposMenu = $this->tiposPermitidos;
        return view('menus.edit', compact('menu', 'ingredients', 'tiposMenu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
            'day_number' => 'required|integer|min:1'
        ]);

        $menu->update([
            'name' => $request->name,
            'type' => $request->type,
            'day_number' => $request->day_number
        ]);

        // Guardamos ingredientes (detecta checkboxes O cantidades escritas)
        $this->syncIngredients($menu, $request);

        return redirect()->route('menus.index')->with('success', 'Menú actualizado correctamente.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menú eliminado.');
    }

    /**
     * FUNCIÓN INTELIGENTE DE GUARDADO
     * Guarda el ingrediente si:
     * 1. Está marcado con el Checkbox.
     * 2. O SI NO ESTÁ MARCADO pero tiene alguna cantidad mayor a 0.
     */
    private function syncIngredients($menu, $request)
    {
        $syncData = [];
        $items = $request->input('items', []); // Array de cantidades (viene de todos los ingredientes)
        $checked = $request->input('ingredients', []); // Array de checkboxes (solo lo marcado)

        // Si $checked es null (ninguno marcado), lo hacemos array vacío
        if (!is_array($checked)) {
            $checked = [];
        }

        // Recorremos TODOS los ingredientes que aparecen en el formulario
        foreach ($items as $ingId => $data) {
            
            $qJ = floatval($data['qty_jardin'] ?? 0);
            $qP = floatval($data['qty_primaria'] ?? 0);
            $qS = floatval($data['qty_secundaria'] ?? 0);
            
            // CONDICIÓN MÁGICA:
            // Guardamos si el ID está en los checkboxes ($checked)
            // O SI la suma de cantidades es mayor a 0 (el usuario escribió algo)
            if (in_array($ingId, $checked) || ($qJ > 0 || $qP > 0 || $qS > 0)) {
                
                $syncData[$ingId] = [
                    'measure_unit'   => $data['measure_unit'] ?? 'kg',
                    'qty_jardin'     => $qJ,
                    'qty_primaria'   => $qP,
                    'qty_secundaria' => $qS,
                ];
            }
        }

        // Sincronizamos (esto borra los viejos y pone los nuevos de la lista filtrada)
        $menu->ingredients()->sync($syncData);
    }
    
    // API Rápida para crear ingredientes desde el modal
    public function storeIngredient(Request $request) 
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'unit' => 'required|string'
        ]);

        Ingredient::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'stock' => 0, 
            'cost' => 0
        ]);

        return back()->with('success', 'Ingrediente agregado: ' . $request->name);
    }
}