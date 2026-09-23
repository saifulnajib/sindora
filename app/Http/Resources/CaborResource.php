<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaborResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisasi_id' => $this->organisasi_id,
            'organisasi' => $this->whenLoaded('organisasi', fn () => $this->organisasi ? ['id' => $this->organisasi->id, 'nama' => $this->organisasi->nama, 'singkatan' => $this->organisasi->singkatan] : null),
            'nama' => $this->nama,
            'kode' => $this->kode,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'logo_path' => $this->logo_path,
            'verification_status' => $this->verification_status instanceof \BackedEnum ? $this->verification_status->value : $this->verification_status,
            'klubs_count' => $this->whenCounted('klubs'),
            'atlets_count' => $this->whenCounted('atlets'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
