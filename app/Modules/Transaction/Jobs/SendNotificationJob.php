<?php

namespace App\Modules\Transaction\Jobs;

use App\Modules\Transaction\Constants\TransactionStatus;
use App\Modules\Transaction\Models\Transaction;
use App\Modules\User\Models\User;
use App\Modules\Wallet\Constants\RegistryType;
use App\Modules\Wallet\Exceptions\WalletBalanceCannotBeNegativeException;
use App\Modules\Wallet\Models\Wallet;
use App\Modules\Wallet\Models\WalletRegistry;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $userId;
    public int $allowedTries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::query()
            ->toBase()
            ->find($this->userId, ['id', 'email']);
        Http::retry($this->allowedTries)
            ->post("https://util.devi.tools/api/v1/notify", ['email' => $user->email])
            ->throw();
    }


    public function failed(Throwable $throwable)
    {
        $cache = Cache::get($key = "send_notification_" . $this->userId);
        $tries = $cache ?? 0;
        if (empty($cache) || $tries < $this->allowedTries) {
            Cache::put($key, $tries + 1, Carbon::now()->addHour());
            $this->release(60);
        }

        Log::error(
            'Error when try to send notification to user id: ' . $this->userId,
            ['error_message' => $throwable->getMessage()]
        );
    }
}
