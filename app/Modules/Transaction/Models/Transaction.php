<?php

namespace App\Modules\Transaction\Models;

use App\Modules\Transaction\Constants\TransactionStatus;
use App\Modules\Transaction\Jobs\SendNotificationJob;
use App\Modules\User\Constants\UserType;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

/**
 * @property int $payer_id
 * @property int $receiver_id
 * @property int $value
 * @property int $status
 * @property mixed $completed_at
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payer_id',
        'receiver_id',
        'value',
        'status',
        'completed_at',
    ];

    public function cancel()
    {
        $this->status = TransactionStatus::CANCELED;
        $this->save();
    }

    public function complete()
    {
        $this->status = TransactionStatus::COMPLETED;
        $this->completed_at = Carbon::now();
        $this->save();
    }

    public function notifyShopOwner()
    {
        $user = User::query()
            ->toBase()
            ->where('type', UserType::SHOP_OWNER)
            ->where('id', $this->receiver_id)
            ->exists();
        if ($user) {
            dispatch(new SendNotificationJob($this->receiver_id));
        }
    }
}
