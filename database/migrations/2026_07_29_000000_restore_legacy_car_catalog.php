<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $catalog = json_decode(gzdecode(base64_decode(self::CATALOG)), true, 512, JSON_THROW_ON_ERROR);
        $now = now();

        foreach ($catalog as $makeName => $makeData) {
            $make = DB::table('car_makes')->where('name', $makeName)->first();

            if (!$make) {
                $makeId = DB::table('car_makes')->insertGetId([
                    'name' => $makeName,
                    'slug' => $this->uniqueMakeSlug($makeName),
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $makeId = $make->id;
            }

            foreach ($makeData['models'] as $modelIndex => $modelName) {
                $exists = DB::table('car_models')
                    ->where('car_make_id', $makeId)
                    ->where('name', $modelName)
                    ->exists();

                if (!$exists) {
                    DB::table('car_models')->insert([
                        'car_make_id' => $makeId,
                        'name' => $modelName,
                        'slug' => $this->uniqueModelSlug($makeId, $modelName),
                        'is_active' => true,
                        'sort_order' => $modelIndex,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // This migration deliberately preserves imported catalog records on rollback.
    }

    private function uniqueMakeSlug(string $name): string
    {
        return $this->uniqueSlug('car_makes', $name);
    }

    private function uniqueModelSlug(int $makeId, string $name): string
    {
        $base = Str::slug($name) ?: 'model';
        $slug = $base;
        $suffix = 2;

        while (DB::table('car_models')->where('car_make_id', $makeId)->where('slug', $slug)->exists()) {
            $slug = sprintf('%s-%d', $base, $suffix++);
        }

        return $slug;
    }

    private function uniqueSlug(string $table, string $name): string
    {
        $base = Str::slug($name) ?: 'make';
        $slug = $base;
        $suffix = 2;

        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = sprintf('%s-%d', $base, $suffix++);
        }

        return $slug;
    }

    private const CATALOG = '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';
};
