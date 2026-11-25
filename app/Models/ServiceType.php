<?php

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory;

    /** ServiceTypeSeeder creates types 1-21 with fixed ids; the price list and its import rely on them. */
    public const LAST_SYSTEM_TYPE_ID = 21;

    /** "Инструментальные исследования": the patient card orders only those booked by appointment. */
    public const INSTRUMENTAL_STUDIES_ID = 4;

    protected $fillable = ['name'];

    public function isSystem(): bool
    {
        return $this->id <= self::LAST_SYSTEM_TYPE_ID;
    }
}
