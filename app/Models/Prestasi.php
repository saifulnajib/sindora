<?php

namespace App\Models;

use App\Enums\Medali;
use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestasi extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'atlet_id',
        'cabor_id',
        'kejuaraan_id',
        'kategori_kelas',
        'medali',
        'peringkat',
        'tanggal',
        'sertifikat_path',
        'nomor_sertifikat',
        'keterangan',
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'medali' => Medali::class,
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
            'tanggal' => 'date',
        ];
    }

    public function atlet(): BelongsTo
    {
        return $this->belongsTo(Atlet::class);
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function kejuaraan(): BelongsTo
    {
        return $this->belongsTo(Kejuaraan::class);
    }
}
