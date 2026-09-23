<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelurahan extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'kecamatan_id',
        'nama',
        'kode',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function klubs(): HasMany
    {
        return $this->hasMany(Klub::class);
    }

    public function sarpras(): HasMany
    {
        return $this->hasMany(Sarpras::class);
    }

    public function atlets(): HasMany
    {
        return $this->hasMany(Atlet::class);
    }

    public function sdms(): HasMany
    {
        return $this->hasMany(Sdm::class);
    }
}
