<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->text('description');
            $table->unsignedInteger('year');
            $table->string('color');
            $table->string('wheel_position');
            $table->string('condition');
            $table->string('transmission');
            $table->string('drive_type');
            $table->string('body_type');
            $table->unsignedInteger('mileage_amount');
            $table->string('mileage_unit');
            $table->float('engine_capacity')->nullable()->default(null);
            $table->string('fuel_type');
            $table->unsignedInteger('power');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_details');
    }
};
