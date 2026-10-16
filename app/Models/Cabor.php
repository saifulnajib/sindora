<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Cabor extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'organisasi_id',
        'nama',
        'kode',
        'kategori',
        'deskripsi',
        'logo_path',
        'sk_nomor',
        'sk_tgl_mulai',
        'sk_tgl_selesai',
        'sk_file_path',
        'nama_ketua',
        'kontak_pengurus',
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'sk_tgl_mulai' => 'date',
            'sk_tgl_selesai' => 'date',
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    public function getSkFileUrlAttribute(): ?string
    {
        return $this->sk_file_path ? Storage::url($this->sk_file_path) : null;
    }

    /**
     * Compute SK status: aktif, segera_berakhir, kadaluarsa, belum_lengkap
     */
    public function getSkStatusAttribute(): string
    {
        if (! $this->sk_nomor && ! $this->sk_file_path) {
            return 'belum_lengkap';
        }

        if (! $this->sk_tgl_selesai) {
            return 'aktif';
        }

        $today = Carbon::today();
        $selesai = Carbon::parse($this->sk_tgl_selesai)->startOfDay();

        if ($selesai->lt($today)) {
            return 'kadaluarsa';
        }

        if ($selesai->lte($today->copy()->addDays(60))) {
            return 'segera_berakhir';
        }

        return 'aktif';
    }

    public function getSkStatusLabelAttribute(): string
    {
        $status = $this->sk_status;

        if ($status === 'belum_lengkap') {
            return 'Belum Ada SK';
        }

        if ($status === 'kadaluarsa') {
            return 'Kedaluwarsa (Berakhir ' . ($this->sk_tgl_selesai ? Carbon::parse($this->sk_tgl_selesai)->translatedFormat('d M Y') : '') . ')';
        }

        if ($status === 'segera_berakhir') {
            $days = Carbon::today()->diffInDays(Carbon::parse($this->sk_tgl_selesai));
            return 'Segera Berakhir (' . $days . ' hari lagi)';
        }

        return 'Aktif s/d ' . ($this->sk_tgl_selesai ? Carbon::parse($this->sk_tgl_selesai)->translatedFormat('d M Y') : '—');
    }

    public function getSkStatusBadgeAttribute(): string
    {
        return match ($this->sk_status) {
            'aktif' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 ring-emerald-300',
            'segera_berakhir' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 ring-amber-300',
            'kadaluarsa' => 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300 ring-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 ring-gray-300',
        };
    }

    public function setKategoriAttribute($value): void
    {
        if (! $value) {
            $this->attributes['kategori'] = null;
            return;
        }

        $cleaned = strtolower(trim((string) $value));
        // Backward compatibility mapping
        if ($cleaned === 'prestasi') {
            $cleaned = 'koni';
        } elseif ($cleaned === 'massal') {
            $cleaned = 'kormi';
        }

        $this->attributes['kategori'] = $cleaned;
    }

    public function getKategoriLabelAttribute(): string
    {
        return match (strtolower($this->kategori ?? '')) {
            'koni' => 'KONI',
            'kormi' => 'KORMI',
            'disabilitas' => 'DISABILITAS',
            default => $this->kategori ? strtoupper($this->kategori) : '—',
        };
    }

    public function scopeKategori($query, ?string $kategori)
    {
        if (! $kategori) {
            return $query;
        }

        return $query->where('kategori', strtolower(trim($kategori)));
    }

    public function scopeKoni($query)
    {
        return $query->where('kategori', 'koni');
    }

    public function scopeKormi($query)
    {
        return $query->where('kategori', 'kormi');
    }

    public function scopeDisabilitas($query)
    {
        return $query->where('kategori', 'disabilitas');
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class);
    }

    public function klubs(): HasMany
    {
        return $this->hasMany(Klub::class);
    }

    public function atlets(): BelongsToMany
    {
        return $this->belongsToMany(Atlet::class, 'atlet_cabor')
            ->withPivot(['klub_id', 'pelatih_id', 'kelas_tanding', 'status_pembinaan'])
            ->withTimestamps();
    }

    public function atletCabors(): HasMany
    {
        return $this->hasMany(AtletCabor::class);
    }

    public function sdms(): HasMany
    {
        return $this->hasMany(Sdm::class);
    }

    public function prestasis(): HasMany
    {
        return $this->hasMany(Prestasi::class);
    }

    public function sarpras(): HasMany
    {
        return $this->hasMany(Sarpras::class);
    }

    public function kejuaraans(): HasMany
    {
        return $this->hasMany(Kejuaraan::class);
    }

    public function pembinaans(): HasMany
    {
        return $this->hasMany(Pembinaan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
