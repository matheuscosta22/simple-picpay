<?php

namespace App\Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $wallet_id
 * @property int $transaction_id
 * @property int $value
 * @property int $balance_after
 * @property mixed $completed_at
 * @property int $type
 */
class WalletRegistry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'value',
        'balance_after',
        'type',
        'completed_at',
    ];
}
