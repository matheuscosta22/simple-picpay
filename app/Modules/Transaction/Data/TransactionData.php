<?php

namespace App\Modules\Transaction\Data;

use App\Modules\User\Constants\UserType;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class TransactionData extends Data
{

    public function __construct(
        public int $payerId,
        public int $receiverId,
        public int $value,
    )
    {
    }
}
