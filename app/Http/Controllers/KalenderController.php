<?php

namespace App\Http\Controllers;

use App\Models\Cabor;
use App\Models\Kejuaraan;
use App\Models\Organisasi;
use App\Models\Pembinaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KalenderController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->string('month')->toString();
        if ($month === '' || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = Carbon::now()->format('Y-m');
        }

        try {
            $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        } catch (\Throwable $e) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $month = $startOfMonth->format('Y-m');
        }

        $caborId = $request->string('cabor_id')->toString();
        $organisasiId = $request->string('organisasi_id')->toString();

        // Kejuaraan events overlapping month
        $kejuaraanQuery = Kejuaraan::query()
            ->with(['cabor:id,nama,kode', 'organisasi:id,nama'])
            ->whereNotNull('tanggal_mulai')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where(function ($qq) use ($startOfMonth, $endOfMonth) {
                    $qq->where('tanggal_mulai', '<=', $endOfMonth->format('Y-m-d'))
                        ->whereRaw('COALESCE(tanggal_selesai, tanggal_mulai) >= ?', [$startOfMonth->format('Y-m-d')]);
                });
            })
            ->when($caborId !== '', fn ($q) => $q->where('cabor_id', $caborId))
            ->when($organisasiId !== '', fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->orderBy('tanggal_mulai');

        $kejuaraans = $kejuaraanQuery->get();

        // Pembinaan events overlapping month
        $pembinaanQuery = Pembinaan::query()
            ->with(['cabor:id,nama,kode', 'organisasi:id,nama'])
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where(function ($qq) use ($startOfMonth, $endOfMonth) {
                    $qq->where('periode_mulai', '<=', $endOfMonth->format('Y-m-d'))
                        ->whereRaw('COALESCE(periode_selesai, periode_mulai) >= ?', [$startOfMonth->format('Y-m-d')]);
                })->orWhere(function ($qq) use ($startOfMonth, $endOfMonth) {
                    // handle null periode_mulai but has periode_selesai
                    $qq->whereNull('periode_mulai')
                        ->whereNotNull('periode_selesai')
                        ->where('periode_selesai', '>=', $startOfMonth->format('Y-m-d'))
                        ->where('periode_selesai', '<=', $endOfMonth->format('Y-m-d'));
                });
            })
            ->when($caborId !== '', fn ($q) => $q->where('cabor_id', $caborId))
            ->when($organisasiId !== '', fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->orderBy('periode_mulai');

        // Also include pembinaans without any date? skip for calendar
        $pembinaans = $pembinaanQuery->get()->filter(fn ($p) => $p->periode_mulai !== null || $p->periode_selesai !== null);

        $events = [];

        foreach ($kejuaraans as $k) {
            $start = $k->tanggal_mulai?->format('Y-m-d');
            $endRaw = $k->tanggal_selesai?->format('Y-m-d');
            $end = $endRaw ?? $start;
            if (! $start) {
                continue;
            }
            $events[] = [
                'id' => 'kejuaraan-'.$k->id,
                'entity_id' => $k->id,
                'title' => $k->nama,
                'start' => $start,
                'end' => $end,
                'type' => 'kejuaraan',
                'location' => $k->lokasi,
                'tingkat' => $k->tingkat,
                'status' => $k->verification_status instanceof \BackedEnum ? $k->verification_status->value : $k->verification_status,
                'cabor' => $k->cabor ? ['id' => $k->cabor->id, 'nama' => $k->cabor->nama] : null,
                'organisasi' => $k->organisasi ? ['id' => $k->organisasi->id, 'nama' => $k->organisasi->nama] : null,
                'url' => route('kejuaraans.show', $k->id),
            ];
        }

        foreach ($pembinaans as $p) {
            $start = $p->periode_mulai?->format('Y-m-d');
            $endRaw = $p->periode_selesai?->format('Y-m-d');
            // fallback: if no start but has end, use end as start
            if (! $start && $endRaw) {
                $start = $endRaw;
            }
            if (! $start) {
                continue;
            }
            $end = $endRaw ?? $start;
            $events[] = [
                'id' => 'pembinaan-'.$p->id,
                'entity_id' => $p->id,
                'title' => $p->nama_program,
                'start' => $start,
                'end' => $end,
                'type' => 'pembinaan',
                'location' => null,
                'tingkat' => null,
                'status' => $p->status,
                'verification_status' => $p->verification_status instanceof \BackedEnum ? $p->verification_status->value : $p->verification_status,
                'cabor' => $p->cabor ? ['id' => $p->cabor->id, 'nama' => $p->cabor->nama] : null,
                'organisasi' => $p->organisasi ? ['id' => $p->organisasi->id, 'nama' => $p->organisasi->nama] : null,
                'url' => route('pembinaans.show', $p->id),
            ];
        }

        // Sort events by start date
        usort($events, fn ($a, $b) => strcmp($a['start'], $b['start']));

        return Inertia::render('Kalender/Index', [
            'events' => $events,
            'filters' => [
                'month' => $month,
                'cabor_id' => $caborId,
                'organisasi_id' => $organisasiId,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
        ]);
    }
}
