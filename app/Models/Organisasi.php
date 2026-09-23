<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisasi extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'nama',
        'singkatan',
        'jenis',
        'alamat',
        'ketua',
        'kontak_hp',
        'kontak_email',
        'logo_path',
        'deskripsi',
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

    public function cabors(): HasMany
    {
        return $this->hasMany(Cabor::class);
    }

    public function pembinaans(): HasMany
    {
        return $this->hasMany(Pembinaan::class);
    }

    public function kejuaraans(): HasMany
    {
        return $this->hasMany(Kejuaraan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
