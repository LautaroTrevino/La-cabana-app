<?php

namespace App\Http\Controllers;

use App\Models\Remito;
use App\Models\RemitoDetail;
use App\Models\Client;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Menu; 

class RemitoController extends Controller
{
    // ... (index, create, store, etc. se mantienen igual)

    /**
     * GENERAR DESDE MENÚ
     * Calcula: (Cantidad Base x Cupo) y guarda el total.
     */
    public function storeMenu(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date'      => 'required|date',
            'menus'     => 'required|array|min:1', 
        ]);

        return DB::transaction(function () use ($request) {
            $client = Client::findOrFail($request->client_id);
            $numeroRemito = 'REM-MENU-' . time();

            $remito = Remito::create([
                'client_id'   => $request->client_id,
                'number'      => $numeroRemito,
                'date'        => $request->date,
                'tipo'        => 'remito', 
                'observation' => 'Generado desde Menú - Cantidades calculadas por cupo',
            ]);

            foreach ($request->menus as $menuId) {
                $menu = Menu::with('ingredients')->find($menuId); 

                if ($menu) {
                    foreach ($menu->ingredients as $ingredient) {
                        // Cálculo: Gramos/CC/Un base * Cupos de la escuela
                        $totalJardin    = ($ingredient->pivot->qty_jardin ?? 0) * ($client->cupo_jardin ?? 0);
                        $totalPrimaria  = ($ingredient->pivot->qty_primaria ?? 0) * ($client->cupo_primaria ?? 0);
                        $totalSecundaria = ($ingredient->pivot->qty_secundaria ?? 0) * ($client->cupo_secundaria ?? 0);

                        $cantidadCalculada = $totalJardin + $totalPrimaria + $totalSecundaria;

                        if ($cantidadCalculada > 0) {
                            $remito->details()->create([
                                'product_id'    => null, 
                                'ingredient_id' => $ingredient->id,
                                'quantity'      => $cantidadCalculada,
                                'measure_unit'  => $ingredient->pivot->measure_unit ?? 'grams'
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('remitos.index')->with('success', 'Remito generado con éxito.');
        });
    }

    /**
     * VISTA DEL REMITO (Conversión Visual)
     */
    public function show(Remito $remito)
    {
        $remito->load(['client', 'details.product', 'details.ingredient']);
        
        // Formateamos los detalles para que la vista reciba kilos/litros directamente
        $remito->details->transform(function ($detail) {
            return $this->convertUnits($detail);
        });

        return view('remitos.show', compact('remito'));
    }

    /**
     * PDF DEL REMITO (Conversión Visual)
     */
    public function print(Remito $remito)
    {
        $remito->load(['client', 'details.product', 'details.ingredient']);
        
        // Aplicamos la misma conversión para el PDF
        $remito->details->transform(function ($detail) {
            return $this->convertUnits($detail);
        });

        $pdf = Pdf::loadView('remitos.pdf', compact('remito'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream("Remito_{$remito->number}.pdf");
    }

    /**
     * FUNCIÓN AUXILIAR DE CONVERSIÓN
     * Transforma gramos a kilos y cc a litros solo para visualización
     */
    private function convertUnits($detail)
    {
        // Si no es un ingrediente (es un producto directo), no convertimos
        if (!$detail->ingredient_id) {
            $detail->display_quantity = $detail->quantity;
            $detail->display_unit = 'un.';
            return $detail;
        }

        $unit = $detail->measure_unit ?? 'grams';

        if ($unit === 'grams') {
            $detail->display_quantity = $detail->quantity / 1000;
            $detail->display_unit = 'kg.';
        } elseif ($unit === 'cc') {
            $detail->display_quantity = $detail->quantity / 1000;
            $detail->display_unit = 'lts.';
        } else {
            $detail->display_quantity = $detail->quantity;
            $detail->display_unit = 'un.';
        }

        return $detail;
    }
}