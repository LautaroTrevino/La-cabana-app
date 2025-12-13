<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('name')->get(); 
        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255', // Nueva validación
            // Cupos (Se mantienen igual)
            'quota_dmc' => 'nullable|integer|min:0',
            'quota_dmc_alt' => 'nullable|integer|min:0',
            'quota_comedor' => 'nullable|integer|min:0',
            'quota_comedor_alt' => 'nullable|integer|min:0',
            'quota_listo' => 'nullable|integer|min:0',
            'quota_maternal' => 'nullable|integer|min:0',
        ]);

        Client::create($request->all());

        return back()->with('success', 'Escuela agregada correctamente.');
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255', // Nueva validación
            // Cupos
            'quota_dmc' => 'nullable|integer|min:0',
            'quota_dmc_alt' => 'nullable|integer|min:0',
            'quota_comedor' => 'nullable|integer|min:0',
            'quota_comedor_alt' => 'nullable|integer|min:0',
            'quota_listo' => 'nullable|integer|min:0',
            'quota_maternal' => 'nullable|integer|min:0',
        ]);
        
        $client->update($request->all());

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success', 'Escuela eliminada.');
    }

    // Búsqueda para Select2
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        if (!$search) {
            return response()->json(['results' => []]);
        }

        $clients = Client::where(function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('address', 'LIKE', "%{$search}%"); // Buscamos también por dirección
                        })
                        ->select('id', 'name', 'address')
                        ->limit(10)
                        ->get();

        $data = $clients->map(function ($item) {
            // Mostramos "Escuela - Dirección" en el buscador
            $text = $item->name . ($item->address ? ' (' . $item->address . ')' : '');
            return ['id' => $item->id, 'text' => $text];
        });

        return response()->json(['results' => $data]);
    }
}