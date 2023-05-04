<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_admin_incorrect_credentials(): void {
        DB::table('users')->insert(array(
            'uuid' => Str::orderedUuid(),
            'first_name' => "Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => 'test@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => false
        ));

        $response = $this->post(
            uri: '/api/v1/admin/login',
            data: array(
                "email" => 'test@buckhill.co.uk',
                "password" => 'incorrect_password'
            )
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);
    }
    public function test_login_admin_normal_user_credentials(): void {
        DB::table('users')->insert(array(
            'uuid' => Str::orderedUuid(),
            'first_name' => "Test",
            'last_name' => "User",
            'is_admin' => false,
            'email' => 'test@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => false
        ));

        $response = $this->post(
            uri: '/api/v1/admin/login',
            data: array(
                "email" => 'test@buckhill.co.uk',
                "password" => 'password'
            )
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);
    }
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
            uri: '/api/v1/admin/login',
            data: array(
                "email" => $email,
                "password" => $password
            )
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', 1)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token'
                ],
                'error',
                'errors',
                'extra'
            ]);
    }
}
