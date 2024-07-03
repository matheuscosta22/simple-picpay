<?php

namespace App\Modules\User\Services;

use App\Modules\Document\Constants\DocumentType;
use App\Modules\Document\Models\Document;
use App\Modules\User\Constants\UserType;
use App\Modules\User\Data\UserData;
use App\Modules\User\Models\User;
use App\Modules\Wallet\Models\Wallet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $offset = ($page - 1) * $perPage;
        $users = User::query()
            ->toBase()
            ->offset($offset)
            ->take($perPage)
            ->get([
                'id',
                'name',
                'type',
                'email',
                'created_at',
                'updated_at'
            ]);

        return new LengthAwarePaginator(
            $users,
            $users->count(),
            $perPage,
            $page,
        );
    }

    public function create(UserData $userData): User
    {
        DB::transaction(function () use ($userData, &$user) {
            $user = new User();
            $user->name = $userData->name;
            $user->type = $userData->type;
            $user->email = $userData->email;
            $user->password = Hash::make($userData->password);
            $user->save();

            $document = new Document();
            $document->user_id = $user->id;
            $document->number = $userData->documentNumber;
            $document->type = $user->type == UserType::SHOP_OWNER ? DocumentType::CNPJ : DocumentType::CPF;
            $document->save();

            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->balance = 0;
            $wallet->save();
        }, 5);

        return $user;
    }

    public function find(int $userId)
    {
        return User::query()
            ->toBase()
            ->find($userId, [
                'id',
                'name',
                'type',
                'email',
                'created_at',
                'updated_at'
            ]);
    }
}
