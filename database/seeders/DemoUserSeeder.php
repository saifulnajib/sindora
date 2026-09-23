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
                'name' => 'Verifikator',
                'email' => 'verifikator@sindora.test',
                'role' => 'verifikator',
                'extra' => [],
            ],
            [
                'name' => 'Operator Organisasi',
                'email' => 'org@sindora.test',
                'role' => 'operator_organisasi',
                'extra' => [
                    'organisasi_id' => $organisasi?->id,
                    'cabor_id' => $cabor?->id,
                ],
            ],
            [
                'name' => 'Operator Klub',
                'email' => 'klub@sindora.test',
                'role' => 'operator_klub',
                'extra' => [
                    'klub_id' => $klub?->id,
                    'cabor_id' => $klub?->cabor_id ?? $cabor?->id,
                ],
            ],
            [
                'name' => 'Pimpinan Viewer',
                'email' => 'pimpinan@sindora.test',
                'role' => 'viewer',
                'extra' => [],
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
