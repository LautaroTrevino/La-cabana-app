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

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        // 1. Validamos los datos
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'cuit'    => 'nullable|string|max:20',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            
            // Cupos (pueden venir nulos del formulario)
            'quota_comedor'     => 'nullable|integer|min:0',
            'quota_comedor_alt' => 'nullable|integer|min:0',
            'quota_dmc'         => 'nullable|integer|min:0',
            'quota_dmc_alt'     => 'nullable|integer|min:0',
            'quota_lcb'         => 'nullable|integer|min:0',
            'quota_maternal'    => 'nullable|integer|min:0',
        ]);

        // 2. CORRECCIÓN: Convertimos cualquier nulo en 0 antes de guardar
        $cuposLimpios = [
            'quota_comedor'     => $request->quota_comedor ?? 0,
            'quota_comedor_alt' => $request->quota_comedor_alt ?? 0,
            'quota_dmc'         => $request->quota_dmc ?? 0,
            'quota_dmc_alt'     => $request->quota_dmc_alt ?? 0,
            'quota_lcb'         => $request->quota_lcb ?? 0,
            'quota_maternal'    => $request->quota_maternal ?? 0,
        ];

        // Fusionamos los datos validados con los cupos limpios (que ahora son ceros si estaban vacíos)
        Client::create(array_merge($validated, $cuposLimpios));

        return redirect()->route('clients.index')->with('success', 'Escuela creada exitosamente.');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        // 1. Validamos
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'cuit'    => 'nullable|string|max:20',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',

            'quota_comedor'     => 'nullable|integer|min:0',
            'quota_comedor_alt' => 'nullable|integer|min:0',
            'quota_dmc'         => 'nullable|integer|min:0',
            'quota_dmc_alt'     => 'nullable|integer|min:0',
            'quota_lcb'         => 'nullable|integer|min:0',
            'quota_maternal'    => 'nullable|integer|min:0',
        ]);
        
        // 2. CORRECCIÓN: Convertimos nulos en 0
        $cuposLimpios = [
            'quota_comedor'     => $request->quota_comedor ?? 0,
            'quota_comedor_alt' => $request->quota_comedor_alt ?? 0,
            'quota_dmc'         => $request->quota_dmc ?? 0,
            'quota_dmc_alt'     => $request->quota_dmc_alt ?? 0,
            'quota_lcb'         => $request->quota_lcb ?? 0,
            'quota_maternal'    => $request->quota_maternal ?? 0,
        ];

        // Actualizamos con los datos seguros
        $client->update(array_merge($validated, $cuposLimpios));

        return redirect()->route('clients.index')->with('success', 'Datos actualizados correctamente.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success', 'Escuela eliminada.');
    }
}