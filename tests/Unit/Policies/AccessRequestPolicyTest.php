<?php

namespace Tests\Unit\Policies;

use App\Models\AccessRequest;
use App\Models\College;
use App\Models\Parameter;
use App\Models\ParameterContent;
use App\Models\User;
use App\Policies\AccessRequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected AccessRequestPolicy $policy;
    protected User $admin;
    protected User $dean;
    protected User $overallCoordinator;
    protected User $faculty;
    protected User $requester;
    protected College $college1;
    protected College $college2;
    protected ParameterContent $parameterContent;
    protected AccessRequest $accessRequest;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new AccessRequestPolicy();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'dean']);
        Role::create(['name' => 'overall_coordinator']);
        Role::create(['name' => 'faculty']);
        
        // Create colleges
        $this->college1 = College::factory()->create();
        $this->college2 = College::factory()->create();
        
        // Create users with different roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->dean = User::factory()->create(['college_id' => $this->college1->id]);
        $this->dean->assignRole('dean');
        
        $this->overallCoordinator = User::factory()->create();
        $this->overallCoordinator->assignRole('overall_coordinator');
        
        $this->faculty = User::factory()->create(['college_id' => $this->college1->id]);
        $this->faculty->assignRole('faculty');
        
        $this->requester = User::factory()->create(['college_id' => $this->college2->id]);
        $this->requester->assignRole('faculty');
        
        // Create parameter content and access request
        $parameter = Parameter::factory()->create();
        $this->parameterContent = ParameterContent::factory()->create([
            'parameter_id' => $parameter->id,
            'uploaded_by' => $this->faculty->id,
            'college_id' => $this->college1->id,
        ]);
        
        $this->accessRequest = AccessRequest::factory()->create([
            'requester_id' => $this->requester->id,
            'file_id' => $this->parameterContent->id,
            'status' => 'pending',
        ]);
    }

    public function test_view_any_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->viewAny($this->admin));
        $this->assertTrue($this->policy->viewAny($this->dean));
        $this->assertTrue($this->policy->viewAny($this->faculty));
        $this->assertTrue($this->policy->viewAny($this->requester));
    }

    public function test_admin_can_view_all_requests()
    {
        $this->assertTrue($this->policy->view($this->admin, $this->accessRequest));
    }

    public function test_overall_coordinator_can_view_all_requests()
    {
        $this->assertTrue($this->policy->view($this->overallCoordinator, $this->accessRequest));
    }

    public function test_dean_can_view_requests_for_files_in_their_college()
    {
        $this->assertTrue($this->policy->view($this->dean, $this->accessRequest));
        
        // Create request for file from different college
        $otherCollegeContent = ParameterContent::factory()->create([
            'parameter_id' => Parameter::factory()->create()->id,
            'uploaded_by' => User::factory()->create(['college_id' => $this->college2->id])->id,
            'college_id' => $this->college2->id,
        ]);
        
        $otherRequest = AccessRequest::factory()->create([
            'requester_id' => $this->requester->id,
            'file_id' => $otherCollegeContent->id,
            'status' => 'pending',
        ]);
        
        $this->assertFalse($this->policy->view($this->dean, $otherRequest));
    }

    public function test_requester_can_view_their_own_requests()
    {
        $this->assertTrue($this->policy->view($this->requester, $this->accessRequest));
    }

    public function test_file_owner_can_view_requests_for_their_files()
    {
        $this->assertTrue($this->policy->view($this->faculty, $this->accessRequest));
    }

    public function test_unrelated_user_cannot_view_request()
    {
        $unrelatedUser = User::factory()->create(['college_id' => $this->college2->id]);
        $unrelatedUser->assignRole('faculty');
        
        $this->assertFalse($this->policy->view($unrelatedUser, $this->accessRequest));
    }

    public function test_all_authenticated_users_can_create_requests()
    {
        $this->assertTrue($this->policy->create($this->admin));
        $this->assertTrue($this->policy->create($this->dean));
        $this->assertTrue($this->policy->create($this->faculty));
        $this->assertTrue($this->policy->create($this->requester));
    }

    public function test_requester_can_update_their_own_pending_requests()
    {
        $this->assertTrue($this->policy->update($this->requester, $this->accessRequest));
    }

    public function test_requester_cannot_update_non_pending_requests()
    {
        $this->accessRequest->update(['status' => 'approved']);
        $this->assertFalse($this->policy->update($this->requester, $this->accessRequest));
        
        $this->accessRequest->update(['status' => 'rejected']);
        $this->assertFalse($this->policy->update($this->requester, $this->accessRequest));
    }

    public function test_non_requester_cannot_update_request()
    {
        $this->assertFalse($this->policy->update($this->faculty, $this->accessRequest));
        $this->assertFalse($this->policy->update($this->dean, $this->accessRequest));
    }

    public function test_admin_can_delete_any_request()
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->accessRequest));
    }

    public function test_requester_can_delete_their_own_pending_requests()
    {
        $this->assertTrue($this->policy->delete($this->requester, $this->accessRequest));
    }

    public function test_requester_cannot_delete_non_pending_requests()
    {
        $this->accessRequest->update(['status' => 'approved']);
        $this->assertFalse($this->policy->delete($this->requester, $this->accessRequest));
    }

    public function test_non_requester_cannot_delete_request()
    {
        $this->assertFalse($this->policy->delete($this->faculty, $this->accessRequest));
        $this->assertFalse($this->policy->delete($this->dean, $this->accessRequest));
    }

    public function test_requester_cannot_approve_their_own_request()
    {
        $this->assertFalse($this->policy->approve($this->requester, $this->accessRequest));
    }

    public function test_cannot_approve_non_pending_requests()
    {
        $this->accessRequest->update(['status' => 'approved']);
        $this->assertFalse($this->policy->approve($this->admin, $this->accessRequest));
    }

    public function test_admin_can_approve_requests()
    {
        $this->assertTrue($this->policy->approve($this->admin, $this->accessRequest));
    }

    public function test_overall_coordinator_can_approve_requests()
    {
        $this->assertTrue($this->policy->approve($this->overallCoordinator, $this->accessRequest));
    }

    public function test_file_owner_can_approve_requests_for_their_files()
    {
        $this->assertTrue($this->policy->approve($this->faculty, $this->accessRequest));
    }

    public function test_dean_can_approve_requests_for_files_in_their_college()
    {
        $this->assertTrue($this->policy->approve($this->dean, $this->accessRequest));
    }

    public function test_unrelated_user_cannot_approve_request()
    {
        $unrelatedUser = User::factory()->create(['college_id' => $this->college2->id]);
        $unrelatedUser->assignRole('faculty');
        
        $this->assertFalse($this->policy->approve($unrelatedUser, $this->accessRequest));
    }

    public function test_reject_has_same_logic_as_approve()
    {
        // Test a few key scenarios to ensure reject mirrors approve logic
        $this->assertTrue($this->policy->reject($this->admin, $this->accessRequest));
        $this->assertTrue($this->policy->reject($this->faculty, $this->accessRequest));
        $this->assertFalse($this->policy->reject($this->requester, $this->accessRequest));
    }

    public function test_only_admin_can_restore_requests()
    {
        $this->assertTrue($this->policy->restore($this->admin, $this->accessRequest));
        
        $this->assertFalse($this->policy->restore($this->dean, $this->accessRequest));
        $this->assertFalse($this->policy->restore($this->faculty, $this->accessRequest));
        $this->assertFalse($this->policy->restore($this->requester, $this->accessRequest));
    }

    public function test_only_admin_can_force_delete_requests()
    {
        $this->assertTrue($this->policy->forceDelete($this->admin, $this->accessRequest));
        
        $this->assertFalse($this->policy->forceDelete($this->dean, $this->accessRequest));
        $this->assertFalse($this->policy->forceDelete($this->faculty, $this->accessRequest));
        $this->assertFalse($this->policy->forceDelete($this->requester, $this->accessRequest));
    }
}