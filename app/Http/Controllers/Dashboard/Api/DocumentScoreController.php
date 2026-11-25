<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DocumentScoreController extends Controller
{
    /**
     * The score range of a filled-in document with its recommended actions, and the actions
     * already done for this response (not for other patients with the same range). Only for a
     * sheet the nurse window shows the account (DocumentResponsePolicy).
     */
    public function getActions(Document $document, DocumentResponse $response): JsonResponse
    {
        Gate::authorize('trackActions', $response);

        $range = $document->scores()
            ->containing($response->total_score)
            ->with('actions')
            ->first();

        if ($range === null) {
            return response()->json([]);
        }

        $range->setRelation('finished', $response->finishedScore()->where('document_score_id', $range->id)->get());

        return response()->json($range);
    }
}
