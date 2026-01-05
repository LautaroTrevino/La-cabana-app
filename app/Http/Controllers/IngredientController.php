<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    /**
     * Muestra la lista de ingredientes.
     */
    public function index()
    {
        // Ordenamos alfabéticamente para facilitar la búsqueda
        $ingredients = Ingredient::orderBy('name')->get();
        return view('ingredients.index', compact('ingredients'));
    }

    /**
     * Guarda un ingrediente nuevo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:ingredients,name|max:255',
            // VALIDACIÓN IMPORTANTE: Solo permitimos las unidades estándar del sistema
            'unit_type' => 'required|in:grams,cc,units', 
            'description' => 'nullable|string|max:500'
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
            // Ignoramos el ID actual para que no de error de "nombre ya existe" sobre sí mismo
            'name' => 'required|max:255|unique:ingredients,name,' . $ingredient->id,
            'unit_type' => 'required|in:grams,cc,units',
            'description' => 'nullable|string|max:500'
        ]);

        $ingredient->update($request->all());

        return back()->with('success', 'Ingrediente actualizado correctamente.');
    }

    /**
     * Elimina el ingrediente si no está en uso en ninguna receta.
     */
    public function destroy(Ingredient $ingredient)
    {
        // Protección de integridad: No borrar si se usa en un menú
        // Asumiendo que tienes la relación 'menus' en el modelo Ingredient
        if ($ingredient->menus()->count() > 0) {
            return back()->with('error', '⚠️ No se puede eliminar: El ingrediente "' . $ingredient->name . '" se usa en una o más recetas activas.');
        }

        $ingredient->delete();

        return back()->with('success', 'Ingrediente eliminado del sistema.');
    }
}