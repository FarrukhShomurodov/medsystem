<?php

namespace App\Services\Clinical\Scoring;

use App\Models\Document;

/**
 * The scored assessment sheets among the medical documents, keyed by their fixed document id
 * (DocumentSeeder creates documents 1-8 with these ids; document 1, the nursing inspection,
 * has no score). The id is the stable key: the titles are display text and may be edited.
 */
enum MedicalScale: int
{
    case MorseFallRisk = 2;
    case HumptyDumptyFallRisk = 3;
    case OutpatientFallRisk = 4;
    case NumericPain = 5;
    case FlaccPain = 6;
    case NipsPain = 7;
    case CpotPain = 8;

    public static function forDocument(Document $document): ?self
    {
        return self::tryFrom($document->id);
    }
}
