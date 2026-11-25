<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Exceptions\Stock\StockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\TransferStatusRequest;
use App\Models\Transfer;
use App\Services\Stock\TransferService;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct(private readonly TransferService $transferService) {}

    /** The status select of transfers/index: completing a transfer moves its stock. */
    public function updateStatus(TransferStatusRequest $request, Transfer $transfer): JsonResponse
    {
        $status = $request->status();

        try {
            $this->transferService->changeStatus($transfer, $status);
        } catch (StockException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Статус перевода: «{$status->label()}».",
            'status' => $status->value,
        ]);
    }
}
