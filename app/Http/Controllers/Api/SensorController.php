<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PumpControlResource;
use App\Http\Resources\SensorReadingResource;
use App\Models\PumpControl;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SensorController extends Controller
{
    /**
     * ESP32 → Kirim data sensor ke server.
     *
     * @param  array{soil_moisture: int, soil_adc: int, is_raining: bool, pump_status: bool, recorded_at: string}  $validated
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'soil_moisture' => ['required', 'integer', 'min:0', 'max:100'],
            'soil_adc' => ['required', 'integer'],
            'is_raining' => ['required', 'boolean'],
            'pump_status' => ['required', 'boolean'],
            'recorded_at' => ['required', 'date'],
        ]);

        $reading = SensorReading::create($validated);

        return response()->json([
            'success' => true,
            'data' => new SensorReadingResource($reading),
        ], 201);
    }

    /**
     * Web → Ambil data sensor terbaru.
     */
    public function latest(): JsonResponse
    {
        $reading = SensorReading::latest()->first();

        if (! $reading) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data sensor.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new SensorReadingResource($reading),
        ]);
    }

    /**
     * Web → Ambil riwayat 50 data sensor terakhir.
     */
    public function history(Request $request): AnonymousResourceCollection
    {
        $query = SensorReading::query();

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        if ($request->filled('is_raining')) {
            $query->where('is_raining', filter_var($request->is_raining, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('pump_status')) {
            $query->where('pump_status', filter_var($request->pump_status, FILTER_VALIDATE_BOOLEAN));
        }

        // Pagination 20 baris per halaman
        $readings = $query->latest('recorded_at')->paginate(20);

        return SensorReadingResource::collection($readings);
    }

    /**
     * Web → Kirim perintah kontrol pompa.
     */
    public function sendPumpCommand(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'in:on,off,auto'],
        ]);

        // Batalkan semua perintah sebelumnya yang belum sempat dieksekusi ESP32
        PumpControl::where('is_executed', false)->update([
            'is_executed' => true,
            'executed_at' => now(),
        ]);

        $control = PumpControl::create([
            'command' => $validated['command'],
            'is_executed' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => new PumpControlResource($control),
        ], 201);
    }

    /**
     * ESP32 → Ambil perintah pompa yang belum dieksekusi.
     */
    public function pendingPumpCommand(): JsonResponse
    {
        $control = PumpControl::latestPending();

        if (! $control) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Tidak ada perintah pending.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new PumpControlResource($control),
        ]);
    }

    /**
     * ESP32 → Konfirmasi perintah pompa sudah dieksekusi.
     */
    public function markPumpCommandExecuted(PumpControl $pumpControl): JsonResponse
    {
        $pumpControl->update([
            'is_executed' => true,
            'executed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => new PumpControlResource($pumpControl),
        ]);
    }

    /**
     * Web → Ambil status koneksi ESP32 terakhir.
     */
    public function connectionStatus(): JsonResponse
    {
        $latestReading = SensorReading::latest()->first();

        if (! $latestReading) {
            return response()->json([
                'success' => true,
                'is_online' => false,
                'last_seen' => null,
                'seconds_ago' => null,
            ]);
        }

        $secondsAgo = now()->diffInSeconds($latestReading->created_at);
        $isOnline = $secondsAgo <= 10; // Online jika data terakhir < 10 detik lalu

        return response()->json([
            'success' => true,
            'is_online' => $isOnline,
            'last_seen' => $latestReading->created_at->toISOString(),
            'seconds_ago' => $secondsAgo,
        ]);
    }
}
