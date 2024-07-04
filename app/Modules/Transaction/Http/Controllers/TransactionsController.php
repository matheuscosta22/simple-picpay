<?php

namespace App\Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transaction\Data\TransactionData;
use App\Modules\Transaction\Http\Requests\TransactionRequest;
use App\Modules\Transaction\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TransactionsController extends Controller
{
    public TransactionService $service;

    public function __construct()
    {
        $this->service = new TransactionService();
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->getAll(
                $request->input('page', 1),
                $request->input('per_page', 10)
            )->setPath($request->url())
        );
    }

    public function show(int $transactionId)
    {
        $transaction = $this->service->find($transactionId);

        if (!$transaction) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }

        return response()->json($transaction);
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        $transactionData = TransactionData::from($request->all());
        $isAllowed = Cache::lock(
            'process_transaction_from_user_id' . $transactionData->payerId,
            5
        )->get();
        if (!$isAllowed) {
            return response()->json(['message' => 'simultaneous execution'], Response::HTTP_BAD_REQUEST);
        }

        $transaction = $this->service->create($transactionData);
        $isAuthorized = $this->service->transactionIsAuthorized($transaction);
        if (!$isAuthorized) {
            return response()->json(['message' => 'transaction is unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $processIsSuccessful = $this->service->processTransaction($transaction);
        if (!$processIsSuccessful) {
            return response()->json(['message' => 'Balance is not enough'], Response::HTTP_BAD_REQUEST);
        }
        return response()->json();
    }
}
