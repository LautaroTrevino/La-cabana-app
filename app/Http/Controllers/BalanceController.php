<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\OrdenEntrega;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Configuración de fechas
        $fechaInicio = $request->date_start ? Carbon::parse($request->date_start) : Carbon::now()->startOfMonth();
        $fechaFin    = $request->date_end   ? Carbon::parse($request->date_end)   : Carbon::now()->endOfMonth();

        $balanceData = [];
        $clientes = Client::orderBy('name')->get();

        foreach ($clientes as $client) {
            
            $ingresosTotales = 0;
            $gastosTotales = 0;
            $cantidadServicios = 0;
            
            // 2. Buscamos las órdenes de entrega del periodo para esta escuela
            $ordenes = OrdenEntrega::with(['details.product'])
                ->where('client_id', $client->id)
                ->whereBetween('date', [$fechaInicio, $fechaFin])
                ->get();

            foreach ($ordenes as $orden) {
                
                // --- A. CALCULAR GASTOS (Mercadería real que salió) ---
                foreach ($orden->details as $detail) {
                    if ($detail->product) {
                        $costoProd = $detail->product->price_per_unit ?? 0; 
                        $gastosTotales += ($detail->quantity * $costoProd);
                    }
                }

                // --- B. CALCULAR INGRESOS (Día hábil: se suman todos los cupos activos) ---
                // Sumamos Comedor si tiene cupo
                if ($client->quota_comedor > 0) {
                    $ingresosTotales += ($client->quota_comedor * ($client->valor_comedor ?? 0));
                }

                // Sumamos DMC si tiene cupo
                if ($client->quota_dmc > 0) {
                    $ingresosTotales += ($client->quota_dmc * ($client->valor_dmc ?? 0));
                }

                // Sumamos Listo para Consumo si tiene cupo
                if ($client->quota_lcb > 0) {
                    $ingresosTotales += ($client->quota_lcb * ($client->valor_lc ?? 0));
                }

                // Sumamos Maternal u otros si existen en tu tabla de clientes
                if (isset($client->quota_maternal) && $client->quota_maternal > 0) {
                    $ingresosTotales += ($client->quota_maternal * ($client->valor_comedor ?? 0));
                }

                $cantidadServicios++; 
            }

            // 3. Solo agregamos al reporte si hubo movimiento (Orden de entrega generada)
            if ($ordenes->count() > 0) {
                $balanceData[] = [
                    'cliente'   => $client->name,
                    'servicios' => $cantidadServicios, // Representa los días de entrega
                    'ingresos'  => $ingresosTotales,
                    'gastos'    => $gastosTotales,
                    'balance'   => $ingresosTotales - $gastosTotales
                ];
            }
        }

        return view('balance.index', compact('balanceData', 'fechaInicio', 'fechaFin'));
    }
}