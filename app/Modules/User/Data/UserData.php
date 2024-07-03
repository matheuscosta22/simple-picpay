<?php

namespace App\Modules\User\Data;

use App\Modules\User\Constants\UserType;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UserData extends Data
{
    #[Computed]
    public int $type;

    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $documentNumber,
    )
    {
        $this->type = Str::length($this->documentNumber) == 14 ? UserType::SHOP_OWNER : UserType::CUSTOMER;
    }
}
