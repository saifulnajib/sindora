<?php

namespace App\Http\Resources;

use App\Enums\TipeSdm;
use App\Enums\VerificationStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SdmResource extends JsonResource
{
    /**
     * Transform the SDM resource with masking for NIK (and optionally no_hp/alamat).
     * Same rule as Atlet: Gate::allows('view-sensitive', $sdm) decides full vs masked.
     * Sprint 3 Part B: add tipe_label, tipe badge, lisensi badge (expired check H-90/H-30), foto_url.
     */
    public function toArray(Request $request): array
    {
        $canViewSensitive = Gate::allows('view-sensitive', $this->resource);

        // Tipe label / enum handling
        $tipe = $this->tipe;
        $tipeValue = $tipe instanceof \BackedEnum ? $tipe->value : $tipe;
        $tipeLabel = null;
        $tipeBadge = null;
        $tipeColor = null;
        if ($tipe instanceof TipeSdm) {
            $tipeLabel = $tipe->label();
            $tipeBadge = $tipe->badgeClasses();
            $tipeColor = $tipe->color();
        } elseif (is_string($tipeValue)) {
            $enum = TipeSdm::tryFrom($tipeValue);
            if ($enum) {
                $tipeLabel = $enum->label();
                $tipeBadge = $enum->badgeClasses();
                $tipeColor = $enum->color();
            } else {
                $tipeLabel = ucfirst($tipeValue);
            }
        }

        // Lisensi badge / expired check using config/sindora.php license_h
        $expiredAt = $this->expired_at;
        // ensure Carbon
        $daysUntilExpired = null;
        $lisensiStatus = null;
        $lisensiLabel = null;
        $lisensiBadge = null;
        $lisensiColor = null;

        if ($expiredAt) {
            $exp = $expiredAt instanceof Carbon ? $expiredAt->copy()->startOfDay() : Carbon::parse($expiredAt)->startOfDay();
            $now = Carbon::now()->startOfDay();
            $daysUntilExpired = (int) $now->diffInDays($exp, false); // negative if expired

            $cfg = config('sindora.license_h.thresholds');
            $thresholds = $cfg ?? [
                'aman' => ['min' => 91, 'label' => 'Aman', 'color' => 'green', 'badge' => 'bg-green-100 text-green-800'],
                'peringatan' => ['min' => 31, 'max' => 90, 'label' => 'Perlu Perpanjangan', 'color' => 'yellow', 'badge' => 'bg-yellow-100 text-yellow-800'],
                'kritis' => ['min' => 1, 'max' => 30, 'label' => 'Segera Perpanjang', 'color' => 'orange', 'badge' => 'bg-orange-100 text-orange-800'],
                'expired' => ['max' => 0, 'label' => 'Kadaluarsa', 'color' => 'red', 'badge' => 'bg-red-100 text-red-800'],
            ];

            if ($daysUntilExpired <= 0) {
                $lisensiStatus = 'expired';
                $lisensiLabel = $thresholds['expired']['label'] ?? 'Kadaluarsa';
                $lisensiBadge = $thresholds['expired']['badge'] ?? 'bg-red-100 text-red-800';
                $lisensiColor = $thresholds['expired']['color'] ?? 'red';
            } elseif ($daysUntilExpired >= 1 && $daysUntilExpired <= 30) {
                $lisensiStatus = 'kritis';
                $lisensiLabel = $thresholds['kritis']['label'] ?? 'Segera Perpanjang';
                $lisensiBadge = $thresholds['kritis']['badge'] ?? 'bg-orange-100 text-orange-800';
                $lisensiColor = $thresholds['kritis']['color'] ?? 'orange';
            } elseif ($daysUntilExpired >= 31 && $daysUntilExpired <= 90) {
                $lisensiStatus = 'peringatan';
                $lisensiLabel = $thresholds['peringatan']['label'] ?? 'Perlu Perpanjangan';
                $lisensiBadge = $thresholds['peringatan']['badge'] ?? 'bg-yellow-100 text-yellow-800';
                $lisensiColor = $thresholds['peringatan']['color'] ?? 'yellow';
            } else {
                $lisensiStatus = 'aman';
                $lisensiLabel = $thresholds['aman']['label'] ?? 'Aman';
                $lisensiBadge = $thresholds['aman']['badge'] ?? 'bg-green-100 text-green-800';
                $lisensiColor = $thresholds['aman']['color'] ?? 'green';
            }
        } else {
            $lisensiStatus = 'none';
            $lisensiLabel = 'Tanpa Lisensi';
            $lisensiBadge = 'bg-gray-100 text-gray-600';
            $lisensiColor = 'gray';
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
            'tipe' => $tipeValue,
            'tipe_label' => $tipeLabel,
            'tipe_badge' => $tipeBadge,
            'tipe_color' => $tipeColor,
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
            'cabor_id' => $this->cabor_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? ['id' => $this->cabor->id, 'nama' => $this->cabor->nama, 'kode' => $this->cabor->kode] : null),
            'klub_id' => $this->klub_id,
            'klub' => $this->whenLoaded('klub', fn () => $this->klub ? ['id' => $this->klub->id, 'nama' => $this->klub->nama] : null),
            'kelurahan_id' => $this->kelurahan_id,
            'kelurahan' => $this->whenLoaded('kelurahan', fn () => $this->kelurahan ? ['id' => $this->kelurahan->id, 'nama' => $this->kelurahan->nama] : null),
            'foto_path' => $this->foto_path,
            'foto_url' => $this->foto_path ? Storage::url($this->foto_path) : null,
            'nomor_lisensi' => $this->nomor_lisensi,
            'lisensi_nomor' => $this->nomor_lisensi,
            'level' => $this->level,
            'lisensi_level' => $this->level,
            'kategori_tenaga' => $this->kategori_tenaga,
            'spesialisasi' => $this->spesialisasi,
            'tanggal_terbit' => $this->tanggal_terbit?->format('Y-m-d'),
            'lisensi_terbit' => $this->tanggal_terbit?->format('Y-m-d'),
            'expired_at' => $this->expired_at?->format('Y-m-d'),
            'days_until_expired' => $daysUntilExpired,
            'lisensi_status' => $lisensiStatus,
            'lisensi_label' => $lisensiLabel,
            'lisensi_badge' => $lisensiBadge,
            'lisensi_color' => $lisensiColor,
            'sertifikat_path' => $this->sertifikat_path,
            'deskripsi' => $this->deskripsi,
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
}
