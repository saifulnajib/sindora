<?php

namespace App\Http\Resources;

use App\Enums\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KejuaraanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $verification = $this->verification_status;
        $verificationValue = $verification instanceof \BackedEnum ? $verification->value : $verification;
        $verificationLabel = null;
        $verificationColor = null;
        $verificationBadge = null;

        if ($verification instanceof VerificationStatus) {
            $verificationLabel = $verification->label();
            $verificationColor = $verification->color();
            $verificationBadge = $verification->badgeClasses();
        } elseif (is_string($verificationValue)) {
            $enum = VerificationStatus::tryFrom($verificationValue);
            if ($enum) {
                $verificationLabel = $enum->label();
                $verificationColor = $enum->color();
                $verificationBadge = $enum->badgeClasses();
            } else {
                $verificationLabel = $verificationValue;
            }
        }

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'jenis' => $this->jenis,
            'tingkat' => $this->tingkat,
            'penyelenggara' => $this->penyelenggara,
            'organisasi_id' => $this->organisasi_id,
            'organisasi' => $this->whenLoaded('organisasi', fn () => $this->organisasi ? [
                'id' => $this->organisasi->id,
                'nama' => $this->organisasi->nama,
            ] : null),
            'cabor_id' => $this->cabor_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? [
                'id' => $this->cabor->id,
                'nama' => $this->cabor->nama,
                'kode' => $this->cabor->kode,
            ] : null),
            'lokasi' => $this->lokasi,
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $this->tanggal_selesai?->format('Y-m-d'),
            'tanggal_range' => $this->tanggal_mulai && $this->tanggal_selesai
                ? $this->tanggal_mulai->format('d M Y').' - '.$this->tanggal_selesai->format('d M Y')
                : ($this->tanggal_mulai?->format('d M Y') ?? null),
            'deskripsi' => $this->deskripsi,
            'poster_path' => $this->poster_path,
            'poster_url' => $this->poster_path ? Storage::url($this->poster_path) : null,
            'verification_status' => $verificationValue,
            'verification_label' => $verificationLabel,
            'verification_color' => $verificationColor,
            'verification_badge' => $verificationBadge,
            'prestasis_count' => $this->whenCounted('prestasis'),
            'catatan_verifikator' => $this->catatan_verifikator,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verified_by' => $this->verified_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
