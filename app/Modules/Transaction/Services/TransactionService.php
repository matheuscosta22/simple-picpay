<?php

namespace App\Modules\Transaction\Services;

use App\Modules\Transaction\Constants\TransactionStatus;
use App\Modules\Transaction\Data\TransactionData;
use App\Modules\Transaction\Jobs\CompleteTransactionJob;
use App\Modules\Transaction\Models\Transaction;
use App\Modules\User\Models\User;
use App\Modules\Wallet\Models\Wallet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class TransactionService
{
    public function getAll(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $offset = ($page - 1) * $perPage;
        $transactions = Transaction::query()
            ->toBase()
            ->where(function ($query) {
                $userId = auth()->user()->id;
                $query->orWhere('payer_id', '=', $userId)
                    ->orWhere('receiver_id', '=', $userId);
            })
            ->offset($offset)
            ->take($perPage)
            ->get([
                'id',
                'payer_id',
                'receiver_id',
                'value',
                'status',
                'completed_at',
                'created_at',
                'updated_at'
            ]);

        return new LengthAwarePaginator(
            $transactions,
            $transactions->count(),
            $perPage,
            $page,
        );
    }

    public function find(int $transactionId)
    {
        return Transaction::query()
            ->toBase()
            ->where(function ($query) {
                $userId = auth()->user()->id;
                $query->orWhere('payer_id', '=', $userId)
                    ->orWhere('receiver_id', '=', $userId);
            })
            ->find($transactionId, [
                'id',
                'payer_id',
                'receiver_id',
                'value',
                'status',
                'completed_at',
                'created_at',
                'updated_at'
            ]);
    }

    public function create(TransactionData $transactionData): Transaction
    {
        $transaction = new Transaction();
        $transaction->payer_id = $transactionData->payerId;
        $transaction->receiver_id = $transactionData->receiverId;
        $transaction->value = $transactionData->value;
        $transaction->status = TransactionStatus::NEW;
        $transaction->save();
        return $transaction;
    }

    public function transactionIsAuthorized(Transaction $transaction): bool
    {
        $response = Http::get('https://util.devi.tools/api/v2/authorize');
        if ($response->failed()) {
            $transaction->cancel();
            return false;
        }

        return true;
    }

    public function processTransaction(Transaction $transaction): bool
    {
        /** @var Wallet $wallet */
        $wallet = Wallet::query()
            ->where('user_id', $transaction->payer_id)
            ->first();
        if (!$wallet || $wallet->balance < $transaction->value) {
            $transaction->cancel();
            return false;
        }

        dispatch(new CompleteTransactionJob($transaction->id));
        return true;
    }
}
