<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccreditationSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the application returns a successful response.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test that the offline page is accessible.
     */
    public function test_offline_page_is_accessible(): void
    {
        $response = $this->get('/offline');
        $response->assertStatus(200);
        $response->assertSee('Accreditation System');
        $response->assertSee('You are currently offline');
    }

    /**
     * Test that the database seeder creates the admin user.
     */
    public function test_database_seeder_creates_admin_user(): void
    {
        $this->seed();
        
        $admin = User::where('email', 'admin@earist.edu.ph')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('System Administrator', $admin->name);
        $this->assertEquals('admin', $admin->role);
        $this->assertTrue(Hash::check('AdminPass2024!', $admin->password));
    }

    /**
     * Test that the database seeder creates coordinator and faculty users.
     */
    public function test_database_seeder_creates_sample_users(): void
    {
        $this->seed();
        
        $coordinator = User::where('email', 'coordinator@earist.edu.ph')->first();
        $this->assertNotNull($coordinator);
        $this->assertEquals('coordinator', $coordinator->role);
        
        $faculty = User::where('email', 'faculty@earist.edu.ph')->first();
        $this->assertNotNull($faculty);
        $this->assertEquals('faculty', $faculty->role);
    }

    /**
     * Test that PWA manifest is accessible.
     */
    public function test_pwa_manifest_is_accessible(): void
    {
        $response = $this->get('/manifest.json');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $manifest = json_decode($response->getContent(), true);
        $this->assertEquals('Accreditation System', $manifest['name']);
        $this->assertEquals('AccredSys', $manifest['short_name']);
    }

    /**
     * Test that service worker is accessible.
     */
    public function test_service_worker_is_accessible(): void
    {
        $response = $this->get('/sw.js');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript');
    }
}