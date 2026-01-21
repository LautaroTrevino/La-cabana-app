<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\OrdenEntrega; // <--- CAMBIO: Usamos OrdenEntrega (Real), no Remito (Papel)
use App\Models\GlobalPrice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. PRECIOS GLOBALES (Valor por cupo)
        $precios = GlobalPrice::firstOrCreate([], ['valor_dmc'=>0, 'valor_comedor'=>0, 'valor_lc'=>0]);

        // 2. FILTROS DE FECHA
        $fechaInicio = $request->input('date_start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin    = $request->input('date_end', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $clients = Client::orderBy('name')->get();
        $balanceData = [];

        foreach ($clients as $client) {
            
            $ingresoTotal = 0;
            $gastoTotal = 0;
            $cantidadServicios = 0;

            // 3. BUSCAMOS LAS ÓRDENES DE ENTREGA (REALES)
            // Traemos las órdenes junto con sus detalles (ingredientes/productos) para calcular costos
            $ordenes = OrdenEntrega::with(['details.ingredient', 'details.product'])
                                   ->where('client_id', $client->id)
                                   ->whereBetween('date', [$fechaInicio, $fechaFin])
                                   ->get();

            foreach ($ordenes as $orden) {
                // --- A. CÁLCULO DE INGRESOS (Según Tipo de Menú y Cupo de Escuela) ---
                // Solo si la orden tiene un tipo de menú definido (Comedor, DMC, etc.)
                if ($orden->menu_type) {
                    $cantidadServicios++;
                    $tipoMenu = $orden->menu_type;
                    $ingresoOrden = 0;

                    // Lógica: Cupo de la Escuela * Precio Global
                    if (in_array($tipoMenu, ['Comedor', 'Comedor Alternativo'])) {
                        $cupo = $client->quota_comedor + $client->quota_comedor_alt; 
                        $ingresoOrden = $cupo * $precios->valor_comedor;
                    } 
                    elseif (in_array($tipoMenu, ['DMC', 'DMC Alternativo'])) {
                        $cupo = $client->quota_dmc + $client->quota_dmc_alt;
                        $ingresoOrden = $cupo * $precios->valor_dmc;
                    }
                    elseif ($tipoMenu == 'Maternal') {
                        $cupo = $client->quota_maternal;
                        $ingresoOrden = $cupo * $precios->valor_dmc; // Asumiendo valor DMC
                    }
                    elseif ($tipoMenu == 'Listo Consumo') {
                        $cupo = $client->quota_listo;
                        $ingresoOrden = $cupo * $precios->valor_lc;
                    }

                    $ingresoTotal += $ingresoOrden;
                }

                // --- B. CÁLCULO DE GASTOS (Costo de Mercadería Real Entregada) ---
                foreach ($orden->details as $detail) {
                    $cantidad = $detail->quantity;
                    $costoUnitario = 0;

                    if ($detail->ingredient) {
                        $costoUnitario = $detail->ingredient->cost ?? 0; 
                    } elseif ($detail->product) {
                        $costoUnitario = $detail->product->cost ?? 0; 
                    }
                    
                    $gastoTotal += ($cantidad * $costoUnitario);
                }
            }

            // --- C. RESULTADO FINAL ---
            $balanceData[] = [
                'cliente'   => $client->name,
                'servicios' => $cantidadServicios,
                'ingresos'  => $ingresoTotal,
                'gastos'    => $gastoTotal,
                'balance'   => $ingresoTotal - $gastoTotal
            ];
        }

        return view('balance.index', compact('balanceData', 'precios', 'fechaInicio', 'fechaFin'));
    }
}