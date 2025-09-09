<?php

namespace Tests\Unit\Policies;

use App\Models\College;
use App\Models\User;
use App\Policies\CollegePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CollegePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected CollegePolicy $policy;
    protected User $admin;
    protected User $dean;
    protected User $overallCoordinator;
    protected User $chairperson;
    protected User $faculty;
    protected College $college;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new CollegePolicy();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'dean']);
        Role::create(['name' => 'overall_coordinator']);
        Role::create(['name' => 'chairperson']);
        Role::create(['name' => 'faculty']);
        
        // Create users with different roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->dean = User::factory()->create();
        $this->dean->assignRole('dean');
        
        $this->overallCoordinator = User::factory()->create();
        $this->overallCoordinator->assignRole('overall_coordinator');
        
        $this->chairperson = User::factory()->create();
        $this->chairperson->assignRole('chairperson');
        
        $this->faculty = User::factory()->create();
        $this->faculty->assignRole('faculty');
        
        $this->college = College::factory()->create();
    }

    public function test_view_any_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->viewAny($this->admin));
        $this->assertTrue($this->policy->viewAny($this->dean));
        $this->assertTrue($this->policy->viewAny($this->overallCoordinator));
        $this->assertTrue($this->policy->viewAny($this->chairperson));
        $this->assertTrue($this->policy->viewAny($this->faculty));
    }

    public function test_view_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->view($this->admin, $this->college));
        $this->assertTrue($this->policy->view($this->dean, $this->college));
        $this->assertTrue($this->policy->view($this->overallCoordinator, $this->college));
        $this->assertTrue($this->policy->view($this->chairperson, $this->college));
        $this->assertTrue($this->policy->view($this->faculty, $this->college));
    }

    public function test_create_allows_admin_and_overall_coordinator()
    {
        $this->assertTrue($this->policy->create($this->admin));
        $this->assertTrue($this->policy->create($this->overallCoordinator));
        
        $this->assertFalse($this->policy->create($this->dean));
        $this->assertFalse($this->policy->create($this->chairperson));
        $this->assertFalse($this->policy->create($this->faculty));
    }

    public function test_update_allows_admin_and_overall_coordinator()
    {
        $this->assertTrue($this->policy->update($this->admin, $this->college));
        $this->assertTrue($this->policy->update($this->overallCoordinator, $this->college));
        
        $this->assertFalse($this->policy->update($this->dean, $this->college));
        $this->assertFalse($this->policy->update($this->chairperson, $this->college));
        $this->assertFalse($this->policy->update($this->faculty, $this->college));
    }

    public function test_delete_allows_only_admin()
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->college));
        
        $this->assertFalse($this->policy->delete($this->dean, $this->college));
        $this->assertFalse($this->policy->delete($this->overallCoordinator, $this->college));
        $this->assertFalse($this->policy->delete($this->chairperson, $this->college));
        $this->assertFalse($this->policy->delete($this->faculty, $this->college));
    }

    public function test_restore_allows_only_admin()
    {
        $this->assertTrue($this->policy->restore($this->admin, $this->college));
        
        $this->assertFalse($this->policy->restore($this->dean, $this->college));
        $this->assertFalse($this->policy->restore($this->overallCoordinator, $this->college));
        $this->assertFalse($this->policy->restore($this->chairperson, $this->college));
        $this->assertFalse($this->policy->restore($this->faculty, $this->college));
    }

    public function test_force_delete_allows_only_admin()
    {
        $this->assertTrue($this->policy->forceDelete($this->admin, $this->college));
        
        $this->assertFalse($this->policy->forceDelete($this->dean, $this->college));
        $this->assertFalse($this->policy->forceDelete($this->overallCoordinator, $this->college));
        $this->assertFalse($this->policy->forceDelete($this->chairperson, $this->college));
        $this->assertFalse($this->policy->forceDelete($this->faculty, $this->college));
    }
}