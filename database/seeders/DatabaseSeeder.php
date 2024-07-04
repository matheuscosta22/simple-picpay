<?php

namespace Database\Seeders;

use App\Modules\Document\Constants\DocumentType;
use App\Modules\Document\Models\Document;
use App\Modules\User\Constants\UserType;
use App\Modules\User\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Modules\Wallet\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $user = new User();
            $user->name = 'Admin';
            $user->type = UserType::CUSTOMER;
            $user->email = 'admin@gmail.com';
            $user->password = Hash::make('password');
            $user->save();

            $document = new Document();
            $document->user_id = $user->id;
            $document->number = '13871228001';
            $document->type = DocumentType::CPF;
            $document->save();

            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->balance = 1000000;
            $wallet->save();
        }, 5);
    }
}
