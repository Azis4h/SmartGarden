<?php

use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SmartGarden IoT API Routes
|--------------------------------------------------------------------------
|
| Endpoint untuk komunikasi dua arah antara ESP32 dan web dashboard.
| Tidak menggunakan auth middleware agar mudah diakses ESP32.
| Tambahkan API key middleware untuk production jika diperlukan.
|
*/

Route::prefix('sensor')->group(function () {
    // ESP32 → Kirim data sensor setiap 2 detik
    Route::post('/data', [SensorController::class, 'store'])->name('api.sensor.store');

    // Web → Ambil data sensor terbaru
    Route::get('/latest', [SensorController::class, 'latest'])->name('api.sensor.latest');

    // Web → Ambil riwayat 50 data terakhir
    Route::get('/history', [SensorController::class, 'history'])->name('api.sensor.history');

    // Web → Status koneksi ESP32
    Route::get('/status', [SensorController::class, 'connectionStatus'])->name('api.sensor.status');
});

Route::prefix('pump')->group(function () {
    // Web → Kirim perintah kontrol pompa
    Route::post('/command', [SensorController::class, 'sendPumpCommand'])->name('api.pump.command');

    // ESP32 → Polling perintah pending
    Route::get('/command/pending', [SensorController::class, 'pendingPumpCommand'])->name('api.pump.pending');

    // ESP32 → Konfirmasi perintah sudah dieksekusi
    Route::put('/command/{pumpControl}/executed', [SensorController::class, 'markPumpCommandExecuted'])->name('api.pump.executed');
});
