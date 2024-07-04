<?php

namespace Tests\Feature;

use App\Modules\Transaction\Constants\TransactionStatus;
use App\Modules\Transaction\Models\Transaction;
use App\Modules\User\Models\User;
use App\Modules\Wallet\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionsControllerTest extends TestCase
{
    public function test_create_and_make_a_authorized_transaction(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' =>  Http::response([], Response::HTTP_OK)
        ]);
        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "682.680.280-50"
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        $user = User::find($data['id']);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->where('user_id', $user->id)->first();
        $wallet->balance = 1000;
        $wallet->save();

        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando2@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "18.721.648/0001-12"
        ]);

        $response->assertSuccessful();
        $data2 = $response->json();

        $response = $this->withHeader('accept', 'application/json')
            ->actingAs($user)
            ->post('/api/users/transactions', [
                'receiver_id' => $data2['id'],
                'value' => 1000,
            ]);
        $response->assertSuccessful();

        $wallet->refresh();
        $shopOwnerWallet = Wallet::query()->where('user_id', $data2['id'])->first();
        $transaction = Transaction::query()->toBase()->first();
        $this->assertEquals(TransactionStatus::COMPLETED, $transaction->status);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals(1000, $shopOwnerWallet->balance);
    }

    public function test_create_and_make_a_unauthorized_transaction(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' =>  Http::response([], Response::HTTP_BAD_REQUEST)
        ]);
        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "682.680.280-50"
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        $user = User::find($data['id']);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->where('user_id', $user->id)->first();
        $wallet->balance = 1000;
        $wallet->save();

        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando2@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "18.721.648/0001-12"
        ]);

        $response->assertSuccessful();
        $data2 = $response->json();

        $response = $this->withHeader('accept', 'application/json')
            ->actingAs($user)
            ->post('/api/users/transactions', [
                'receiver_id' => $data2['id'],
                'value' => 1000,
            ]);
        $response->assertForbidden();

        $wallet->refresh();
        $shopOwnerWallet = Wallet::query()->where('user_id', $data2['id'])->first();
        $this->assertEquals(1000, $wallet->balance);
        $this->assertEquals(0, $shopOwnerWallet->balance);
    }

    public function test_list_transactions()
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' =>  Http::response([], Response::HTTP_OK)
        ]);
        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "682.680.280-50"
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        $user = User::find($data['id']);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->where('user_id', $user->id)->first();
        $wallet->balance = 1000;
        $wallet->save();

        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando2@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "18.721.648/0001-12"
        ]);

        $response->assertSuccessful();
        $data2 = $response->json();

        $response = $this->withHeader('accept', 'application/json')
            ->actingAs($user)
            ->post('/api/users/transactions', [
                'receiver_id' => $data2['id'],
                'value' => 1000,
            ]);
        $response->assertSuccessful();

        $response = $this->withHeader('accept', 'application/json')
            ->actingAs($user)
            ->get('/api/users/transactions?page=1&per_page=10');
        $response->assertSuccessful();
        $this->assertEquals($user->id, $response->json()['data'][0]['payer_id']);
        $this->assertEquals($data2['id'], $response->json()['data'][0]['receiver_id']);

        $response = $this->withHeader('accept', 'application/json')
            ->actingAs($user)
            ->get('/api/users/transactions/1');
        $response->assertSuccessful();
        $this->assertEquals($user->id, $response->json()['payer_id']);
        $this->assertEquals($data2['id'], $response->json()['receiver_id']);
    }
}
