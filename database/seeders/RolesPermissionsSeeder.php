<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on the permission matrix
        $permissions = [
            // College CRUD
            'colleges.view',
            'colleges.create',
            'colleges.edit',
            'colleges.delete',
            
            // Academic Year
            'academic_years.view',
            'academic_years.create',
            'academic_years.edit',
            'academic_years.delete',
            
            // Area Level
            'areas.view',
            'areas.create',
            'areas.edit',
            'areas.delete',
            
            // Parameter
            'parameters.view',
            'parameters.create',
            'parameters.edit',
            'parameters.delete',
            
            // Parameter Content
            'parameter_contents.view',
            'parameter_contents.view_own',
            'parameter_contents.view_tagged',
            'parameter_contents.create',
            'parameter_contents.edit',
            'parameter_contents.edit_own',
            'parameter_contents.delete',
            'parameter_contents.request_access',
            
            // Request Access
            'access_requests.create',
            'access_requests.approve',
            'access_requests.reject',
            'access_requests.assign',
            
            // Tag Colleges / Assign Accreditors
            'college_assignments.view',
            'college_assignments.create',
            'college_assignments.edit',
            
            // SWOT
            'swot.view',
            'swot.create',
            'swot.edit',
            'swot.delete',
            
            // Area Ranking
            'area_rankings.view',
            'area_rankings.create',
            'area_rankings.edit',
            
            // Admin Settings
            'admin_settings.view',
            'admin_settings.create',
            'admin_settings.edit',
            'admin_settings.delete',
            
            // Reports
            'reports.view',
            'reports.export',
            
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $deanRole = Role::create(['name' => 'dean']);
        $accreditorLeadRole = Role::create(['name' => 'accreditor_lead']);
        $accreditorMemberRole = Role::create(['name' => 'accreditor_member']);
        $chairpersonRole = Role::create(['name' => 'chairperson']);
        $facultyRole = Role::create(['name' => 'faculty']);
        $overallCoordRole = Role::create(['name' => 'overall_coordinator']);

        // Assign permissions to roles based on permission matrix
        
        // Admin - Full access to everything
        $adminRole->givePermissionTo(Permission::all());
        
        // Dean - View access to most features
        $deanRole->givePermissionTo([
            'colleges.view',
            'academic_years.view',
            'areas.view',
            'parameters.view',
            'parameter_contents.view',
            'swot.view',
            'area_rankings.view',
            'reports.view',
        ]);
        
        // Accreditor Lead - View access + tagged content access + request creation
        $accreditorLeadRole->givePermissionTo([
            'colleges.view',
            'academic_years.view',
            'areas.view',
            'parameters.view',
            'parameter_contents.view',
            'parameter_contents.view_tagged',
            'parameter_contents.request_access',
            'access_requests.create',
            'college_assignments.view',
            'swot.view',
            'area_rankings.view',
        ]);
        
        // Accreditor Member - Similar to Lead but more limited
        $accreditorMemberRole->givePermissionTo([
            'colleges.view',
            'academic_years.view',
            'areas.view',
            'parameters.view',
            'parameter_contents.view',
            'parameter_contents.view_tagged',
            'parameter_contents.request_access',
            'access_requests.create',
            'college_assignments.view',
            'swot.view',
            'area_rankings.view',
        ]);
        
        // Chairperson - College-specific management + area management
        $chairpersonRole->givePermissionTo([
            'colleges.view',
            'academic_years.view',
            'areas.view',
            'areas.create',
            'areas.edit',
            'parameters.view',
            'parameter_contents.view',
            'parameter_contents.create',
            'parameter_contents.edit_own',
            'parameter_contents.request_access',
            'access_requests.create',
            'swot.view',
            'swot.create',
            'swot.edit',
            'area_rankings.view',
        ]);
        
        // Faculty - Content creation and management
        $facultyRole->givePermissionTo([
            'colleges.view',
            'academic_years.view',
            'areas.view',
            'parameters.view',
            'parameter_contents.view',
            'parameter_contents.create',
            'parameter_contents.edit',
            'parameter_contents.edit_own',
            'parameter_contents.delete',
            'access_requests.create',
            'swot.view',
            'swot.create',
            'swot.edit',
            'swot.delete',
            'area_rankings.view',
        ]);
        
        // Overall Coordinator - Management and approval capabilities
        $overallCoordRole->givePermissionTo([
            'colleges.view',
            'colleges.create',
            'colleges.edit',
            'academic_years.view',
            'areas.view',
            'parameters.view',
            'parameter_contents.view',
            'access_requests.approve',
            'access_requests.assign',
            'college_assignments.view',
            'college_assignments.create',
            'college_assignments.edit',
            'swot.view',
            'area_rankings.view',
            'reports.view',
        ]);
    }
}
