<?php

namespace App\Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transaction\Data\TransactionData;
use App\Modules\Transaction\Http\Requests\TransactionRequest;
use App\Modules\Transaction\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TransactionsController extends Controller
{

    public function __invoke(TransactionRequest $request): JsonResponse
    {
        $service = new TransactionService();
        $transactionData = TransactionData::from($request->all());
        $isAllowed = Cache::lock(
            'process_transaction_from_user_id' . $transactionData->payerId,
            5
        );
        if (!$isAllowed->get()) {
            return response()->json(['message' => 'simultaneous execution'], Response::HTTP_BAD_REQUEST);
        }

        $transaction = $service->create($transactionData);
        $isAuthorized = $service->transactionIsAuthorized();
        if (!$isAuthorized) {
            return response()->json(['message' => 'transaction is unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $processIsSuccessful = $service->processTransaction($transaction);
        if (!$processIsSuccessful) {
            return response()->json(['message' => 'Balance is not enough'], Response::HTTP_BAD_REQUEST);
        }
        return response()->json();
    }
}
