<?php

namespace App\Providers;

use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\Pembinaan;
use App\Models\Sarpras;
use App\Models\Sdm;
use App\Models\User;
use App\Policies\AtletPolicy;
use App\Policies\CaborPolicy;
use App\Policies\KecamatanPolicy;
use App\Policies\KelurahanPolicy;
use App\Policies\KlubPolicy;
use App\Policies\OrganisasiPolicy;
use App\Policies\PembinaanPolicy;
use App\Policies\SarprasPolicy;
use App\Policies\SdmPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Sprint 2 1.3 — Explicit policy mappings (Laravel also auto-discovers by naming convention)
        Gate::policy(Atlet::class, AtletPolicy::class);
        Gate::policy(Sdm::class, SdmPolicy::class);
        Gate::policy(Kecamatan::class, KecamatanPolicy::class);
        Gate::policy(Kelurahan::class, KelurahanPolicy::class);
        Gate::policy(Organisasi::class, OrganisasiPolicy::class);
        Gate::policy(Cabor::class, CaborPolicy::class);
        Gate::policy(Klub::class, KlubPolicy::class);
        Gate::policy(Sarpras::class, SarprasPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Pembinaan::class, PembinaanPolicy::class);

        // 0.7 Masking gate: view-sensitive for Atlet & Sdm
        Gate::define('view-sensitive', function ($user, $model = null) {
            if ($model instanceof Atlet) {
                return app(AtletPolicy::class)->viewSensitive($user, $model);
            }
            if ($model instanceof Sdm) {
                return app(SdmPolicy::class)->viewSensitive($user, $model);
            }
            // Generic check without model (e.g. Gate::allows('view-sensitive')) -> allow only super_admin
            if ($model === null) {
                return $user->hasRole('super_admin');
            }

            return false;
        });
    }
}
