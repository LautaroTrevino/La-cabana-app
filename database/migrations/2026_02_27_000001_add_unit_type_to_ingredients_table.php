<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  // ← esta línea es la que faltaba

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (! Schema::hasColumn('ingredients', 'unit_type')) {
                $table->string('unit_type')->nullable()->after('description');
            }
        });

        if (Schema::hasColumn('ingredients', 'unit')) {
            DB::table('ingredients')->get()->each(function ($row) {
                $normalizado = match (strtolower(trim($row->unit ?? ''))) {
                    'kg', 'gr', 'grams', 'g' => 'grams',
                    'lt', 'cc', 'ml'          => 'cc',
                    'un', 'units', 'u'        => 'units',
                    default                   => 'grams',
                };
                DB::table('ingredients')
                    ->where('id', $row->id)
                    ->update(['unit_type' => $normalizado]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'unit_type')) {
                $table->dropColumn('unit_type');
            }
        });
    }
};