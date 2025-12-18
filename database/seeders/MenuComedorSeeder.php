<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Support\Str;

class MenuComedorSeeder extends Seeder
{
    public function run(): void
    {
        $cicloMenu = [
            'LUNES 1 - POLENTA CON LECHE Y SALSA' => [
                'POLENTA' => ['j' => 40, 'p' => 60, 's' => 80],
                'CEBOLLA' => ['j' => 20, 'p' => 40, 's' => 50],
                'ZANAHORIA' => ['j' => 30, 'p' => 40, 's' => 50],
                'TOMATE' => ['j' => 50, 'p' => 60, 's' => 70],
                'SAL FINA' => ['j' => 0.2, 'p' => 0.2, 's' => 0.2],
                'LECHE EN POLVO' => ['j' => 10, 'p' => 20, 's' => 30],
                'QUESO PASTA DURA' => ['j' => 10, 'p' => 20, 's' => 25],
                'ACEITE GIRASOL' => ['j' => 10, 'p' => 15, 's' => 15],
                'FRUTA' => ['j' => 150, 'p' => 200, 's' => 200],
            ],
            'MARTES 2 - CUADRADOS DE LENTEJAS Y ARROZ' => [
                'LENTEJAS' => ['j' => 25, 'p' => 30, 's' => 40],
                'ARROZ' => ['j' => 30, 'p' => 50, 's' => 60],
                'PAN RALLADO' => ['j' => 10, 'p' => 15, 's' => 20],
                'CEBOLLA' => ['j' => 15, 'p' => 20, 's' => 30],
                'ZANAHORIA' => ['j' => 8, 'p' => 10, 's' => 15],
                'HUEVO' => ['j' => 5, 'p' => 15, 's' => 20],
                'REMOLACHA/ TOMATE' => ['j' => 50, 'p' => 70, 's' => 80],
                'ACEITE' => ['j' => 10, 'p' => 15, 's' => 25],
                'SAL' => ['j' => 0.15, 'p' => 0.15, 's' => 0.2],
                'FRUTA' => ['j' => 0, 'p' => 0, 's' => 200],
            ],
            'MIERCOLES 3 - SALTEADO DE CERDO CON FIDEOS' => [
                'CERDO' => ['j' => 50, 'p' => 70, 's' => 100],
                'CEBOLLA' => ['j' => 20, 'p' => 30, 's' => 40],
                'ZAPALLITO' => ['j' => 20, 'p' => 40, 's' => 50],
                'ZANAHORIA' => ['j' => 20, 'p' => 40, 's' => 50],
                'ACEITE' => ['j' => 7, 'p' => 15, 's' => 20],
                'FIDEOS' => ['j' => 50, 'p' => 80, 's' => 100],
                'CONDIMENTOS' => ['j' => 0.1, 'p' => 0.3, 's' => 0.3],
                'SAL' => ['j' => 0.25, 'p' => 0.25, 's' => 0.36],
                'LECHE EN POLVO' => ['j' => 100, 'p' => 15, 's' => 150],
                'MAICENA' => ['j' => 10, 'p' => 15, 's' => 20],
                'AZUCAR' => ['j' => 5, 'p' => 10, 's' => 10],
            ],
            // ... Agregá acá el resto de los días que necesites
        ];

        foreach ($cicloMenu as $nombreMenu => $ingredientes) {
            $menu = Menu::firstOrCreate(['name' => $nombreMenu]);

            foreach ($ingredientes as $nombreProd => $porciones) {
                // Buscamos si el producto ya existe por nombre
                $producto = Product::where('name', $nombreProd)->first();

                if (!$producto) {
                    // Si no existe, generamos un código único sin colisiones
                    $baseCode = 'GEN-' . strtoupper(substr(str_replace(' ', '', $nombreProd), 0, 5));
                    $finalCode = $baseCode;
                    $counter = 1;

                    // Si el código existe, le sumamos un número (Ej: GEN-ACE1, GEN-ACE2)
                    while (Product::where('code', $finalCode)->exists()) {
                        $finalCode = $baseCode . $counter;
                        $counter++;
                    }

                    $producto = Product::create([
                        'name'  => $nombreProd,
                        'code'  => $finalCode,
                        'stock' => 0,
                    ]);
                }

                // Sincronizamos con la tabla intermedia
                $menu->products()->syncWithoutDetaching([
                    $producto->id => [
                        'qty_jardin'    => $porciones['j'],
                        'qty_primaria'  => $porciones['p'],
                        'qty_secundaria' => $porciones['s'],
                    ]
                ]);
            }
        }
    }
}