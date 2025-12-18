<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    /**
     * Muestra la lista de ingredientes para editar o borrar.
     */
    public function index()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('ingredients.index', compact('ingredients'));
    }

    /**
     * Guarda un ingrediente nuevo (usado por el modal de recetas).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:ingredients,name',
            'unit_type' => 'required',
            'description' => 'nullable'
        ]);

        Ingredient::create($request->all());

        return back()->with('success', 'Ingrediente creado con éxito.');
    }

    /**
     * Actualiza un ingrediente existente.
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'name' => 'required|unique:ingredients,name,' . $ingredient->id,
            'unit_type' => 'required',
            'description' => 'nullable'
        ]);

        $ingredient->update($request->all());

        return back()->with('success', 'Ingrediente actualizado correctamente.');
    }

    /**
     * Elimina el ingrediente si no está en uso.
     */
    public function destroy(Ingredient $ingredient)
    {
        // Verificamos si tiene menús asociados para no romper las recetas
        if ($ingredient->menus()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: este ingrediente forma parte de una o más recetas.');
        }

        $ingredient->delete();

        return back()->with('success', 'Ingrediente eliminado del sistema.');
    }
}