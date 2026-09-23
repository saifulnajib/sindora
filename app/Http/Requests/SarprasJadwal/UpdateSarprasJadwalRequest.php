<?php

namespace App\Http\Requests\SarprasJadwal;

use App\Models\SarprasJadwal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSarprasJadwalRequest extends FormRequest
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
            $sarprasId = $data['sarpras_id'] ?? null;
            $tanggal = $data['tanggal'] ?? null;
            $jamMulai = $data['jam_mulai'] ?? null;
            $jamSelesai = $data['jam_selesai'] ?? null;

            if (! $sarprasId || ! $tanggal || ! $jamMulai || ! $jamSelesai) {
                return;
            }

            $normalize = function ($time) {
                return strlen($time) === 5 ? $time.':00' : $time;
            };
            $jamMulaiNorm = $normalize($jamMulai);
            $jamSelesaiNorm = $normalize($jamSelesai);

            // Get current jadwal id from route parameter (sarpras_jadwal)
            $currentId = $this->route('sarpras_jadwal') ? $this->route('sarpras_jadwal')->id ?? $this->route('sarpras_jadwal') : $this->route('id');
            // fallback: try to get model via route binding name 'sarpras_jadwal' or 'jadwal'
            if (is_object($currentId) && isset($currentId->id)) {
                $currentId = $currentId->id;
            }

            $exists = SarprasJadwal::where('sarpras_id', $sarprasId)
                ->where('tanggal', $tanggal)
                ->where('id', '!=', $currentId)
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
        if ($this->has('keperluan') && ! $this->has('kegiatan')) {
            $this->merge(['kegiatan' => $this->input('keperluan')]);
        }
    }
}
