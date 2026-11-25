<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * The initial administrator, from ADMIN_EMAIL / ADMIN_PASSWORD (config/medsystem.php).
 * An existing account keeps its password, so re-seeding never resets a changed one.
 */
class AdminSeeder extends Seeder
{
    private const string DEFAULT_EMAIL = 'admin@medsystem.test';

    private const string DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $email = config('medsystem.admin.email') ?: self::DEFAULT_EMAIL;
        $password = config('medsystem.admin.password') ?: self::DEFAULT_PASSWORD;

        if ($password === self::DEFAULT_PASSWORD && app()->isProduction()) {
            throw new RuntimeException('Refusing to seed the administrator with the default password in production: set ADMIN_PASSWORD.');
        }

        Admin::query()
            ->firstOrCreate(['email' => $email], ['password' => $password])
            ->assignRole(Admin::MAIN_ADMIN_ROLE);
    }
}
