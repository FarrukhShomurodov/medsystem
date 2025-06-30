<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentScoreController extends Controller
{
    public function getActions(int $documentId, int $score): JsonResponse
    {
        $matching = DocumentScore::query()
            ->where('document_id', $documentId)
            ->where(function ($query) use ($score) {
                $query->where('from_score', '<=', $score)
                    ->where(function ($q) use ($score) {
                        $q->where('to_score', '>=', $score)
                            ->orWhereNull('to_score');
                    });
            })
            ->first();

        return response()->json([
            'actions' => $matching?->actions ?? [],
        ]);
    }
}
