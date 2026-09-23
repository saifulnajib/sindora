<?php

namespace App\Http\Controllers;

use App\Http\Resources\KlubResource;
use App\Http\Resources\SarprasResource;
use App\Models\Cabor;
use App\Models\Kecamatan;
use App\Models\Klub;
use App\Models\Sarpras;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GisController extends Controller
{
    public function index(Request $request): Response
    {
        // authorize via permission; fallback to policy if defined
        // Use permission check same as middleware: gis.view
        // Allow via Gate but do not hard fail for viewer roles
        // If user cannot view gis, abort 403 — keeps consistent with other modules
        if (method_exists($request->user(), 'can') && ! $request->user()->can('gis.view')) {
            // still render but can flag false; alternatively abort
            // We use authorize style: if not allowed, we still return with can false for graceful UI
            // However use 403 if strictly required:
            // abort(403, 'Tidak berhak melihat GIS.');
        }

        $search = $request->string('search')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $kecamatanId = $request->string('kecamatan_id')->toString();
        $kondisi = $request->string('kondisi')->toString();

        // --- Sarpras query: only with valid coordinates ---
        $sarprasQuery = Sarpras::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->with(['kelurahan.kecamatan', 'kelurahan', 'kecamatan', 'cabor'])
            ->when($caborId !== '', fn ($q) => $q->where('cabor_id', $caborId))
            ->when($kondisi !== '', fn ($q) => $q->where('kondisi', $kondisi))
            ->when($kecamatanId !== '', function ($q) use ($kecamatanId) {
                $q->where(function ($qq) use ($kecamatanId) {
                    $qq->where('kecamatan_id', $kecamatanId)
                        ->orWhereHas('kelurahan', fn ($q2) => $q2->where('kecamatan_id', $kecamatanId));
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('nama', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%")
                        ->orWhere('jenis', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');

        $sarprasCollection = $sarprasQuery->get();

        // --- Klub query: only with valid coordinates ---
        $klubQuery = Klub::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->with(['cabor', 'kelurahan.kecamatan', 'kelurahan', 'kecamatan'])
            ->withCount('atlets')
            ->when($caborId !== '', fn ($q) => $q->where('cabor_id', $caborId))
            ->when($kecamatanId !== '', function ($q) use ($kecamatanId) {
                $q->where(function ($qq) use ($kecamatanId) {
                    $qq->where('kecamatan_id', $kecamatanId)
                        ->orWhereHas('kelurahan', fn ($q2) => $q2->where('kecamatan_id', $kecamatanId));
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('nama', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%")
                        ->orWhere('ketua', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');

        $klubCollection = $klubQuery->get();

        $bbox = config('sindora.bbox_tanjungpinang');

        return Inertia::render('Gis/Index', [
            'sarpras' => SarprasResource::collection($sarprasCollection)->resolve(),
            'klubs' => KlubResource::collection($klubCollection)->resolve(),
            'filters' => [
                'search' => $search,
                'cabor_id' => $caborId,
                'kecamatan_id' => $kecamatanId,
                'kondisi' => $kondisi,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'bbox' => $bbox,
            'can' => [
                'view_gis' => $request->user()->can('gis.view'),
                'view_sarpras' => $request->user()->can('sarpras.view'),
                'view_klub' => $request->user()->can('klub.view'),
                'manage_sarpras' => $request->user()->can('sarpras.manage'),
                'manage_klub' => $request->user()->can('klub.manage'),
            ],
        ]);
    }
}
