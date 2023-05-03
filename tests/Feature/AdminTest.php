<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function test_login_admin(): void {
        $email = 'test@buckhill.co.uk';
        $password = 'password';
        DB::table('users')->insert(array(
            'uuid' => Str::orderedUuid(),
            'first_name' => "Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => bcrypt($password),
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => false
        ));

        $response = $this->post(
            uri: '/v1/admin/login',
            data: array(
                "email" => $email,
                "password" => $password
            )
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('data.token')
            );
    }
}
