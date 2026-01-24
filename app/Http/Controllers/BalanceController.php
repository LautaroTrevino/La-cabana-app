<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\OrdenEntrega;
use App\Models\GlobalPrice; // <--- IMPORTANTE: Usamos precios globales
use Illuminate\Http\Request;
use Carbon\Carbon;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Configuración de fechas
        $fechaInicio = $request->date_start ? Carbon::parse($request->date_start) : Carbon::now()->startOfMonth();
        $fechaFin    = $request->date_end   ? Carbon::parse($request->date_end)   : Carbon::now()->endOfMonth();

        // 2. Obtener Precios Globales (Si no existen, crea todo en 0)
        $precios = GlobalPrice::firstOrCreate([], [
            'valor_comedor'     => 0,
            'valor_comedor_alt' => 0,
            'valor_dmc'         => 0,
            'valor_dmc_alt'     => 0,
            'valor_lc'          => 0,
            'valor_maternal'    => 0,
        ]);

        $balanceData = [];
        $clientes = Client::orderBy('name')->get();

        // Variables para los totales generales (Encabezado del reporte)
        $totalIngresosPeriodo = 0;
        $totalGastosPeriodo = 0;

        foreach ($clientes as $client) {
            
            $ingresosTotales = 0;
            $gastosTotales = 0;
            $diasConServicio = 0;
            
            // 3. Buscamos las órdenes de entrega del periodo para esta escuela
            $ordenes = OrdenEntrega::with(['details.product'])
                ->where('client_id', $client->id)
                ->whereBetween('date', [$fechaInicio, $fechaFin])
                ->get();

            foreach ($ordenes as $orden) {
                
                // --- A. CALCULAR GASTOS (Mercadería real que salió del depósito) ---
                foreach ($orden->details as $detail) {
                    if ($detail->product) {
                        $costoProd = $detail->product->price_per_unit ?? 0; 
                        $gastosTotales += ($detail->quantity * $costoProd);
                    }
                }

                // --- B. CALCULAR INGRESOS (Teóricos: Cupo Escuela * Precio Global) ---
                // Se asume que si hubo entrega, se facturan todos los servicios activos.

                // 1. Comedor
                if ($client->quota_comedor > 0) 
                    $ingresosTotales += ($client->quota_comedor * $precios->valor_comedor);
                
                if ($client->quota_comedor_alt > 0) 
                    $ingresosTotales += ($client->quota_comedor_alt * $precios->valor_comedor_alt);

                // 2. DMC
                if ($client->quota_dmc > 0) 
                    $ingresosTotales += ($client->quota_dmc * $precios->valor_dmc);
                
                if ($client->quota_dmc_alt > 0) 
                    $ingresosTotales += ($client->quota_dmc_alt * $precios->valor_dmc_alt);

                // 3. Otros
                if ($client->quota_lcb > 0) 
                    $ingresosTotales += ($client->quota_lcb * $precios->valor_lc);

                if ($client->quota_maternal > 0) 
                    $ingresosTotales += ($client->quota_maternal * $precios->valor_maternal);

                $diasConServicio++; 
            }

            // 4. Solo agregamos al reporte si hubo movimiento
            if ($diasConServicio > 0) {
                $balanceData[] = [
                    'cliente'   => $client->name,
                    'servicios' => $diasConServicio, // Días trabajados
                    'ingresos'  => $ingresosTotales,
                    'gastos'    => $gastosTotales,
                    'balance'   => $ingresosTotales - $gastosTotales
                ];

                // Sumamos a los totales generales
                $totalIngresosPeriodo += $ingresosTotales;
                $totalGastosPeriodo += $gastosTotales;
            }
        }

        // Enviamos TODAS las variables necesarias a la vista
        return view('balance.index', compact(
            'balanceData', 
            'precios', 
            'fechaInicio', 
            'fechaFin',
            'totalIngresosPeriodo',
            'totalGastosPeriodo'
        ));
    }

    // --- C. MÉTODO PARA ACTUALIZAR PRECIOS GLOBALES ---
    public function updatePrices(Request $request)
    {
        $request->validate([
            'valor_comedor'     => 'required|numeric|min:0',
            'valor_comedor_alt' => 'required|numeric|min:0',
            'valor_dmc'         => 'required|numeric|min:0',
            'valor_dmc_alt'     => 'required|numeric|min:0',
            'valor_lc'          => 'required|numeric|min:0',
            'valor_maternal'    => 'required|numeric|min:0',
        ]);

        $prices = GlobalPrice::first();
        $prices->update($request->all());

        return back()->with('success', 'Valores de cupos actualizados correctamente.');
    }
}