<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PembinaanPeserta extends Model
{
    use HasFactory;

    protected $table = 'pembinaan_peserta';

    protected $fillable = [
        'pembinaan_id',
        'peserta_type',
        'peserta_id',
        'peran',
        'catatan',
    ];

    public function pembinaan(): BelongsTo
    {
        return $this->belongsTo(Pembinaan::class);
    }

    public function peserta(): MorphTo
    {
        return $this->morphTo();
    }
}
