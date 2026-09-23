<?php

namespace App\Models;

use App\Enums\StatusPembinaan;
use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atlet extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'nama',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'klub_id',
        'cabor_id',
        'pelatih_id',
        'kelurahan_id',
        'kelas_tanding',
        'status_pembinaan',
        'berat_badan',
        'tinggi_badan',
        'foto_path',
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'tanggal_lahir' => 'date',
            'status_pembinaan' => StatusPembinaan::class,
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
        ];
    }

    public function klub(): BelongsTo
    {
        return $this->belongsTo(Klub::class);
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    /**
     * Pelatih (SDM where tipe=pelatih)
     */
    public function pelatih(): BelongsTo
    {
        return $this->belongsTo(Sdm::class, 'pelatih_id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    public function prestasis(): HasMany
    {
        return $this->hasMany(Prestasi::class);
    }
}
