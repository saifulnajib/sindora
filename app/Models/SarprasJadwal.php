<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SarprasJadwal extends Model
{
    use HasFactory;

    protected $table = 'sarpras_jadwals';

    protected $fillable = [
        'sarpras_id',
        'klub_id',
        'hari',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'kegiatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_mulai' => 'datetime:H:i',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    public function sarpras(): BelongsTo
    {
        return $this->belongsTo(Sarpras::class);
    }

    public function klub(): BelongsTo
    {
        return $this->belongsTo(Klub::class);
    }
}
