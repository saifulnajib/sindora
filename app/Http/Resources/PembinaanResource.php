<?php

namespace App\Http\Resources;

use App\Enums\VerificationStatus;
use App\Models\Atlet;
use App\Models\Klub;
use App\Models\Sdm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PembinaanResource extends JsonResource
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

        // Anggaran formatted as Rp
        $anggaran = $this->anggaran;
        $anggaranFormatted = null;
        if ($anggaran !== null) {
            $anggaranFormatted = 'Rp '.number_format((float) $anggaran, 0, ',', '.');
        }

        // Periode human readable
        $periode = null;
        if ($this->periode_mulai && $this->periode_selesai) {
            $periode = $this->periode_mulai->format('d M Y').' - '.$this->periode_selesai->format('d M Y');
        } elseif ($this->periode_mulai) {
            $periode = $this->periode_mulai->format('d M Y');
        } elseif ($this->periode_selesai) {
            $periode = $this->periode_selesai->format('d M Y');
        }

        // Peserta collection when loaded (polymorphic)
        $peserta = null;
        if ($this->relationLoaded('peserta')) {
            $peserta = $this->peserta->map(function ($row) {
                $type = $row->peserta_type;
                $shortType = class_basename($type);
                $nama = null;
                if ($row->relationLoaded('peserta') && $row->peserta) {
                    $nama = $row->peserta->nama ?? $row->peserta->name ?? null;
                }

                return [
                    'id' => $row->id,
                    'peserta_type' => $type,
                    'peserta_type_short' => $shortType,
                    'peserta_id' => $row->peserta_id,
                    'peran' => $row->peran,
                    'catatan' => $row->catatan,
                    'nama' => $nama,
                    'peserta' => $row->relationLoaded('peserta') && $row->peserta ? [
                        'id' => $row->peserta->id,
                        'nama' => $row->peserta->nama ?? $row->peserta->name ?? null,
                    ] : null,
                ];
            })->values();
        }

        // whenCounted peserta counts via withCount aliases
        $atletsCount = $this->whenCounted('atlets');
        $klubsCount = $this->whenCounted('klubs');
        $sdmsCount = $this->whenCounted('sdms');

        // Also fallback to peserta relation counts if counted via different names
        // but we will use explicit whenCounted

        return [
            'id' => $this->id,
            'nama_program' => $this->nama_program,
            'deskripsi' => $this->deskripsi,
            'target' => $this->target,
            'anggaran' => $anggaran !== null ? (string) $anggaran : null,
            'anggaran_raw' => $anggaran !== null ? (float) $anggaran : null,
            'anggaran_formatted' => $anggaranFormatted,
            'sumber_anggaran' => $this->sumber_anggaran,
            'tahun_anggaran' => $this->tahun_anggaran !== null ? (int) $this->tahun_anggaran : null,
            'periode_mulai' => $this->periode_mulai?->format('Y-m-d'),
            'periode_selesai' => $this->periode_selesai?->format('Y-m-d'),
            'periode' => $periode,
            'evaluasi' => $this->evaluasi,
            'status' => $this->status,
            'verification_status' => $verificationValue,
            'verification_label' => $verificationLabel,
            'verification_color' => $verificationColor,
            'verification_badge' => $verificationBadge,
            'catatan_verifikator' => $this->catatan_verifikator,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verified_by' => $this->verified_by,
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
            'peserta_counts' => [
                'atlets_count' => $atletsCount,
                'klubs_count' => $klubsCount,
                'sdms_count' => $sdmsCount,
            ],
            // Keep also flat keys for backward compat
            'atlets_count' => $atletsCount,
            'klubs_count' => $klubsCount,
            'sdms_count' => $sdmsCount,
            'peserta' => $peserta,
            // For Form pre-select convenience: ids grouped
            'peserta_atlet_ids' => $this->whenLoaded('peserta', function () {
                return $this->peserta->where('peserta_type', Atlet::class)->pluck('peserta_id')->values();
            }),
            'peserta_klub_ids' => $this->whenLoaded('peserta', function () {
                return $this->peserta->where('peserta_type', Klub::class)->pluck('peserta_id')->values();
            }),
            'peserta_sdm_ids' => $this->whenLoaded('peserta', function () {
                return $this->peserta->where('peserta_type', Sdm::class)->pluck('peserta_id')->values();
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
