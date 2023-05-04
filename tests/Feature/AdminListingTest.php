<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
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
        $response = $this->get('/api/v1/admin/user-listing');

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

        $count = DB::table('users')->where('is_admin')->count();

        $response->assertStatus(200)
            ->assertJsonPath('success', 1)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('total', $count)
            ->assertJsonStructure($this->structure);
    }

    public function test_authenticated_filtered_listing(): void {
        $this->assertTrue(true);
    }

    public function test_authenticated_listing_by_page(): void {
        $this->assertTrue(true);
    }
}
