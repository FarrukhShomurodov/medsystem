<?php

namespace App\Models;

use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    const COLOR = [
        '#F4EAD5', // светло-бежевый
        '#DFF6FF', // светло-голубой
        '#E0FFE0', // мятный
        '#FFF5CC', // пастельный жёлтый
        '#FFEBE0', // персиковый
        '#E6E6FA', // лаванда
        '#F0FFF0', // медово-зелёный
        '#FAFAD2', // светло-золотистый
        '#FFE4E1', // розовато-бежевый
        '#F5FFFA', // мятно-кремовый

        // Яркие и живые
        '#FFD700', // ярко-золотой
        '#FF69B4', // ярко-розовый
        '#00CED1', // бирюзовый
        '#FFA07A', // светлый лосось
        '#98FB98', // светло-зелёный
        '#FFB6C1', // светло-розовый
        '#FF8C00', // тёмно-оранжевый
        '#7FFFD4', // аквамарин
        '#FF6347', // томатный
        '#87CEFA', // светло-голубой (небесный)
    ];

    protected $fillable = [
        'title',
        'survey_type_id',
        'diagnosis_id',
        'color',
    ];

    /**
     * @return BelongsTo<Diagnosis, $this>
     */
    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }

    /**
     * @return BelongsTo<SurveyType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SurveyType::class, 'survey_type_id', 'id');
    }

    /**
     * @return HasMany<SurveyQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    /**
     * @return HasMany<SurveyGroup, $this>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(SurveyGroup::class);
    }
}
