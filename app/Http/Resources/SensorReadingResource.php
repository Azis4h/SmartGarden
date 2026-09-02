<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorReadingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'soil_moisture' => $this->soil_moisture,
            'soil_adc' => $this->soil_adc,
            'is_raining' => $this->is_raining,
            'rain_label' => $this->is_raining ? 'Hujan' : 'Tidak Hujan',
            'pump_status' => $this->pump_status,
            'pump_label' => $this->pump_status ? 'ON' : 'OFF',
            'recorded_at' => $this->recorded_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
