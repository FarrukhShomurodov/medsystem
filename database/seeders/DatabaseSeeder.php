<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\DayOfWeek;
use App\Models\ServiceType;
use App\Models\StaffPosition;
use App\Models\VitalParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
//        Admin::query()->create([
//            'email' => 'medicals@gmail.com',
//            'password' => Hash::make('medicals@gmail.com'),
//        ]);
//
//        StaffPosition::query()->create([
//            'name' => 'Доктор'
//        ]);
//
//        StaffPosition::query()->create([
//            'name' => 'Старшая медсестра'
//        ]);
//
//        StaffPosition::query()->create([
//            'name' => 'Медсестра'
//        ]);
//
//        StaffPosition::query()->create([
//            'name' => 'Заведуюший'
//        ]);
//
//        $types = [
//            'Данные отсутствуют',
//            'Общие исследования',
//            'Манипуляция',
//            'Инструментальные исследования',
//            'Лабораторное тестирование',
//            'Лечение',
//            'Анестезиологические процедуры',
//            'Интенсивная терапия',
//            'Хирургия',
//            'Профилактические процедуры',
//            'Консультация',
//            'Основные',
//            'Лечение медикаментами',
//            'Медицинские записи',
//            'Сервис почасовой',
//            'Технические',
//            'Дневной сервис',
//            'Имплантация',
//            'Иммунизация',
//            'Химиотерапия',
//            'Питание',
//        ];
//
//        foreach ($types as $type) {
//            ServiceType::query()->create([
//                'name' => $type
//            ]);
//        }
//
//        $days = [
//            'Понедельник',
//            'Вторник',
//            'Среда',
//            'Четверг',
//            'Пятница',
//            'Суббота',
//            'Воскресенье',
//        ];
//
//        foreach ($days as $day) {
//            DayOfWeek::query()->create([
//                'name' => $day,
//            ]);
//        }
//
        $this->call(PermissionSeeder::class);
//
//        $this->call(DocumentSeeder::class);
//        $this->call(WarehouseSeeder::class);

//        VitalParameter::query()->create([
//            'name' => 'Систолическое АД'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Диастолическое АД'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Пульс'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Температура'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Сатурация (SPO₂)'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Частота дыхания (ЧДД)'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Уровень сознания'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Кислород/воздух'
//        ]);
//
//        VitalParameter::query()->create([
//            'name' => 'Диурез / Баланс'
//        ]);
    }
}
