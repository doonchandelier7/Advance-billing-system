<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles: Super Admin, Admin, Employee, CA / Accountant.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Full system access; can manage all settings and users.',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access; can manage most resources and users.',
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Standard staff access for day-to-day billing operations.',
            ],
            [
                'name' => 'CA / Accountant',
                'slug' => 'ca_accountant',
                'description' => 'Chartered Accountant / Accountant role for finance and reports.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
