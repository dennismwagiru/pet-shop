<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Str;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_admin_with_mismatching_passwords(): void {
        $response = $this->post(
            uri: '/api/v1/admin/create',
            data: array(
                "first_name" => "Test",
                "last_name" => "User",
                "email" => "test@buckhill.co.uk",
                "password" => "password",
                "password_confirmation" => "password_confirmation",
                "avatar" => Str::uuid(),
                "phone_number" => "2547023129303"
            )
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', "Failed Validation")
            ->assertJsonPath('errors.password', "The password confirmation does not match.")
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors' => [
                    'password'
                ],
                'trace'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => "test@buckhill.co.uk"
        ]);
    }

    public function test_create_admin_with_invalid_payload(): void
    {
        $response = $this->post(
            uri: '/api/v1/admin/create',
            data: array(
                "email" => "test@buckhill.co.uk",
                "password" => "password",
                "password_confirmation" => "password",
                "avatar" => Str::uuid(),
                "phone_number" => "2547023129303"
            )
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', "Failed Validation")
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => "test@buckhill.co.uk"
        ]);
    }

    public function test_create_admin_with_existing_email(): void
    {
        $email = 'test@buckhill.co.uk';
        $password = 'password';
        DB::table('users')->insert(array(
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
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
            uri: '/api/v1/admin/create',
            data: array(
                "first_name" => "Test",
                "last_name" => "User",
                "email" => $email,
                "password" => $password,
                "password_confirmation" => "password_confirmation",
                "avatar" => Str::uuid(),
                "phone_number" => "2547023129303"
            )
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', "Failed Validation")
            ->assertJsonPath('errors.email', "The email has already been taken.")
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors' => [
                    'email'
                ],
                'trace'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => $email
        ]);
    }

    public function test_create_admin_with_valid_payload(): void {
        $email = 'test@buckhill.co.uk';
        $password = 'password';
        $response = $this->post(
            uri: '/api/v1/admin/create',
            data: array(
                "first_name" => "Test",
                "last_name" => "User",
                "email" => $email,
                "password" => $password,
                "password_confirmation" => $password,
                "avatar" => Str::uuid(),
                "phone_number" => "2547023129303"
            )
        );

        $response->assertStatus(200)
            ->assertJsonPath('success', 1)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'address',
                    'phone_number',
                    'updated_at',
                    'created_at',
                    'token'
                ],
                'error',
                'errors',
                'extra'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => $email
        ]);
    }
}
