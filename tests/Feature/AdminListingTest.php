<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminListingTest extends TestCase
{
    use DatabaseTransactions;

    protected array $structure = [
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'links',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
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

    public function test_unauthenticated_listing(): void
    {
        $response = $this->get('/api/v1/admin/user-listing',
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
    }

    public function test_authenticated_listing(): void {
        $response = $this->get('/api/v1/admin/user-listing',
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $count = DB::table('users')->where('is_admin', true)->count();

        $response->assertStatus(200)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('total', $count)
            ->assertJsonStructure($this->structure);
    }

    public function test_authenticated_listing_pagination(): void {
        DB::table('users')->insert([
            'uuid' => Str::orderedUuid(),
            'first_name' => "Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => 'test-user@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '95 Nairobi',
            'phone_number' => '+254704128303',
            'is_marketing' => false
        ]);
        $response = $this->get('/api/v1/admin/user-listing?page=2&limit=1',
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $count = DB::table('users')->where('is_admin', true)->count();

        $response->assertStatus(200)
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', $count)
            ->assertJsonStructure($this->structure);
    }

    public function test_authenticated_filtered_listing(): void {
        DB::table('users')->insert([
            'uuid' => Str::orderedUuid(),
            'first_name' => "A Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => 'a-test-user@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => false
        ]);
        $response = $this->get('/api/v1/admin/user-listing?email=a-test-user',
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $response->assertStatus(200)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('data.0.email', 'a-test-user@buckhill.co.uk')
            ->assertJsonPath('total', 1)
            ->assertJsonStructure($this->structure);
    }

    public function test_authenticated_sort_by_unknown_column(): void {
        $response = $this->get('/api/v1/admin/user-listing?sortBy=emailsss&desc=true',
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $response->assertStatus(200)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('data.0.email', 'admin@buckhill.co.uk')
            ->assertJsonPath('total', 1)
            ->assertJsonStructure($this->structure);
    }

    public function test_authenticated_ordered_listing(): void {
        DB::table('users')->insert([
            'uuid' => Str::orderedUuid(),
            'first_name' => "A Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => 'a-test-user@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => false
        ]);
        DB::table('users')->insert([
            'uuid' => Str::orderedUuid(),
            'first_name' => "Test",
            'last_name' => "User",
            'is_admin' => true,
            'email' => 'test-user@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => false
        ]);
        $response = $this->get('/api/v1/admin/user-listing?sortBy=email&desc=true',
            headers: [
                'Authorization' => 'Bearer '. $this->getUserToken(),
            ]
        );

        $count = DB::table('users')->where('is_admin', true)->count();

        $response->assertStatus(200)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('data.0.email', 'test-user@buckhill.co.uk')
            ->assertJsonPath('data.1.email', 'admin@buckhill.co.uk')
            ->assertJsonPath('data.2.email', 'a-test-user@buckhill.co.uk')
            ->assertJsonPath('total', $count)
            ->assertJsonStructure($this->structure);
    }
}
