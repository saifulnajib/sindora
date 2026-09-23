<?php

namespace App\Http\Resources;

use App\Enums\KondisiSarpras;
use App\Enums\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SarprasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $kondisi = $this->kondisi;
        $kondisiValue = $kondisi instanceof \BackedEnum ? $kondisi->value : $kondisi;
        $kondisiLabel = null;
        $kondisiColor = null;
        $kondisiBadge = null;
        if ($kondisi instanceof \BackedEnum) {
            $kondisiLabel = method_exists($kondisi, 'label') ? $kondisi->label() : $kondisiValue;
            $kondisiColor = method_exists($kondisi, 'color') ? $kondisi->color() : null;
            $kondisiBadge = method_exists($kondisi, 'badgeClasses') ? $kondisi->badgeClasses() : null;
        } elseif (is_string($kondisiValue)) {
            $enum = KondisiSarpras::tryFrom($kondisiValue);
            if ($enum) {
                $kondisiLabel = $enum->label();
                $kondisiColor = $enum->color();
                $kondisiBadge = $enum->badgeClasses();
            } else {
                $kondisiLabel = ucfirst(str_replace('_', ' ', (string) $kondisiValue));
            }
        }

        $verification = $this->verification_status;
        $verificationValue = $verification instanceof \BackedEnum ? $verification->value : $verification;
        $verificationLabel = null;
        $verificationColor = null;
        if ($verification instanceof \BackedEnum) {
            $verificationLabel = method_exists($verification, 'label') ? $verification->label() : $verificationValue;
            $verificationColor = method_exists($verification, 'color') ? $verification->color() : null;
        } elseif (is_string($verificationValue)) {
            $enum = VerificationStatus::tryFrom($verificationValue);
            if ($enum) {
                $verificationLabel = $enum->label();
                $verificationColor = $enum->color();
            }
        }

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'jenis' => $this->jenis,
            'alamat' => $this->alamat,
            'kelurahan_id' => $this->kelurahan_id,
            'kelurahan' => $this->whenLoaded('kelurahan', fn () => $this->kelurahan ? ['id' => $this->kelurahan->id, 'nama' => $this->kelurahan->nama] : null),
            'kecamatan_id' => $this->kecamatan_id,
            'kecamatan' => $this->whenLoaded('kecamatan', fn () => $this->kecamatan ? ['id' => $this->kecamatan->id, 'nama' => $this->kecamatan->nama] : null),
            'klub_id' => $this->klub_id,
            'klub' => $this->whenLoaded('klub', fn () => $this->klub ? ['id' => $this->klub->id, 'nama' => $this->klub->nama] : null),
            'cabor_id' => $this->cabor_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? ['id' => $this->cabor->id, 'nama' => $this->cabor->nama, 'kode' => $this->cabor->kode] : null),
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'lat' => $this->latitude !== null ? (float) $this->latitude : null,
            'lng' => $this->longitude !== null ? (float) $this->longitude : null,
            'kondisi' => $kondisiValue,
            'kondisi_label' => $kondisiLabel,
            'kondisi_color' => $kondisiColor,
            'kondisi_badge' => $kondisiBadge,
            'kapasitas' => $this->kapasitas,
            'foto_path' => $this->foto_path,
            'foto_url' => $this->foto_path ? Storage::url($this->foto_path) : null,
            'deskripsi' => $this->deskripsi,
            'fasilitas' => $this->fasilitas,
            'verification_status' => $verificationValue,
            'verification_label' => $verificationLabel,
            'verification_color' => $verificationColor,
            'catatan_verifikator' => $this->catatan_verifikator,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verified_by' => $this->verified_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
