<?php

namespace App\Http\Controllers;

use App\Enums\VerificationStatus;
use App\Http\Requests\Verifikasi\DecisionRequest;
use App\Http\Requests\Verifikasi\SubmitRequest;
use App\Http\Resources\AtletResource;
use App\Http\Resources\KejuaraanResource;
use App\Http\Resources\KlubResource;
use App\Http\Resources\PembinaanResource;
use App\Http\Resources\PrestasiResource;
use App\Http\Resources\SarprasResource;
use App\Http\Resources\SdmResource;
use App\Models\Atlet;
use App\Models\Kejuaraan;
use App\Models\Klub;
use App\Models\Pembinaan;
use App\Models\Prestasi;
use App\Models\Sarpras;
use App\Models\Sdm;
use App\Models\User;
use App\Notifications\VerifikasiNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VerifikasiController extends Controller
{
    public const ENTITY_MAP = [
        'klub' => Klub::class,
        'atlet' => Atlet::class,
        'sdm' => Sdm::class,
        'sarpras' => Sarpras::class,
        'kejuaraan' => Kejuaraan::class,
        'prestasi' => Prestasi::class,
        'pembinaan' => Pembinaan::class,
    ];

    public const ENTITY_RESOURCE = [
        'klub' => KlubResource::class,
        'atlet' => AtletResource::class,
        'sdm' => SdmResource::class,
        'sarpras' => SarprasResource::class,
        'kejuaraan' => KejuaraanResource::class,
        'prestasi' => PrestasiResource::class,
        'pembinaan' => PembinaanResource::class,
    ];

    /**
     * GET /verifikasi/queue
     * Dashboard Verifikator — lists entities with verification_status = menunggu_verifikasi
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Gate: need verifikasi.view or verifikator/super_admin
        if (! $user->can('verifikasi.view') && ! $user->hasRole(['verifikator', 'super_admin'])) {
            abort(403, 'Tidak memiliki akses verifikasi.');
        }

        $entity = $request->query('entity', 'all');
        $search = $request->string('search')->toString();
        $allowedEntities = ['klub', 'atlet', 'sdm', 'sarpras', 'kejuaraan', 'prestasi', 'pembinaan', 'all'];
        if (! in_array($entity, $allowedEntities, true)) {
            $entity = 'all';
        }

        $perPage = 15;
        $queues = [];
        $counts = [];

        // Helper to build filtered query for each entity
        $buildQuery = function (string $type) use ($search) {
            $modelClass = self::ENTITY_MAP[$type];
            $q = $modelClass::query()->where('verification_status', VerificationStatus::MenungguVerifikasi);

            if ($search !== '') {
                if ($type === 'klub') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('nama', 'like', "%{$search}%")
                            ->orWhere('ketua', 'like', "%{$search}%");
                    });
                } elseif ($type === 'atlet') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('nama', 'like', "%{$search}%")
                            ->orWhere('kelas_tanding', 'like', "%{$search}%");
                    });
                } elseif ($type === 'sdm') {
                    $q->where('nama', 'like', "%{$search}%");
                } elseif ($type === 'sarpras') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('nama', 'like', "%{$search}%")
                            ->orWhere('jenis', 'like', "%{$search}%");
                    });
                } elseif ($type === 'kejuaraan') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('nama', 'like', "%{$search}%")
                            ->orWhere('penyelenggara', 'like', "%{$search}%");
                    });
                } elseif ($type === 'prestasi') {
                    $q->where(function ($qq) use ($search) {
                        $qq->whereHas('atlet', fn ($aq) => $aq->where('nama', 'like', "%{$search}%"))
                            ->orWhereHas('kejuaraan', fn ($kq) => $kq->where('nama', 'like', "%{$search}%"));
                    });
                } elseif ($type === 'pembinaan') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('nama_program', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%");
                    });
                }
            }

            return $q;
        };

        // Counts for tabs (total menunggu per entity, filtered by search if present)
        foreach (['klub', 'atlet', 'sdm', 'sarpras', 'kejuaraan', 'prestasi', 'pembinaan'] as $t) {
            $counts[$t] = (clone $buildQuery($t))->count();
        }
        $counts['all'] = array_sum($counts);

        if ($entity === 'all') {
            foreach (['klub', 'atlet', 'sdm', 'sarpras', 'kejuaraan', 'prestasi', 'pembinaan'] as $t) {
                $paginator = $buildQuery($t)
                    ->with($this->eagerRelations($t))
                    ->orderBy('updated_at', 'desc')
                    ->paginate($perPage, ['*'], $t.'_page')
                    ->withQueryString();

                $resourceClass = self::ENTITY_RESOURCE[$t];
                $queues[$t] = [
                    'data' => $resourceClass::collection($paginator->items())->resolve(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                    'links' => [
                        'prev' => $paginator->previousPageUrl(),
                        'next' => $paginator->nextPageUrl(),
                    ],
                ];
            }
        } else {
            $paginator = $buildQuery($entity)
                ->with($this->eagerRelations($entity))
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage, ['*'], 'page')
                ->withQueryString();

            $resourceClass = self::ENTITY_RESOURCE[$entity];
            // For single entity view, provide queues as single key + also 'current' for convenience
            $queues[$entity] = [
                'data' => collect($paginator->items())->map(function ($model) use ($resourceClass, $entity) {
                    $arr = (new $resourceClass($model))->resolve();
                    $arr['entity_type'] = $entity;

                    return $arr;
                })->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'links' => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ];

            // Also include empty arrays for other entities for tab consistency
            foreach (['klub', 'atlet', 'sdm', 'sarpras', 'kejuaraan', 'prestasi', 'pembinaan'] as $t) {
                if (! isset($queues[$t])) {
                    $queues[$t] = ['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => $perPage, 'total' => 0], 'links' => ['prev' => null, 'next' => null]];
                } else {
                    // ensure entity_type injected already
                }
            }
        }

        // When entity=all, inject entity_type per row for frontend convenience
        if ($entity === 'all') {
            foreach ($queues as $t => &$payload) {
                foreach ($payload['data'] as &$row) {
                    $row['entity_type'] = $t;
                }
                unset($row);
            }
            unset($payload);
        }

        return Inertia::render('Verifikasi/Queue', [
            'queues' => $queues,
            'counts' => $counts,
            'filters' => [
                'entity' => $entity,
                'search' => $search,
            ],
            'can' => [
                'manage' => $user->can('verifikasi.manage'),
                'view' => $user->can('verifikasi.view'),
            ],
        ]);
    }

    /**
     * POST /verifikasi/submit — bulk submit for verification
     */
    public function submit(SubmitRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $entityType = $validated['entity_type'];
        $ids = $validated['ids'];

        $modelClass = self::ENTITY_MAP[$entityType] ?? null;
        if (! $modelClass) {
            abort(422, 'Entity type tidak valid.');
        }

        $user = $request->user();
        $models = $modelClass::whereIn('id', $ids)->get();

        if ($models->count() !== count($ids)) {
            return redirect()->back()->with('error', 'Beberapa data tidak ditemukan.');
        }

        // Authorize each model: ensure operator owns data (via Policy update gate but before terverifikasi guard)
        // We check manage permission + scoped ownership manually to allow draft submission
        foreach ($models as $model) {
            // Check entity manage permission
            $perm = $entityType.'.manage';
            if (! $user->can($perm) && ! $user->hasRole('super_admin')) {
                abort(403, 'Tidak memiliki izin untuk mengajukan '.$entityType.'.');
            }

            // Scoped ownership for operator_klub: use Gate check but bypass terverifikasi guard for submit
            if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
                $modelKlubId = null;
                if (isset($model->klub_id)) {
                    $modelKlubId = $model->klub_id;
                } elseif ($model instanceof Klub) {
                    $modelKlubId = $model->id;
                } elseif ($model instanceof Prestasi) {
                    // Prestasi scope via atlet's klub
                    $model->loadMissing('atlet');
                    $modelKlubId = $model->atlet?->klub_id;
                } elseif ($model instanceof Kejuaraan) {
                    // Kejuaraan is organisasi-level, operator_klub cannot submit kejuaraan (permission already denies), but handle gracefully
                    $modelKlubId = null;
                    // Force deny for prestasi/kejuaraan when operator_klub tries to submit non-owned scope via pembinaan
                    if ($entityType === 'kejuaraan' || $entityType === 'pembinaan') {
                        abort(403, 'Operator klub tidak dapat mengajukan '.$entityType.'.');
                    }
                } elseif ($model instanceof Pembinaan) {
                    abort(403, 'Operator klub tidak dapat mengajukan pembinaan.');
                }

                if ($modelKlubId !== null && (int) $user->klub_id !== (int) $modelKlubId) {
                    abort(403, 'Anda hanya dapat mengajukan data milik klub Anda.');
                }

                if ($entityType === 'prestasi' && $modelKlubId === null) {
                    abort(403, 'Prestasi tanpa atlet/klub tidak dapat diajukan oleh operator klub.');
                }

                if ($modelKlubId === null && $model instanceof Sarpras && $model->klub_id === null) {
                    abort(403, 'Sarpras tanpa klub tidak dapat diajukan oleh operator klub.');
                }
            } elseif ($user->hasRole('operator_klub') && $user->klub_id === null) {
                abort(403, 'Operator klub tanpa klub_id tidak dapat mengajukan verifikasi.');
            }

            // Prevent submitting already terverifikasi without change? Allow but warn
            if (method_exists($model, 'isTerverifikasi') && $model->isTerverifikasi()) {
                // Spec: terverifikasi cannot be edited without re-verification; submitting again is allowed only for super_admin/verifikator
                if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                    return redirect()->back()->with('error', 'Data terverifikasi tidak dapat diajukan ulang oleh operator.');
                }
            }
        }

        // Bulk update to menunggu_verifikasi
        // Use query builder for efficiency, but still fire model events? Use each->update for audit
        foreach ($models as $model) {
            $model->update([
                'verification_status' => VerificationStatus::MenungguVerifikasi,
                // keep catatan_verifikator as is or clear? keep
            ]);
        }

        // Notifikasi 4.5: notify verifikators that new submission waiting
        $this->notifyVerifikatorsOnSubmit($user, $entityType, $models);

        return redirect()->back()->with('success', count($ids).' data '.$entityType.' berhasil diajukan untuk verifikasi.');
    }

    /**
     * POST /verifikasi/{entity}/{id}/approve
     */
    public function approve(Request $request, string $entity, int $id): RedirectResponse
    {
        $this->ensureVerifikator($request);

        $model = $this->resolveModel($entity, $id);

        $catatan = $request->input('catatan_verifikator');

        // Optional catatan validation if provided
        if ($catatan !== null && $catatan !== '' && mb_strlen($catatan) < 10) {
            return redirect()->back()->withErrors(['catatan_verifikator' => 'Catatan minimal 10 karakter.'])->withInput();
        }

        // State machine: only menunggu_verifikasi / perlu_perbaikan / ditolak can be approved? Allow any except already terverifikasi
        // Just call markTerverifikasi
        $model->markTerverifikasi($request->user()->id, $catatan);

        $this->notifyOperatorOnDecision($request->user(), $entity, $model, 'approved', $catatan);

        return redirect()->back()->with('success', ucfirst($entity).' #'.$id.' berhasil diverifikasi.');
    }

    /**
     * POST /verifikasi/{entity}/{id}/reject
     */
    public function reject(DecisionRequest $request, string $entity, int $id): RedirectResponse
    {
        $this->ensureVerifikator($request);

        $model = $this->resolveModel($entity, $id);

        $validated = $request->validated();
        $catatan = $validated['catatan_verifikator'];

        $model->markDitolak($catatan, $request->user()->id);

        $this->notifyOperatorOnDecision($request->user(), $entity, $model, 'rejected', $catatan);

        return redirect()->back()->with('success', ucfirst($entity).' #'.$id.' ditolak.');
    }

    /**
     * POST /verifikasi/{entity}/{id}/request-revision
     */
    public function requestRevision(DecisionRequest $request, string $entity, int $id): RedirectResponse
    {
        $this->ensureVerifikator($request);

        $model = $this->resolveModel($entity, $id);

        $validated = $request->validated();
        $catatan = $validated['catatan_verifikator'];

        $model->markPerluPerbaikan($catatan, $request->user()->id);

        $this->notifyOperatorOnDecision($request->user(), $entity, $model, 'revision', $catatan);

        return redirect()->back()->with('success', ucfirst($entity).' #'.$id.' diminta perbaikan.');
    }

    private function ensureVerifikator(Request $request): void
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (! $user->can('verifikasi.manage') && ! $user->hasRole(['verifikator', 'super_admin'])) {
            abort(403, 'Hanya verifikator yang dapat melakukan aksi ini.');
        }
    }

    private function resolveModel(string $entity, int $id)
    {
        $entity = strtolower($entity);
        $modelClass = self::ENTITY_MAP[$entity] ?? null;
        if (! $modelClass) {
            abort(404, 'Entity tidak dikenal.');
        }

        $model = $modelClass::find($id);
        if (! $model) {
            abort(404, 'Data tidak ditemukan.');
        }

        return $model;
    }

    private function eagerRelations(string $entity): array
    {
        return match ($entity) {
            'klub' => ['cabor', 'kecamatan', 'kelurahan'],
            'atlet' => ['klub', 'cabor', 'kelurahan'],
            'sdm' => ['klub', 'cabor'],
            'sarpras' => ['klub', 'cabor', 'kelurahan', 'kecamatan'],
            'kejuaraan' => ['organisasi', 'cabor'],
            'prestasi' => ['atlet', 'cabor', 'kejuaraan'],
            'pembinaan' => ['organisasi', 'cabor'],
            default => [],
        };
    }

    private function getEntityName($model, string $entityType): string
    {
        return $model->nama ?? $model->nama_program ?? $model->name ?? (ucfirst($entityType).' #'.$model->getKey());
    }

    private function notifyVerifikatorsOnSubmit(User $actor, string $entityType, $models): void
    {
        try {
            $verifikators = User::role(['verifikator', 'super_admin'])->get();
            // Also users with verifikasi.manage permission (in case custom roles)
            $withPerm = User::permission('verifikasi.manage')->get();
            $targets = $verifikators->merge($withPerm)->unique('id')->filter(fn ($u) => $u->id !== $actor->id);

            if ($targets->isEmpty()) {
                return;
            }

            foreach ($models as $model) {
                $name = $this->getEntityName($model, $entityType);
                $message = "{$actor->name} mengajukan {$entityType} '{$name}' untuk verifikasi.";
                foreach ($targets as $target) {
                    try {
                        $target->notify(new VerifikasiNotification(
                            entityType: $entityType,
                            entityId: $model->getKey(),
                            entityName: $name,
                            action: 'submitted',
                            message: $message,
                            actorName: $actor->name,
                        ));
                    } catch (\Throwable $e) {
                        // silently ignore notification failures
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function notifyOperatorOnDecision(User $actor, string $entityType, $model, string $action, ?string $catatan = null): void
    {
        try {
            $name = $this->getEntityName($model, $entityType);
            $actionLabel = match ($action) {
                'approved' => 'disetujui',
                'rejected' => 'ditolak',
                'revision' => 'diminta perbaikan',
                default => $action,
            };
            $message = "{$entityType} '{$name}' {$actionLabel} oleh {$actor->name}.";

            // Targets: operators + super_admin, plus try to find owner via klub/organisasi context
            $candidates = collect();

            // If model has klub_id, notify operators of that klub
            if (isset($model->klub_id) && $model->klub_id) {
                $candidates = $candidates->merge(User::where('klub_id', $model->klub_id)->get());
            }
            // If model is Klub itself, notify its klub users
            if ($model instanceof Klub) {
                $candidates = $candidates->merge(User::where('klub_id', $model->id)->get());
            }
            // If model is Prestasi, get via atlet klub
            if ($model instanceof Prestasi) {
                $model->loadMissing('atlet');
                if ($model->atlet?->klub_id) {
                    $candidates = $candidates->merge(User::where('klub_id', $model->atlet->klub_id)->get());
                }
            }
            // If organisasi scoped
            if (isset($model->organisasi_id) && $model->organisasi_id) {
                $candidates = $candidates->merge(User::where('organisasi_id', $model->organisasi_id)->get());
            }

            // fallback: all operators + super_admin
            if ($candidates->isEmpty()) {
                $candidates = User::role(['operator_klub', 'operator_organisasi', 'super_admin'])->get();
            } else {
                // also include super_admin
                $candidates = $candidates->merge(User::role('super_admin')->get());
            }

            $targets = $candidates->unique('id')->filter(fn ($u) => $u->id !== $actor->id);
            if ($targets->isEmpty()) {
                // ensure at least fallback to all super_admin minus actor
                $targets = User::role('super_admin')->where('id', '!=', $actor->id)->get();
            }

            foreach ($targets as $target) {
                try {
                    $target->notify(new VerifikasiNotification(
                        entityType: $entityType,
                        entityId: $model->getKey(),
                        entityName: $name,
                        action: $action,
                        message: $message,
                        actorName: $actor->name,
                        catatan: $catatan,
                    ));
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
