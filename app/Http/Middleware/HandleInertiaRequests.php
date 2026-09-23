<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'klub_id' => $request->user()->klub_id,
                    'organisasi_id' => $request->user()->organisasi_id,
                    'cabor_id' => $request->user()->cabor_id,
                    'roles' => $request->user()->getRoleNames()->values()->all(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name')->values()->all(),
                ] : null,
                'unread_notifications_count' => $request->user() ? (function () use ($request) {
                    try {
                        return $request->user()->unreadNotifications()->count();
                    } catch (\Throwable $e) {
                        return 0;
                    }
                })() : 0,
                'notifications' => $request->user() ? (function () use ($request) {
                    try {
                        return $request->user()->notifications()->latest()->limit(5)->get()->map(fn ($n) => [
                            'id' => $n->id,
                            'type' => $n->type,
                            'data' => $n->data,
                            'read_at' => $n->read_at?->toDateTimeString(),
                            'created_at' => $n->created_at?->toDateTimeString(),
                            'created_at_human' => $n->created_at?->diffForHumans(),
                        ])->values()->all();
                    } catch (\Throwable $e) {
                        return [];
                    }
                })() : [],
                // can.* helpers for frontend v-if convenience — Sprint 2 1.3 RBAC
                'can' => $request->user() ? [
                    'is_super_admin' => $request->user()->hasRole('super_admin'),
                    'is_verifikator' => $request->user()->hasRole('verifikator'),
                    'is_operator_organisasi' => $request->user()->hasRole('operator_organisasi'),
                    'is_operator_klub' => $request->user()->hasRole('operator_klub'),
                    'is_viewer' => $request->user()->hasRole('viewer'),
                    // view shortcuts used by sidebar/nav
                    'view_wilayah' => $request->user()->can('wilayah.view'),
                    'view_organisasi' => $request->user()->can('organisasi.view'),
                    'view_cabor' => $request->user()->can('cabor.view'),
                    'view_klub' => $request->user()->can('klub.view'),
                    'view_atlet' => $request->user()->can('atlet.view'),
                    'view_sdm' => $request->user()->can('sdm.view'),
                    'view_sarpras' => $request->user()->can('sarpras.view'),
                    'view_verifikasi' => $request->user()->can('verifikasi.view'),
                    'view_kejuaraan' => $request->user()->can('kejuaraan.view'),
                    'view_prestasi' => $request->user()->can('prestasi.view'),
                    'view_pembinaan' => $request->user()->can('pembinaan.view'),
                    'view_laporan' => $request->user()->can('laporan.view'),
                    'view_gis' => $request->user()->can('gis.view'),
                    'view_dashboard' => $request->user()->can('dashboard.view'),
                    'view_user' => $request->user()->can('user.view'),
                    'view_audit' => $request->user()->can('audit.view'),
                    // manage shortcuts for frontend forms/buttons — required by Sprint 2 spec
                    'manage_wilayah' => $request->user()->can('wilayah.manage'),
                    'manage_organisasi' => $request->user()->can('organisasi.manage'),
                    'manage_cabor' => $request->user()->can('cabor.manage'),
                    'manage_klub' => $request->user()->can('klub.manage'),
                    'manage_atlet' => $request->user()->can('atlet.manage'),
                    'manage_sdm' => $request->user()->can('sdm.manage'),
                    'manage_sarpras' => $request->user()->can('sarpras.manage'),
                    'manage_kejuaraan' => $request->user()->can('kejuaraan.manage'),
                    'manage_prestasi' => $request->user()->can('prestasi.manage'),
                    'manage_pembinaan' => $request->user()->can('pembinaan.manage'),
                    'manage_user' => $request->user()->can('user.manage'),
                    'manage_users' => $request->user()->can('user.manage'),
                    'can_manage_users' => $request->user()->can('user.manage'),
                    'manage_verifikasi' => $request->user()->can('verifikasi.manage'),
                    'manage_role' => $request->user()->can('role.manage'),
                    // legacy/alias keys for older frontend components
                    'create_organisasi' => $request->user()->can('organisasi.manage'),
                    'create_cabor' => $request->user()->can('cabor.manage'),
                    'create_klub' => $request->user()->can('klub.manage'),
                    'create_atlet' => $request->user()->can('atlet.manage'),
                    'create_sdm' => $request->user()->can('sdm.manage'),
                ] : [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
