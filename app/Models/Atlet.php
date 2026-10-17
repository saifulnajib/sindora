<?php

namespace App\Models;

use App\Enums\StatusPembinaan;
use App\Enums\VerificationStatus;
use App\Traits\Auditable;
use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atlet extends Model
{
    use Auditable, HasFactory, HasVerificationStatus, SoftDeletes;

    protected $fillable = [
        'nama',
        'nik',
        'nik_hash',
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

    protected $attributes = [
        'status_pembinaan' => 'daerah',
    ];

    protected static function booted(): void
    {
        static::saving(function (Atlet $atlet) {
            if ($atlet->isDirty('nik') && $atlet->nik) {
                $atlet->nik_hash = self::hashNik($atlet->nik);
            }
        });
    }

    public static function hashNik(?string $nik): ?string
    {
        if (blank($nik)) {
            return null;
        }

        return hash_hmac('sha256', trim($nik), config('app.key'));
    }

    public static function findByNik(?string $nik): ?self
    {
        $hash = self::hashNik($nik);
        if (! $hash) {
            return null;
        }

        return static::query()->where('nik_hash', $hash)->first();
    }

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

    public function atletCabors(): HasMany
    {
        return $this->hasMany(AtletCabor::class);
    }

    public function cabors(): BelongsToMany
    {
        return $this->belongsToMany(Cabor::class, 'atlet_cabor')
            ->withPivot(['id', 'klub_id', 'pelatih_id', 'kelas_tanding', 'status_pembinaan'])
            ->withTimestamps();
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
