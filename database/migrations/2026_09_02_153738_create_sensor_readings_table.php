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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->integer('soil_moisture')->comment('Soil moisture 0-100%');
            $table->integer('soil_adc')->comment('Raw ADC value from sensor');
            $table->boolean('is_raining')->default(false)->comment('True = hujan');
            $table->boolean('pump_status')->default(false)->comment('True = pompa ON');
            $table->timestamp('recorded_at')->comment('Waktu dari RTC ESP32');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
