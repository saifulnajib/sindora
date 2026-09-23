<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class);
    }

    public function klubs(): HasMany
    {
        return $this->hasMany(Klub::class);
    }

    public function atlets(): HasMany
    {
        return $this->hasMany(Atlet::class);
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
