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
        Schema::table('news', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        $usedSlugs = [];

        DB::table('news')
            ->orderBy('id')
            ->select(['id', 'title'])
            ->get()
            ->each(function ($news) use (&$usedSlugs) {
                $baseSlug = Str::slug((string) $news->title);
                $baseSlug = $baseSlug !== '' ? $baseSlug : 'news';
                $slug = $baseSlug;
                $suffix = 2;

                while (in_array($slug, $usedSlugs, true)) {
                    $slug = sprintf('%s-%d', $baseSlug, $suffix);
                    $suffix++;
                }

                $usedSlugs[] = $slug;

                DB::table('news')
                    ->where('id', $news->id)
                    ->update(['slug' => $slug]);
            });

        Schema::table('news', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
