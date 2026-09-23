<?php

namespace App\Models;

use App\Enums\TipeSdm;
use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sdm extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $table = 'sdms';

    protected $fillable = [
        'nama',
        'tipe',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'cabor_id',
        'klub_id',
        'kelurahan_id',
        'foto_path',
        'nomor_lisensi',
        'level',
        'kategori_tenaga',
        'spesialisasi',
        'tanggal_terbit',
        'expired_at',
        'sertifikat_path',
        'deskripsi',
        'verification_status',
        'catatan_verifikator',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => TipeSdm::class,
            'nik' => 'encrypted',
            'no_hp' => 'encrypted',
            'tanggal_lahir' => 'date',
            'tanggal_terbit' => 'date',
            'expired_at' => 'date',
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function klub(): BelongsTo
    {
        return $this->belongsTo(Klub::class);
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Atlet yang dibina oleh SDM ini sebagai pelatih.
     */
    public function atlets(): HasMany
    {
        return $this->hasMany(Atlet::class, 'pelatih_id');
    }

    public function scopePelatih($query)
    {
        return $query->where('tipe', TipeSdm::Pelatih);
    }

    public function scopeWasit($query)
    {
        return $query->where('tipe', TipeSdm::Wasit);
    }

    public function scopeTenaga($query)
    {
        return $query->where('tipe', TipeSdm::Tenaga);
    }
}
