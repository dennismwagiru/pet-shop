<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;
use Str;
use Tests\TestCase;

class DeleteUser extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    // Utility function to create a test user
    private function createTestUser(): UuidInterface
    {
        $uuid = Str::orderedUuid();

        DB::table('users')->insert(array(
            'uuid' => $uuid,
            'first_name' => "Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => 'test@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => 'password',
            'avatar' => $uuid,
            'address' => '20 132',
            'phone_number' => '25471234823',
            'is_marketing' => false
        ));

        return $uuid;
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
     * Test whether an admin can be deleted with an unauthenticated request
     *
     * 1. Assert the response status code is 401
     * 2. Assert that the user still exists in the database
     */
    public function test_delete_admin_unauthenticated(): void {
        $uuid = $this->createTestUser();
        $response = $this->delete(
            uri: '/api/v1/admin/user-delete/'.$uuid,
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

        $this->assertDatabaseHas('users', [
            "email" => 'test@buckhill.co.uk'
        ]);
    }

    /**
     * Test whether a uuid that doesn't exist can be deleted
     *
     * 1. Assert that this fails with status code 404
     */
    public function test_delete_admin_who_doesnt_exist() {
        $uuid = Str::uuid();
        $response = $this->delete(
            uri: '/api/v1/admin/user-delete/'.$uuid,
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $response
            ->assertStatus(404)
            ->assertJsonPath('success', 0)
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);
    }

    /**
     * Test whether an admin can login after deletion
     *
     * 1. Assert that user login fails after deletion
     */
    public function test_admin_login_after_delete(): void {
        $uuid = $this->createTestUser();

        \Log::warning($uuid);
        $this->delete(
            uri: '/api/v1/admin/user-delete/'.$uuid,
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );
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
}
