<?php

namespace App\Models;

use Database\Factories\DocumentResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentResponse extends Model
{
    /** @use HasFactory<DocumentResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'examination_map_id',
        'document_id',
        'total_score',
    ];

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<ExaminationMap, $this>
     */
    public function map(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class, 'examination_map_id');
    }

    /** The initial nursing assessment, whether or not a stay holds it yet. */
    public function isInitialAssessment(): bool
    {
        return $this->document_id === Document::INITIAL_NURSING_ASSESSMENT;
    }

    /**
     * The initial nursing assessment filled in before the admission, which no stay holds yet:
     * the one filled-in document that may still be deleted (from the new-stay form). Any other
     * sheet is part of the patient's medical record.
     */
    public function isUnattachedInitialAssessment(): bool
    {
        return $this->isInitialAssessment() && $this->examination_map_id === null;
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function unattachedInitialAssessment(Builder $query): void
    {
        $query->where('document_id', Document::INITIAL_NURSING_ASSESSMENT)->whereNull('examination_map_id');
    }

    /** @return HasMany<DocumentAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(DocumentAnswer::class, 'document_response_id', 'id');
    }

    /**
     * @return HasMany<PatientDocumentScoreFinished, $this>
     */
    public function finishedScore(): HasMany
    {
        return $this->hasMany(PatientDocumentScoreFinished::class);
    }
}
