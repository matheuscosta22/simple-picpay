<?php

namespace App\Modules\Transaction\Services;

use App\Modules\Transaction\Constants\TransactionStatus;
use App\Modules\Transaction\Data\TransactionData;
use App\Modules\Transaction\Jobs\CompleteTransactionJob;
use App\Modules\Transaction\Models\Transaction;
use App\Modules\Wallet\Models\Wallet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TransactionService
{
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

    public function transactionIsAuthorized(): bool
    {
        $response = Http::get('https://util.devi.tools/api/v2/authorize');
        return $response->successful();
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
