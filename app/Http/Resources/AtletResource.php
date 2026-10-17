<?php

namespace App\Http\Resources;

use App\Enums\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AtletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Masking per role (0.7):
     * - nik: show only last 4 digits, mask rest with * (e.g. ************1234)
     * - no_hp: mask like 08**-****-1234 (keep first 2 and last 4)
     * - alamat: like "Jl. ***" (keep first 3 chars + " ***", or " ***" if short)
     * Admin + operator_klub pemilik data boleh lihat full, else masked via Gate::denies('view-sensitive')
     */
    public function toArray(Request $request): array
    {
        $canViewSensitive = Gate::allows('view-sensitive', $this->resource);

        $verification = $this->verification_status;
        $verificationValue = $verification instanceof \BackedEnum ? $verification->value : $verification;
        $verificationLabel = null;
        $verificationColor = null;
        if ($verification instanceof \BackedEnum) {
            $verificationLabel = $verification->label();
            $verificationColor = $verification->color();
        } elseif (is_string($verificationValue)) {
            $enum = VerificationStatus::tryFrom($verificationValue);
            if ($enum) {
                $verificationLabel = $enum->label();
                $verificationColor = $enum->color();
            }
        }

        $statusPembinaan = $this->status_pembinaan;
        $statusPembinaanValue = $statusPembinaan instanceof \BackedEnum ? $statusPembinaan->value : $statusPembinaan;

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nik' => $canViewSensitive ? $this->nik : self::maskNik($this->nik),
            'nik_masked' => self::maskNik($this->nik),
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $canViewSensitive ? $this->alamat : self::maskAlamat($this->alamat),
            'alamat_masked' => self::maskAlamat($this->alamat),
            'no_hp' => $canViewSensitive ? $this->no_hp : self::maskHp($this->no_hp),
            'no_hp_masked' => self::maskHp($this->no_hp),
            'email' => $this->email,
            'klub_id' => $this->klub_id,
            'klub' => $this->whenLoaded('klub', fn () => $this->klub ? ['id' => $this->klub->id, 'nama' => $this->klub->nama] : null),
            'cabor_id' => $this->cabor_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? ['id' => $this->cabor->id, 'nama' => $this->cabor->nama, 'kode' => $this->cabor->kode] : null),
            'cabors' => $this->formatCabors($statusPembinaanValue),
            'cabor_names' => $this->formatCaborNames($statusPembinaanValue),
            'pelatih_id' => $this->pelatih_id,
            'pelatih' => $this->whenLoaded('pelatih', fn () => $this->pelatih ? ['id' => $this->pelatih->id, 'nama' => $this->pelatih->nama] : null),
            'kelurahan_id' => $this->kelurahan_id,
            'kelurahan' => $this->whenLoaded('kelurahan', fn () => $this->kelurahan ? ['id' => $this->kelurahan->id, 'nama' => $this->kelurahan->nama] : null),
            'kelas_tanding' => $this->kelas_tanding,
            'status_pembinaan' => $statusPembinaanValue,
            'status_pembinaan_label' => $statusPembinaan instanceof \BackedEnum ? $statusPembinaan->label() : $statusPembinaanValue,
            'berat_badan' => $this->berat_badan,
            'tinggi_badan' => $this->tinggi_badan,
            'foto_path' => $this->foto_path,
            'foto_url' => $this->foto_path ? Storage::url($this->foto_path) : null,
            'verification_status' => $verificationValue,
            'verification_label' => $verificationLabel,
            'verification_color' => $verificationColor,
            'catatan_verifikator' => $this->catatan_verifikator,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verified_by' => $this->verified_by,
            'can_view_sensitive' => $canViewSensitive,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    public static function maskNik(?string $nik): ?string
    {
        if (blank($nik)) {
            return $nik;
        }
        $nik = (string) $nik;
        $len = strlen($nik);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 4).substr($nik, -4);
    }

    public static function maskHp(?string $hp): ?string
    {
        if (blank($hp)) {
            return $hp;
        }
        $digits = preg_replace('/\D/', '', (string) $hp);
        if (strlen($digits) < 7) {
            return '****';
        }
        $first = substr($digits, 0, 2);
        $last = substr($digits, -4);

        // Format as 08**-****-1234 style
        return $first.str_repeat('*', 2).'-****-'.$last;
    }

    public static function maskAlamat(?string $alamat): ?string
    {
        if (blank($alamat)) {
            return $alamat;
        }
        $alamat = trim((string) $alamat);
        if (mb_strlen($alamat) <= 3) {
            return '***';
        }

        return mb_substr($alamat, 0, 3).' ***';
    }

    public static function maskNama(?string $nama): ?string
    {
        if (blank($nama)) {
            return $nama;
        }

        $nama = trim((string) $nama);
        if (in_array(strtolower($nama), ['tim', 'kontingen', 'tim / kontingen', 'beregu'], true)) {
            return $nama;
        }

        $words = preg_split('/\s+/', $nama);
        $masked = array_map(function ($word) {
            $len = mb_strlen($word);
            if ($len <= 2) {
                return mb_substr($word, 0, 1) . '*';
            }
            if ($len === 3) {
                return mb_substr($word, 0, 1) . '*' . mb_substr($word, -1);
            }

            return mb_substr($word, 0, 1) . str_repeat('*', $len - 2) . mb_substr($word, -1);
        }, $words);

        return implode(' ', $masked);
    }

    private function formatCabors($defaultStatus = 'daerah'): array
    {
        if ($this->relationLoaded('atletCabors')) {
            return $this->atletCabors->map(function ($ac) {
                return [
                    'id' => $ac->id,
                    'cabor_id' => $ac->cabor_id,
                    'cabor_nama' => $ac->cabor?->nama,
                    'cabor_kode' => $ac->cabor?->kode,
                    'klub_id' => $ac->klub_id,
                    'klub_nama' => $ac->klub?->nama,
                    'pelatih_id' => $ac->pelatih_id,
                    'pelatih_nama' => $ac->pelatih?->nama,
                    'kelas_tanding' => $ac->kelas_tanding,
                    'status_pembinaan' => $ac->status_pembinaan ?? 'daerah',
                ];
            })->values()->toArray();
        }

        if ($this->relationLoaded('cabors')) {
            return $this->cabors->map(function ($c) {
                return [
                    'id' => $c->pivot?->id,
                    'cabor_id' => $c->id,
                    'cabor_nama' => $c->nama,
                    'cabor_kode' => $c->kode,
                    'klub_id' => $c->pivot?->klub_id,
                    'pelatih_id' => $c->pivot?->pelatih_id,
                    'kelas_tanding' => $c->pivot?->kelas_tanding,
                    'status_pembinaan' => $c->pivot?->status_pembinaan ?? 'daerah',
                ];
            })->values()->toArray();
        }

        if ($this->cabor_id) {
            return [
                [
                    'id' => null,
                    'cabor_id' => $this->cabor_id,
                    'cabor_nama' => $this->cabor?->nama,
                    'cabor_kode' => $this->cabor?->kode,
                    'klub_id' => $this->klub_id,
                    'klub_nama' => $this->klub?->nama,
                    'pelatih_id' => $this->pelatih_id,
                    'pelatih_nama' => $this->pelatih?->nama,
                    'kelas_tanding' => $this->kelas_tanding,
                    'status_pembinaan' => $defaultStatus ?? 'daerah',
                ],
            ];
        }

        return [];
    }

    private function formatCaborNames($defaultStatus = 'daerah'): string
    {
        $list = $this->formatCabors($defaultStatus);
        return collect($list)->pluck('cabor_nama')->filter()->unique()->join(', ');
    }
}

