<?php

namespace Tests\Feature;

use App\Modules\User\Models\User;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    public function test_create_and_get_user(): void
    {
        $response = $this->post('/api/users', [
            "name" => "Matheus Costa",
            "email" => "testando@gmail.com",
            "password" => "asdfasdf",
            "document_number" => "682.680.280-50"
        ]);

        $response->assertSuccessful();
        $data = $response->json();
        $this->assertDatabaseHas('documents', ['user_id' => $data['id']]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $data['id'],
            'balance' => 0,
        ]);

        $user = User::find($data['id']);
        $response = $this->actingAs($user)->get('/api/users/1');
        $this->assertEquals($data['id'], $response->json()['id']);

        $response = $this->actingAs($user)->get('/api/users');
        $this->assertEquals($data['id'], $response->json()['data'][0]['id']);
    }
}
