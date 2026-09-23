<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelurahanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'kode' => $this->kode,
            'kecamatan_id' => $this->kecamatan_id,
            'kecamatan' => $this->whenLoaded('kecamatan', fn () => $this->kecamatan ? ['id' => $this->kecamatan->id, 'nama' => $this->kecamatan->nama, 'kode' => $this->kecamatan->kode] : null),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
