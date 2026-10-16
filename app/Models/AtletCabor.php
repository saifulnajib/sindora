<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtletCabor extends Model
{
    use HasFactory;

    protected $table = 'atlet_cabor';

    protected $fillable = [
        'atlet_id',
        'cabor_id',
        'klub_id',
        'pelatih_id',
        'kelas_tanding',
        'status_pembinaan',
    ];

    public function atlet(): BelongsTo
    {
        return $this->belongsTo(Atlet::class);
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function klub(): BelongsTo
    {
        return $this->belongsTo(Klub::class);
    }

    public function pelatih(): BelongsTo
    {
        return $this->belongsTo(Sdm::class, 'pelatih_id');
    }
}
