<?php

namespace Admin;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Str;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /**
     * Generate an access token to be used in http requests
     */
    protected function getUserToken() {
        $response = $this->post(
            uri: '/api/v1/admin/login',
            data: array(
                "email" => 'admin@buckhill.co.uk',
                "password" => 'password'
            )
        );

        return $response->json('data.token');
    }

    /**
     * Test whether an admin can be created with an unauthenticated request
     *
     * 1. Assert the response status code is 401
     * 2. Assert that the user was not saved to the database
     */
    public function test_create_admin_unauthenticated(): void {
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
                "phone_number" => "2547023129303",
                "address" => "32, Nairobi"
            ),
            headers: [
                'Accept' => 'application/json'
            ]
        );

        $response->assertStatus(401)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', 'Unauthorized')
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => $email
        ]);
    }

    /**
     * Test whether an admin can be created with mismatching passwords
     *
     * 1. Assert the response status code is 422
     * 2. Assert that the error is a validation error
     * 3. Assert that the errors indicate password did not match confirmation
     * 4. Assert that the user was not saved to the database
     */
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
            ),
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', "Failed Validation")
            ->assertJsonPath('errors.password.0', "The password confirmation does not match.")
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

    /**
     * Test whether an admin can be created with an invalid payload
     *
     * 1. Assert the response status code is 422
     * 2. Assert that the error is a validation error
     * 3. Assert that the user was not saved to the database
     */
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
            ),
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
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

    /**
     * Test whether an admin can be created with an already existing email address
     *
     * 1. Assert the response status code is 422
     * 2. Assert that the error is a validation error
     * 3. Assert that the errors indicate that the email is already taken
     * 4. Assert that the user was not saved to the database
     */
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
            ),
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', "Failed Validation")
            ->assertJsonPath('errors.email.0', "The email has already been taken.")
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors' => [
                    'email'
                ],
                'trace'
            ]);

        $count = DB::table('users')->where('email', $email)->count();

        $this->assertTrue($count == 1);
    }

    /**
     * Test whether an admin can be created with a valid payload
     *
     * 1. Assert the response status code is 200
     * 2. Assert that success is 1
     * 3. Assert that the email in the response is the same one that was sent
     * 4. Assert that the user exists in the database
     */
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
                "phone_number" => "2547023129303",
                "address" => "32, Nairobi"
            ),
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $response->assertStatus(200)
            ->assertJsonPath('success', 1)
            ->assertJsonPath('data.email', $email)
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

        $this->assertDatabaseHas('users', ['email' => $email]);
    }
}
