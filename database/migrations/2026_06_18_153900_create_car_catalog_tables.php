<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('name');
            $table->index(['is_active', 'sort_order', 'name']);
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_make_id')->constrained('car_makes')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['car_make_id', 'name']);
            $table->unique(['car_make_id', 'slug']);
            $table->index(['car_make_id', 'is_active', 'sort_order', 'name']);
        });

        $now = now();
        $catalog = [
            'Audi' => ['A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q3', 'Q5', 'Q7', 'Q8'],
            'BMW' => ['1 Series', '3 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5', 'X6'],
            'Chevrolet' => ['Aveo', 'Captiva', 'Cruze', 'Lacetti', 'Niva', 'Tahoe'],
            'Ford' => ['EcoSport', 'Explorer', 'Fiesta', 'Focus', 'Kuga', 'Mondeo', 'Mustang'],
            'Honda' => ['Accord', 'Civic', 'CR-V', 'Fit', 'HR-V', 'Pilot'],
            'Hyundai' => ['Accent', 'Creta', 'Elantra', 'Santa Fe', 'Solaris', 'Sonata', 'Tucson'],
            'Kia' => ['Ceed', 'Cerato', 'K5', 'Rio', 'Sorento', 'Soul', 'Sportage'],
            'Lada' => ['Granta', 'Kalina', 'Largus', 'Niva', 'Priora', 'Vesta', 'XRAY'],
            'Lexus' => ['ES', 'GX', 'IS', 'LX', 'NX', 'RX'],
            'Mazda' => ['3', '6', 'CX-3', 'CX-5', 'CX-7', 'CX-9'],
            'Mercedes-Benz' => ['A-Class', 'C-Class', 'E-Class', 'G-Class', 'GLA', 'GLC', 'GLE', 'S-Class'],
            'Mitsubishi' => ['ASX', 'L200', 'Lancer', 'Outlander', 'Pajero', 'Pajero Sport'],
            'Nissan' => ['Almera', 'Juke', 'Murano', 'Pathfinder', 'Qashqai', 'Teana', 'X-Trail'],
            'Renault' => ['Arkana', 'Duster', 'Kaptur', 'Logan', 'Megane', 'Sandero'],
            'Skoda' => ['Fabia', 'Karoq', 'Kodiaq', 'Octavia', 'Rapid', 'Superb', 'Yeti'],
            'Toyota' => ['Camry', 'Corolla', 'Highlander', 'Land Cruiser', 'Prado', 'Prius', 'RAV4'],
            'Volkswagen' => ['Golf', 'Jetta', 'Passat', 'Polo', 'Taos', 'Teramont', 'Tiguan', 'Touareg'],
            'Volvo' => ['S60', 'S80', 'S90', 'V60', 'XC40', 'XC60', 'XC90'],
        ];

        foreach ($catalog as $makeIndex => $models) {
            $makeId = DB::table('car_makes')->insertGetId([
                'name' => $makeIndex,
                'slug' => Str::slug($makeIndex),
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($models as $modelIndex => $model) {
                DB::table('car_models')->insert([
                    'car_make_id' => $makeId,
                    'name' => $model,
                    'slug' => Str::slug($model),
                    'is_active' => true,
                    'sort_order' => $modelIndex,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('car_makes');
    }
};
