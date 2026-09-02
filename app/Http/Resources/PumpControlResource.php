<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PumpControlResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'command' => $this->command,
            'is_executed' => $this->is_executed,
            'executed_at' => $this->executed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
