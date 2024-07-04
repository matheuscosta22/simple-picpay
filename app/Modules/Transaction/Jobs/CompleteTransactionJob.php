<?php

namespace App\Modules\Transaction\Jobs;

use App\Modules\Transaction\Constants\TransactionStatus;
use App\Modules\Transaction\Models\Transaction;
use App\Modules\Wallet\Constants\RegistryType;
use App\Modules\Wallet\Exceptions\WalletBalanceCannotBeNegativeException;
use App\Modules\Wallet\Models\Wallet;
use App\Modules\Wallet\Models\WalletRegistry;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompleteTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $transactionId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::query()->find($this->transactionId);
        if (is_null($transaction->completed_at) && $transaction->status == TransactionStatus::NEW) {
            /** @var Wallet $payerWallet */
            $payerWallet = Wallet::query()
                ->where('user_id', $transaction->payer_id)
                ->first();
            if (!$payerWallet) {
                $transaction->cancel();
                return;
            }

            /** @var Wallet $receiverWallet */
            $receiverWallet = Wallet::query()
                ->where('user_id', $transaction->receiver_id)
                ->first();
            if (!$receiverWallet) {
                $transaction->cancel();
                return;
            }

            DB::transaction(function () use ($transaction, $payerWallet, $receiverWallet) {
                $this->updateWalletBalance($payerWallet, $transaction, RegistryType::CASH_OUT);
                $this->updateWalletBalance($receiverWallet, $transaction, RegistryType::CASH_IN);
                $transaction->complete();
                $transaction->notifyShopOwner();
            }, 5);
        }
    }

    private function updateWalletBalance(Wallet $wallet, Transaction $transaction, int $registryType)
    {
        $balanceAfter = $registryType == RegistryType::CASH_OUT ?
            $wallet->balance - $transaction->value :
            $wallet->balance + $transaction->value;

        if ($balanceAfter < 0) {
            throw new WalletBalanceCannotBeNegativeException();
        }

        $walletRegistry = new WalletRegistry();
        $walletRegistry->wallet_id = $wallet->id;
        $walletRegistry->transaction_id = $transaction->id;
        $walletRegistry->value = $transaction->value;
        $walletRegistry->balance_after = $balanceAfter;
        $walletRegistry->type = $registryType;
        $walletRegistry->completed_at = Carbon::now();
        $walletRegistry->save();

        $wallet->balance = $balanceAfter;
        $wallet->save();
    }

    public function failed(Throwable $throwable)
    {
        if ($throwable instanceof WalletBalanceCannotBeNegativeException) {
            /** @var Transaction $transaction */
            $transaction = Transaction::query()->find($this->transactionId);
            $transaction->cancel();
        }

        Log::error(
            'Error when try to complete transaction id: ' . $this->transactionId,
            ['error_message' => $throwable->getMessage()]
        );
    }
}
