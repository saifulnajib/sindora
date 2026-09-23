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

class Klub extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'nama',
        'cabor_id',
        'kelurahan_id',
        'kecamatan_id',
        'alamat',
        'ketua',
        'kontak_hp',
        'kontak_email',
        'logo_path',
        'nomor_sk',
        'tanggal_sk',
        'dokumen_legalitas_path',
        'jadwal_latihan',
        'deskripsi',
        'latitude',
        'longitude',
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
            'tanggal_sk' => 'date',
            'jadwal_latihan' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function atlets(): HasMany
    {
        return $this->hasMany(Atlet::class);
    }

    public function sdms(): HasMany
    {
        return $this->hasMany(Sdm::class);
    }

    public function sarpras(): HasMany
    {
        return $this->hasMany(Sarpras::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
