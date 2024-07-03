<?php

namespace App\Modules\User\Services;

use App\Modules\User\Data\TransactionData;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function getAccessToken(string $email, string $password): null|string
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (!$user) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        return $user->createToken(
            $user->email . '-AuthToken',
            ['*'],
            Carbon::now()->addMinutes(5)
        )->plainTextToken;
    }
}
