<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'nama',
        'kode',
    ];

    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class);
    }

    public function klubs(): HasMany
    {
        return $this->hasMany(Klub::class);
    }

    public function sarpras(): HasMany
    {
        return $this->hasMany(Sarpras::class);
    }
}
