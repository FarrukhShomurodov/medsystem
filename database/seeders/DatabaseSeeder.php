<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\DayOfWeek;
use App\Models\StaffPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::query()->create([
            'email' => 'medicals@gmail.com',
            'password' => Hash::make('medicals@gmail.com'),
        ]);

        StaffPosition::query()->create([
            'name' => 'Доктор'
        ]);

        StaffPosition::query()->create([
            'name' => 'Старшая медсестра'
        ]);

        StaffPosition::query()->create([
            'name' => 'Медсестра'
        ]);

        $days = [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
            'Суббота',
            'Воскресенье',
        ];

        foreach ($days as $day) {
            DayOfWeek::query()->create([
                'name' => $day,
            ]);
        }

    }
}
