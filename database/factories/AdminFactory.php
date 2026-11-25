<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * A login account for the "admin" guard; staff members sign in with one, too.
 *
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    /** The current password being used by the factory. */
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function forStaff(?Staff $staff = null): static
    {
        return $this->state(fn (): array => ['staff_id' => $staff ?? Staff::factory()]);
    }

    public function mainAdmin(): static
    {
        return $this->role('main_admin');
    }

    public function role(string $name): static
    {
        return $this->afterCreating(fn (Admin $admin): Admin => $admin->assignRole(Role::findOrCreate($name, 'admin')));
    }
}
