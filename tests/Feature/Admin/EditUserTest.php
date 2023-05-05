<?php

namespace Admin;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;
use Tests\TestCase;

class EditUserTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    protected function getPayload(): array
    {
        return [
            'first_name' => "New",
            'last_name' => "Name",
            'is_admin' => true,
            'email' => 'test-email-changed@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => '',
            'address' => '95 Nairobi',
            'phone_number' => '+254704128303',
            'is_marketing' => false
        ];
    }

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

    public function test_edit_admin_unauthenticated(): void
    {
        $uuid = $this->createTestUser();

        $payload = $this->getPayload();

        $response = $this->put(
            uri: '/api/v1/admin/user-edit/'.$uuid,
            data: $payload
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
            "email" => $payload['email']
        ]);
    }

    public function test_edit_admin_missing_uuid(): void {
        $uuid = Str::uuid();

        $payload = $this->getPayload();
        $response = $this->put(
            uri: '/api/v1/admin/user-edit/'.$uuid,
            data: $payload,
            headers: [
                'Authorization' => 'Bearer ' . $this->getUserToken()
            ]
        );

        $response->assertStatus(404)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', 'User not found')
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => $payload['email']
        ]);
    }

    public function test_edit_admin_validation(): void {
        $uuid = $this->createTestUser();

        $response = $this->put(
            uri: '/api/v1/admin/user-edit/'.$uuid,
            headers: [
                'Authorization' => 'Bearer ' . $this->getUserToken()
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', 0)
            ->assertJsonPath('error', 'Failed Validation')
            ->assertJsonStructure([
                'success',
                'data',
                'error',
                'errors',
                'trace'
            ]);
    }

    public function test_edit_admin_valid(): void {
        $uuid = $this->createTestUser();
        $payload = $this->getPayload();

        $response = $this->put(
            uri: '/api/v1/admin/user-edit/'.$uuid,
            data: $payload,
            headers: [
                'Authorization' => 'Bearer ' . $this->getUserToken()
            ]
        );

        $response->assertStatus(200)
            ->assertJsonPath('success', 1)
            ->assertJsonPath('data.email', $payload['email'])
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
                ],
                'error',
                'errors',
                'extra'
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => 'test@buckhill.co.uk'
        ]);

        $this->assertDatabaseHas('users', [
            "email" => $payload['email']
        ]);
    }
}
