<?php

namespace Database\Seeders;

use App\Models\Cabor;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist (in case this seeder runs standalone)
        if (Role::count() === 0) {
            $this->call(RolePermissionSeeder::class);
        }

        // Try to resolve contextual ids for operator users
        $organisasi = Organisasi::first();
        $cabor = Cabor::first();
        $klub = Klub::first();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@sindora.test',
                'role' => 'super_admin',
                'extra' => [],
            ],
            [
                'name' => 'Verifikator Dispora',
                'email' => 'verifikator@sindora.test',
                'role' => 'verifikator',
                'extra' => [],
            ],
            [
                'name' => 'Pimpinan',
                'email' => 'pimpinan@sindora.test',
                'role' => 'pimpinan',
                'extra' => [],
            ],
            [
                'name' => 'Pengurus Cabor (Pengkot)',
                'email' => 'pengkot@sindora.test',
                'role' => 'pengurus_cabor',
                'extra' => [
                    'organisasi_id' => $organisasi?->id,
                    'cabor_id' => $cabor?->id,
                ],
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Update password if needed to ensure "password"
            if (! Hash::check('password', $user->password)) {
                $user->update(['password' => Hash::make('password')]);
            }

            // Update kontek IDs if available and not already set
            $extra = array_filter($data['extra'], fn ($v) => ! is_null($v));
            if (! empty($extra)) {
                $user->fill($extra);
                // Only save if fillable attributes exist in DB (klub_id etc)
                try {
                    $user->save();
                    if ($user->cabor_id) {
                        $user->cabors()->syncWithoutDetaching([$user->cabor_id]);
                    }
                } catch (\Throwable $e) {
                    // Ignore if FK constraints fail when no master data yet
                    logger()->warning('DemoUserSeeder context link failed', [
                        'email' => $data['email'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Assign role (sync without detaching other logic)
            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
