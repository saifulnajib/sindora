<?php

namespace App\Http\Resources;

use App\Enums\Medali;
use App\Enums\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PrestasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $medali = $this->medali;
        $medaliValue = $medali instanceof \BackedEnum ? $medali->value : $medali;
        $medaliLabel = null;
        $medaliColor = null;
        $medaliBadge = null;

        if ($medali instanceof Medali) {
            $medaliLabel = $medali->label();
            $medaliColor = $medali->color();
            $medaliBadge = $medali->badgeClasses();
        } elseif (is_string($medaliValue)) {
            $enum = Medali::tryFrom($medaliValue);
            if ($enum) {
                $medaliLabel = $enum->label();
                $medaliColor = $enum->color();
                $medaliBadge = $enum->badgeClasses();
            } else {
                $medaliLabel = $medaliValue ? ucfirst(str_replace('_', ' ', $medaliValue)) : null;
            }
        }

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
            }
        }

        return [
            'id' => $this->id,
            'atlet_id' => $this->atlet_id,
            'atlet' => $this->whenLoaded('atlet', fn () => $this->atlet ? [
                'id' => $this->atlet->id,
                'nama' => $this->atlet->nama,
            ] : null),
            'cabor_id' => $this->cabor_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? [
                'id' => $this->cabor->id,
                'nama' => $this->cabor->nama,
                'kode' => $this->cabor->kode,
            ] : null),
            'kejuaraan_id' => $this->kejuaraan_id,
            'kejuaraan' => $this->whenLoaded('kejuaraan', fn () => $this->kejuaraan ? [
                'id' => $this->kejuaraan->id,
                'nama' => $this->kejuaraan->nama,
                'tingkat' => $this->kejuaraan->tingkat,
            ] : null),
            'medali' => $medaliValue,
            'medali_label' => $medaliLabel,
            'medali_color' => $medaliColor,
            'medali_badge' => $medaliBadge,
            'peringkat' => $this->peringkat,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'kategori_kelas' => $this->kategori_kelas,
            'sertifikat_path' => $this->sertifikat_path,
            'sertifikat_url' => $this->sertifikat_path ? Storage::url($this->sertifikat_path) : null,
            'nomor_sertifikat' => $this->nomor_sertifikat,
            'verification_status' => $verificationValue,
            'verification_label' => $verificationLabel,
            'verification_color' => $verificationColor,
            'verification_badge' => $verificationBadge,
            'keterangan' => $this->keterangan,
            'catatan_verifikator' => $this->catatan_verifikator,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verified_by' => $this->verified_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
