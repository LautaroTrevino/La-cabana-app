<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\GlobalPrice;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('name')->get(); 
        
        // Enviamos precios para el modal de configuración rápida
        $prices = GlobalPrice::firstOrCreate([], ['valor_dmc'=>0, 'valor_comedor'=>0, 'valor_lc'=>0]);

        return view('clients.index', compact('clients', 'prices'));
    }

    public function updateGlobalPrices(Request $request)
    {
        $request->validate([
            'valor_dmc'     => 'required|numeric|min:0',
            'valor_comedor' => 'required|numeric|min:0',
            'valor_lc'      => 'required|numeric|min:0',
        ]);

        $prices = GlobalPrice::first();
        $prices->update($request->all());

        return back()->with('success', '¡Precios globales actualizados!');
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string',
            'address' => 'nullable|string|max:255',
            'cuit' => 'nullable|string|max:20',
            
            // CUPOS DE SERVICIO (Para Balance / Facturación)
            'quota_dmc'         => 'nullable|integer|min:0',
            'quota_dmc_alt'     => 'nullable|integer|min:0',
            'quota_comedor'     => 'nullable|integer|min:0',
            'quota_comedor_alt' => 'nullable|integer|min:0',
            'quota_maternal'    => 'nullable|integer|min:0',
            'quota_listo'       => 'nullable|integer|min:0',

            // CUPOS OPERATIVOS (Para cálculo de ingredientes en cocina)
            // Se mantienen para saber cuánto mandar de base si el menú es genérico
            'cupo_jardin'     => 'nullable|integer|min:0',
            'cupo_primaria'   => 'nullable|integer|min:0',
            'cupo_secundaria' => 'nullable|integer|min:0',
        ]);

        Client::create($request->all());

        return redirect()->route('clients.index')->with('success', 'Escuela creada exitosamente.');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'cuit' => 'nullable|string|max:20',

            // Validación de Cupos
            'quota_dmc'         => 'nullable|integer|min:0',
            'quota_dmc_alt'     => 'nullable|integer|min:0',
            'quota_comedor'     => 'nullable|integer|min:0',
            'quota_comedor_alt' => 'nullable|integer|min:0',
            'quota_maternal'    => 'nullable|integer|min:0',
            'quota_listo'       => 'nullable|integer|min:0',

            'cupo_jardin'     => 'nullable|integer|min:0',
            'cupo_primaria'   => 'nullable|integer|min:0',
            'cupo_secundaria' => 'nullable|integer|min:0',
        ]);
        
        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Cupos actualizados correctamente.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success', 'Escuela eliminada.');
    }
}