<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Staff;
use Illuminate\Http\JsonResponse;

class StaffController
{
    public function surveys(Staff $staff): JsonResponse
    {
        return response()->json($staff->surveys->map(function ($survey) {
            return [
                'id' => $survey->id,
                'title' => $survey->title,
            ];
        }));
    }
}
