<?php

namespace App\Models;

use App\Enums\KondisiSarpras;
use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sarpras extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $table = 'sarpras';

    protected $fillable = [
        'nama',
        'jenis',
        'alamat',
        'kelurahan_id',
        'kecamatan_id',
        'klub_id',
        'cabor_id',
        'latitude',
        'longitude',
        'kondisi',
        'kapasitas',
        'foto_path',
        'deskripsi',
        'fasilitas',
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'kondisi' => KondisiSarpras::class,
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
            'fasilitas' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function klub(): BelongsTo
    {
        return $this->belongsTo(Klub::class);
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(SarprasJadwal::class);
    }
}
