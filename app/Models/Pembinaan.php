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

class Pembinaan extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'nama_program',
        'deskripsi',
        'target',
        'anggaran',
        'sumber_anggaran',
        'tahun_anggaran',
        'periode_mulai',
        'periode_selesai',
        'evaluasi',
        'status',
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
        'organisasi_id',
        'cabor_id',
    ];

    protected function casts(): array
    {
        return [
            'anggaran' => 'decimal:2',
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class);
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(PembinaanPeserta::class);
    }

    /**
     * Helper: attach peserta polymorphic (Atlet|Klub|Sdm)
     */
    public function attachPeserta(Model $peserta, ?string $peran = null, ?string $catatan = null): PembinaanPeserta
    {
        return $this->peserta()->create([
            'peserta_type' => get_class($peserta),
            'peserta_id' => $peserta->getKey(),
            'peran' => $peran,
            'catatan' => $catatan,
        ]);
    }

    // Convenience relations via polymorphic pivot
    public function atlets()
    {
        return $this->morphedByMany(Atlet::class, 'peserta', 'pembinaan_peserta', 'pembinaan_id', 'peserta_id');
    }

    public function klubs()
    {
        return $this->morphedByMany(Klub::class, 'peserta', 'pembinaan_peserta', 'pembinaan_id', 'peserta_id');
    }

    public function sdms()
    {
        return $this->morphedByMany(Sdm::class, 'peserta', 'pembinaan_peserta', 'pembinaan_id', 'peserta_id');
    }
}
