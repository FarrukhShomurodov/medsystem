<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\DayOfWeekFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayOfWeek extends Model
{
    /** @use HasFactory<DayOfWeekFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * The id of the date's weekday: the rows are seeded with ISO-8601 ids, Monday = 1 (DayOfWeekSeeder).
     */
    public static function idOf(CarbonInterface $date): int
    {
        return $date->isoWeekday();
    }
}
