<?php

namespace App\Http\Requests\SarprasJadwal;

use App\Models\SarprasJadwal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSarprasJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sarpras.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'sarpras_id' => ['required', 'integer', Rule::exists('sarpras', 'id')],
            'klub_id' => ['nullable', 'integer', Rule::exists('klubs', 'id')],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i,H:i:s'],
            'jam_selesai' => ['required', 'date_format:H:i,H:i:s', 'after:jam_mulai'],
            'kegiatan' => ['nullable', 'string', 'max:500'],
            // alias field keperluan allowed, will be mapped to kegiatan
            'keperluan' => ['nullable', 'string', 'max:500'],
            'hari' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $this->validated();
            // if alias keperluan provided, use it
            $kegiatan = $data['kegiatan'] ?? $data['keperluan'] ?? null;
            $sarprasId = $data['sarpras_id'] ?? null;
            $tanggal = $data['tanggal'] ?? null;
            $jamMulai = $data['jam_mulai'] ?? null;
            $jamSelesai = $data['jam_selesai'] ?? null;

            if (! $sarprasId || ! $tanggal || ! $jamMulai || ! $jamSelesai) {
                return;
            }

            // normalize to H:i:s
            $normalize = function ($time) {
                return strlen($time) === 5 ? $time.':00' : $time;
            };
            $jamMulaiNorm = $normalize($jamMulai);
            $jamSelesaiNorm = $normalize($jamSelesai);

            $exists = SarprasJadwal::where('sarpras_id', $sarprasId)
                ->where('tanggal', $tanggal)
                ->where(function ($q) use ($jamMulaiNorm, $jamSelesaiNorm) {
                    $q->where('jam_mulai', '<', $jamSelesaiNorm)
                        ->where('jam_selesai', '>', $jamMulaiNorm);
                })
                ->exists();

            if ($exists) {
                $v->errors()->add('jam_mulai', 'Slot bentrok dengan jadwal lain pada sarpras dan tanggal yang sama.');
                $v->errors()->add('jam_selesai', 'Slot bentrok dengan jadwal lain pada sarpras dan tanggal yang sama.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Map keperluan -> kegiatan if kegiatan not set
        if ($this->has('keperluan') && ! $this->has('kegiatan')) {
            $this->merge(['kegiatan' => $this->input('keperluan')]);
        }
        // Ensure jam format consistent (ensure seconds if needed for validation we accept both)
        // no transform needed
    }
}
