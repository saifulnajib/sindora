<?php

namespace App\Http\Resources;

use App\Enums\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KlubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $verification = $this->verification_status;
        $verificationValue = $verification instanceof \BackedEnum ? $verification->value : $verification;
        $verificationLabel = $verification instanceof \BackedEnum ? $verification->label() : (is_string($verification) ? $verification : null);
        $verificationColor = $verification instanceof \BackedEnum ? $verification->color() : null;

        // Fallback label/color via enum tryFrom if string
        if (is_string($verificationValue) && $verificationValue !== null) {
            try {
                $enum = VerificationStatus::tryFrom($verificationValue);
                if ($enum) {
                    $verificationLabel = $enum->label();
                    $verificationColor = $enum->color();
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'cabor_id' => $this->cabor_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? [
                'id' => $this->cabor->id,
                'nama' => $this->cabor->nama,
                'kode' => $this->cabor->kode,
            ] : null),
            'kelurahan_id' => $this->kelurahan_id,
            'kelurahan' => $this->whenLoaded('kelurahan', fn () => $this->kelurahan ? [
                'id' => $this->kelurahan->id,
                'nama' => $this->kelurahan->nama,
                'kecamatan_id' => $this->kelurahan->kecamatan_id,
            ] : null),
            'kecamatan_id' => $this->kecamatan_id,
            'kecamatan' => $this->whenLoaded('kecamatan', fn () => $this->kecamatan ? [
                'id' => $this->kecamatan->id,
                'nama' => $this->kecamatan->nama,
            ] : null),
            'alamat' => $this->alamat,
            'ketua' => $this->ketua,
            'kontak' => [
                'hp' => $this->kontak_hp,
                'email' => $this->kontak_email,
            ],
            'kontak_hp' => $this->kontak_hp,
            'kontak_email' => $this->kontak_email,
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
            'dokumen_legalitas_path' => $this->dokumen_legalitas_path,
            'dokumen_url' => $this->dokumen_legalitas_path ? Storage::url($this->dokumen_legalitas_path) : null,
            'jadwal_latihan' => $this->jadwal_latihan,
            'deskripsi' => $this->deskripsi,
            'nomor_sk' => $this->nomor_sk,
            'tanggal_sk' => $this->tanggal_sk?->format('Y-m-d'),
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'lat' => $this->latitude !== null ? (float) $this->latitude : null,
            'lng' => $this->longitude !== null ? (float) $this->longitude : null,
            'verification_status' => $verificationValue,
            'verification_label' => $verificationLabel,
            'verification_color' => $verificationColor,
            'catatan_verifikator' => $this->catatan_verifikator,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'atlets_count' => $this->whenCounted('atlets'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
