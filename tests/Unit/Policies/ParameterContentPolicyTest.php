<?php

namespace Tests\Unit\Policies;

use App\Models\College;
use App\Models\Parameter;
use App\Models\ParameterContent;
use App\Models\User;
use App\Policies\ParameterContentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParameterContentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ParameterContentPolicy $policy;
    protected User $admin;
    protected User $dean;
    protected User $overallCoordinator;
    protected User $chairperson;
    protected User $faculty;
    protected User $accreditorLead;
    protected User $accreditorMember;
    protected College $college1;
    protected College $college2;
    protected ParameterContent $parameterContent;
    protected ParameterContent $otherCollegeContent;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new ParameterContentPolicy();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'dean']);
        Role::create(['name' => 'overall_coordinator']);
        Role::create(['name' => 'chairperson']);
        Role::create(['name' => 'faculty']);
        Role::create(['name' => 'accreditor_lead']);
        Role::create(['name' => 'accreditor_member']);
        
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
        
        $this->chairperson = User::factory()->create(['college_id' => $this->college1->id]);
        $this->chairperson->assignRole('chairperson');
        
        $this->faculty = User::factory()->create(['college_id' => $this->college1->id]);
        $this->faculty->assignRole('faculty');
        
        $this->accreditorLead = User::factory()->create();
        $this->accreditorLead->assignRole('accreditor_lead');
        
        $this->accreditorMember = User::factory()->create();
        $this->accreditorMember->assignRole('accreditor_member');
        
        // Create parameter content
        $parameter = Parameter::factory()->create();
        $this->parameterContent = ParameterContent::factory()->create([
            'parameter_id' => $parameter->id,
            'uploaded_by' => $this->faculty->id,
            'college_id' => $this->college1->id,
        ]);
        
        $this->otherCollegeContent = ParameterContent::factory()->create([
            'parameter_id' => $parameter->id,
            'uploaded_by' => User::factory()->create(['college_id' => $this->college2->id])->id,
            'college_id' => $this->college2->id,
        ]);
    }

    public function test_view_any_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->viewAny($this->admin));
        $this->assertTrue($this->policy->viewAny($this->dean));
        $this->assertTrue($this->policy->viewAny($this->faculty));
    }

    public function test_admin_can_view_all_content()
    {
        $this->assertTrue($this->policy->view($this->admin, $this->parameterContent));
        $this->assertTrue($this->policy->view($this->admin, $this->otherCollegeContent));
    }

    public function test_overall_coordinator_can_view_all_content()
    {
        $this->assertTrue($this->policy->view($this->overallCoordinator, $this->parameterContent));
        $this->assertTrue($this->policy->view($this->overallCoordinator, $this->otherCollegeContent));
    }

    public function test_dean_can_view_content_from_their_college()
    {
        $this->assertTrue($this->policy->view($this->dean, $this->parameterContent));
        $this->assertFalse($this->policy->view($this->dean, $this->otherCollegeContent));
    }

    public function test_owner_can_view_their_own_content()
    {
        $this->assertTrue($this->policy->view($this->faculty, $this->parameterContent));
    }

    public function test_chairperson_can_view_content_from_their_college()
    {
        $this->assertTrue($this->policy->view($this->chairperson, $this->parameterContent));
        $this->assertFalse($this->policy->view($this->chairperson, $this->otherCollegeContent));
    }

    public function test_create_allows_admin_chairperson_and_faculty()
    {
        $this->assertTrue($this->policy->create($this->admin));
        $this->assertTrue($this->policy->create($this->chairperson));
        $this->assertTrue($this->policy->create($this->faculty));
        
        $this->assertFalse($this->policy->create($this->dean));
        $this->assertFalse($this->policy->create($this->accreditorLead));
    }

    public function test_admin_can_update_all_content()
    {
        $this->assertTrue($this->policy->update($this->admin, $this->parameterContent));
        $this->assertTrue($this->policy->update($this->admin, $this->otherCollegeContent));
    }

    public function test_owner_can_update_their_own_content()
    {
        $this->assertTrue($this->policy->update($this->faculty, $this->parameterContent));
    }

    public function test_chairperson_can_update_content_from_their_college()
    {
        $this->assertTrue($this->policy->update($this->chairperson, $this->parameterContent));
        $this->assertFalse($this->policy->update($this->chairperson, $this->otherCollegeContent));
    }

    public function test_non_owner_cannot_update_content_from_other_college()
    {
        $this->assertFalse($this->policy->update($this->faculty, $this->otherCollegeContent));
    }

    public function test_admin_can_delete_all_content()
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->parameterContent));
        $this->assertTrue($this->policy->delete($this->admin, $this->otherCollegeContent));
    }

    public function test_owner_can_delete_their_own_content()
    {
        $this->assertTrue($this->policy->delete($this->faculty, $this->parameterContent));
    }

    public function test_non_owner_cannot_delete_content()
    {
        $this->assertFalse($this->policy->delete($this->chairperson, $this->parameterContent));
        $this->assertFalse($this->policy->delete($this->dean, $this->parameterContent));
    }

    public function test_cannot_request_access_to_own_content()
    {
        $this->assertFalse($this->policy->requestAccess($this->faculty, $this->parameterContent));
    }

    public function test_admin_and_coordinator_do_not_need_to_request_access()
    {
        $this->assertFalse($this->policy->requestAccess($this->admin, $this->otherCollegeContent));
        $this->assertFalse($this->policy->requestAccess($this->overallCoordinator, $this->otherCollegeContent));
    }

    public function test_dean_can_request_access_to_content_outside_their_college()
    {
        $this->assertTrue($this->policy->requestAccess($this->dean, $this->otherCollegeContent));
        $this->assertFalse($this->policy->requestAccess($this->dean, $this->parameterContent));
    }

    public function test_chairperson_can_request_access_to_content_outside_their_college()
    {
        $this->assertTrue($this->policy->requestAccess($this->chairperson, $this->otherCollegeContent));
    }

    public function test_faculty_can_request_access_to_content_they_do_not_own()
    {
        $this->assertTrue($this->policy->requestAccess($this->faculty, $this->otherCollegeContent));
    }

    public function test_admin_can_approve_all_access()
    {
        $this->assertTrue($this->policy->approveAccess($this->admin, $this->parameterContent));
        $this->assertTrue($this->policy->approveAccess($this->admin, $this->otherCollegeContent));
    }

    public function test_overall_coordinator_can_approve_all_access()
    {
        $this->assertTrue($this->policy->approveAccess($this->overallCoordinator, $this->parameterContent));
        $this->assertTrue($this->policy->approveAccess($this->overallCoordinator, $this->otherCollegeContent));
    }

    public function test_owner_can_approve_access_to_their_content()
    {
        $this->assertTrue($this->policy->approveAccess($this->faculty, $this->parameterContent));
    }

    public function test_non_owner_cannot_approve_access_to_others_content()
    {
        $this->assertFalse($this->policy->approveAccess($this->faculty, $this->otherCollegeContent));
        $this->assertFalse($this->policy->approveAccess($this->chairperson, $this->otherCollegeContent));
    }

    public function test_download_requires_view_permission_first()
    {
        // Faculty can download their own content
        $this->assertTrue($this->policy->download($this->faculty, $this->parameterContent));
        
        // Faculty cannot download content from other college without view permission
        $this->assertFalse($this->policy->download($this->faculty, $this->otherCollegeContent));
    }

    public function test_download_with_permission_requirements()
    {
        // Set content to require permission but not granted
        $this->parameterContent->update([
            'requires_permission' => true,
            'permission_status' => 'requested'
        ]);
        
        // Admin can still download
        $this->assertTrue($this->policy->download($this->admin, $this->parameterContent));
        
        // Overall coordinator can still download
        $this->assertTrue($this->policy->download($this->overallCoordinator, $this->parameterContent));
        
        // Owner can still download
        $this->assertTrue($this->policy->download($this->faculty, $this->parameterContent));
        
        // Others cannot download without granted permission
        $this->assertFalse($this->policy->download($this->chairperson, $this->parameterContent));
    }
}