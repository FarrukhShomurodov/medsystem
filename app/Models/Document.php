<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    /**
     * "Первичный сестринский осмотр": filled in on admission (fixed id, see DocumentSeeder), so it
     * is offered on the map forms and left out of the other document pickers.
     */
    public const INITIAL_NURSING_ASSESSMENT = 1;

    protected $fillable = [
        'title',
    ];

    /**
     * In the order of the paper sheet, which is the order they were seeded in.
     *
     * @return HasMany<DocumentQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(DocumentQuestion::class)->orderBy('id');
    }

    /**
     * @return HasMany<DocumentScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(DocumentScore::class);
    }

    /**
     * @return HasMany<DocumentResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(DocumentResponse::class);
    }
}
