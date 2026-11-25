<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An uploaded document attached to a record (patients today). `url` holds the path on the disk.
 */
class File extends Model
{
    /**
     * The private disk: files are only served by PatientFileController. The uploads 1.x stored on
     * the public disk are moved here by `php artisan patient-files:privatize`.
     */
    public const DISK = 'local';

    protected $fillable = [
        'name',
        'url',
    ];

    /** @return MorphTo<Model, $this> */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
