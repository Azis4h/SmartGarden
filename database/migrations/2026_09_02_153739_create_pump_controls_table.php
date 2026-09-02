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
        Schema::create('pump_controls', function (Blueprint $table) {
            $table->id();
            $table->enum('command', ['on', 'off', 'auto'])->default('auto')->comment('Perintah pompa dari web');
            $table->boolean('is_executed')->default(false)->comment('Sudah dieksekusi ESP32?');
            $table->timestamp('executed_at')->nullable()->comment('Waktu dieksekusi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pump_controls');
    }
};
