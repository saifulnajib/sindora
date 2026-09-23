<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'singkatan' => $this->singkatan,
            'jenis' => $this->jenis,
            'alamat' => $this->alamat,
            'ketua' => $this->ketua,
            'kontak_hp' => $this->kontak_hp,
            'kontak_email' => $this->kontak_email,
            'deskripsi' => $this->deskripsi,
            'logo_path' => $this->logo_path,
            'verification_status' => $this->verification_status instanceof \BackedEnum ? $this->verification_status->value : $this->verification_status,
            'cabors_count' => $this->whenCounted('cabors'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
