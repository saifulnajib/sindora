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
            'organisasi' => $this->whenLoaded('organisasi', fn () => $this->organisasi ? [
                'id' => $this->organisasi->id,
                'nama' => $this->organisasi->nama,
                'singkatan' => $this->organisasi->singkatan,
            ] : null),
            'nama' => $this->nama,
            'kode' => $this->kode,
            'kategori' => $this->kategori,
            'kategori_label' => $this->kategori_label,
            'deskripsi' => $this->deskripsi,
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_url,
            'sk_nomor' => $this->sk_nomor,
            'sk_tgl_mulai' => $this->sk_tgl_mulai?->format('Y-m-d'),
            'sk_tgl_selesai' => $this->sk_tgl_selesai?->format('Y-m-d'),
            'sk_file_path' => $this->sk_file_path,
            'sk_file_url' => $this->sk_file_url,
            'sk_status' => $this->sk_status,
            'sk_status_label' => $this->sk_status_label,
            'sk_status_badge' => $this->sk_status_badge,
            'nama_ketua' => $this->nama_ketua,
            'kontak_pengurus' => $this->kontak_pengurus,
            'verification_status' => $this->verification_status instanceof \BackedEnum ? $this->verification_status->value : $this->verification_status,
            'can_update' => $request->user()?->can('update', $this->resource) ?? false,
            'can_delete' => $request->user()?->can('delete', $this->resource) ?? false,
            'klubs_count' => $this->whenCounted('klubs'),
            'atlets_count' => $this->whenCounted('atlets'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
