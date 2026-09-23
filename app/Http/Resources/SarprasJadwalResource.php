<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SarprasJadwalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sarpras_id' => $this->sarpras_id,
            'sarpras' => $this->whenLoaded('sarpras', fn () => $this->sarpras ? [
                'id' => $this->sarpras->id,
                'nama' => $this->sarpras->nama,
                'jenis' => $this->sarpras->jenis,
            ] : null),
            'klub_id' => $this->klub_id,
            'klub' => $this->whenLoaded('klub', fn () => $this->klub ? [
                'id' => $this->klub->id,
                'nama' => $this->klub->nama,
            ] : null),
            'hari' => $this->hari,
            'tanggal' => $this->tanggal ? $this->tanggal->format('Y-m-d') : null,
            'tanggal_formatted' => $this->tanggal ? $this->tanggal->format('d M Y') : null,
            'jam_mulai' => $this->jam_mulai ? (is_string($this->jam_mulai) ? substr($this->jam_mulai, 0, 5) : $this->jam_mulai->format('H:i')) : null,
            'jam_selesai' => $this->jam_selesai ? (is_string($this->jam_selesai) ? substr($this->jam_selesai, 0, 5) : $this->jam_selesai->format('H:i')) : null,
            'kegiatan' => $this->kegiatan,
            // alias for legacy 'keperluan' field name
            'keperluan' => $this->kegiatan,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
