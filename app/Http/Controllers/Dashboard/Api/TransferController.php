<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\Request;

class TransferController
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function updateStatus(Request $request, Transfer $transfer)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,canceled'
        ]);

        $transfer->update(['status' => $request->status]);
        $this->transferService->updating($transfer);

        return response()->json(['success' => true, 'message' => 'Статус успешно обновлен']);
    }

}
