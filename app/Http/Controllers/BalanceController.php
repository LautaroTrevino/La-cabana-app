<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\OrdenEntrega;
use App\Models\GlobalPrice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Rango de fechas
        $fechaInicio = $request->filled('date_start')
            ? Carbon::parse($request->date_start)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fechaFin = $request->filled('date_end')
            ? Carbon::parse($request->date_end)->endOfDay()
            : Carbon::now()->endOfMonth();

        // 2. Precios globales
        $precios = GlobalPrice::firstOrCreate([], [
            'valor_comedor'     => 0,
            'valor_comedor_alt' => 0,
            'valor_dmc'         => 0,
            'valor_dmc_alt'     => 0,
            'valor_lc'          => 0,
            'valor_maternal'    => 0,
        ]);

        $balanceData          = [];
        $clientes             = Client::orderBy('name')->get();
        $totalIngresosPeriodo = 0;
        $totalGastosPeriodo   = 0;

        foreach ($clientes as $client) {

            $gastosTotales = 0;

            $ordenes = OrdenEntrega::with(['details.product'])
                ->where('client_id', $client->id)
                ->whereBetween('date', [$fechaInicio, $fechaFin])
                ->get();

            $diasConServicio = $ordenes->count();

            if ($diasConServicio === 0) {
                continue;
            }

            // ── A. GASTOS: costo real acumulado de todas las órdenes ──
            foreach ($ordenes as $orden) {
                foreach ($orden->details as $detail) {
                    if ($detail->product) {
                        $gastosTotales += $detail->quantity * (float) ($detail->product->price_per_unit ?? 0);
                    }
                }
            }

            // ── B. INGRESOS: cupo × precio × días con servicio ────────
            //
            // BUG FIX: en el código original los ingresos se sumaban
            // DENTRO del foreach de órdenes, multiplicando el ingreso diario
            // por cada orden del día. Con 3 órdenes en un día, los ingresos
            // se triplicaban. La lógica correcta es:
            //   ingreso_por_dia × cantidad_de_días_con_servicio
            //
            $ingresoPorDia = 0;

            if (($client->quota_comedor ?? 0) > 0) {
                $ingresoPorDia += $client->quota_comedor * (float) $precios->valor_comedor;
            }
            if (($client->quota_comedor_alt ?? 0) > 0) {
                $ingresoPorDia += $client->quota_comedor_alt * (float) $precios->valor_comedor_alt;
            }
            if (($client->quota_dmc ?? 0) > 0) {
                $ingresoPorDia += $client->quota_dmc * (float) $precios->valor_dmc;
            }
            if (($client->quota_dmc_alt ?? 0) > 0) {
                $ingresoPorDia += $client->quota_dmc_alt * (float) $precios->valor_dmc_alt;
            }
            if (($client->quota_lcb ?? 0) > 0) {
                $ingresoPorDia += $client->quota_lcb * (float) $precios->valor_lc;
            }
            if (($client->quota_maternal ?? 0) > 0) {
                $ingresoPorDia += $client->quota_maternal * (float) $precios->valor_maternal;
            }

            $ingresosTotales = $ingresoPorDia * $diasConServicio;

            $balanceData[] = [
                'cliente'   => $client->name,
                'nivel'     => $client->level ?? '—',
                'servicios' => $diasConServicio,
                'ingresos'  => $ingresosTotales,
                'gastos'    => $gastosTotales,
                'balance'   => $ingresosTotales - $gastosTotales,
            ];

            $totalIngresosPeriodo += $ingresosTotales;
            $totalGastosPeriodo   += $gastosTotales;
        }

        return view('balance.index', compact(
            'balanceData',
            'precios',
            'fechaInicio',
            'fechaFin',
            'totalIngresosPeriodo',
            'totalGastosPeriodo'
        ));
    }

    // ── Actualizar precios globales ──────────────────────────────
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

        // FIX: updateOrCreate por si aún no existe el registro
        $prices = GlobalPrice::firstOrCreate([]);
        $prices->update($request->only([
            'valor_comedor', 'valor_comedor_alt',
            'valor_dmc', 'valor_dmc_alt',
            'valor_lc', 'valor_maternal',
        ]));

        return back()->with('success', 'Valores de cupos actualizados correctamente.');
    }
}
