@extends('layouts.admin')

@section('title', 'Edit User Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Role Management</a></li>
                    <li class="breadcrumb-item active">Edit Role</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Edit User Role: {{ $user->first_name }} {{ $user->last_name }}
                    </h3>
                </div>

                <div class="card-body">
                    <!-- User Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">User Information</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Employee ID:</strong></div>
                                        <div class="col-sm-8"><code>{{ $user->employee_id }}</code></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Name:</strong></div>
                                        <div class="col-sm-8">{{ $user->first_name }} {{ $user->last_name }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Email:</strong></div>
                                        <div class="col-sm-8">{{ $user->email }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Current Role:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'dean' ? 'warning' : 'info') }}">
                                                {{ $availableRoles[$user->role] ?? ucfirst($user->role) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Status:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'secondary' : 'danger') }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($user->college)
                                    <div class="row">
                                        <div class="col-sm-4"><strong>College:</strong></div>
                                        <div class="col-sm-8">{{ $user->college->name }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Role Descriptions</h6>
                                    <div class="role-descriptions">
                                        <div class="mb-2">
                                            <strong>Administrator:</strong> Full system access and management
                                        </div>
                                        <div class="mb-2">
                                            <strong>Dean:</strong> College-level management and oversight
                                        </div>
                                        <div class="mb-2">
                                            <strong>Overall Coordinator:</strong> Cross-college coordination
                                        </div>
                                        <div class="mb-2">
                                            <strong>Chairperson:</strong> Department-level management
                                        </div>
                                        <div class="mb-2">
                                            <strong>Faculty:</strong> Teaching and content management
                                        </div>
                                        <div class="mb-2">
                                            <strong>Accreditor Lead:</strong> Lead accreditation processes
                                        </div>
                                        <div class="mb-2">
                                            <strong>Accreditor Member:</strong> Participate in accreditation
                                        </div>
                                        <div class="mb-2">
                                            <strong>Staff:</strong> General staff access
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Update Form -->
                    <form method="POST" action="{{ route('admin.roles.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">New Role <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Select a role...</option>
                                        @foreach($availableRoles as $roleKey => $roleLabel)
                                            <option value="{{ $roleKey }}" 
                                                {{ old('role', $user->role) == $roleKey ? 'selected' : '' }}
                                                {{ $roleKey === $user->role ? 'data-current="true"' : '' }}>
                                                {{ $roleLabel }}
                                                {{ $roleKey === $user->role ? ' (Current)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Select the new role for this user. This will determine their access level and permissions.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Permissions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Additional Permissions (Optional)</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="manage_users" id="perm_manage_users">
                                                <label class="form-check-label" for="perm_manage_users">
                                                    Manage Users
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="manage_colleges" id="perm_manage_colleges">
                                                <label class="form-check-label" for="perm_manage_colleges">
                                                    Manage Colleges
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="manage_areas" id="perm_manage_areas">
                                                <label class="form-check-label" for="perm_manage_areas">
                                                    Manage Areas
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="manage_parameters" id="perm_manage_parameters">
                                                <label class="form-check-label" for="perm_manage_parameters">
                                                    Manage Parameters
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="approve_content" id="perm_approve_content">
                                                <label class="form-check-label" for="perm_approve_content">
                                                    Approve Content
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_reports" id="perm_view_reports">
                                                <label class="form-check-label" for="perm_view_reports">
                                                    View Reports
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="export_data" id="perm_export_data">
                                                <label class="form-check-label" for="perm_export_data">
                                                    Export Data
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="audit_logs" id="perm_audit_logs">
                                                <label class="form-check-label" for="perm_audit_logs">
                                                    Access Audit Logs
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="system_settings" id="perm_system_settings">
                                                <label class="form-check-label" for="perm_system_settings">
                                                    System Settings
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        Additional permissions beyond the base role. These are optional and role-specific permissions will take precedence.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Alert -->
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Changing a user's role will immediately affect their access to system features. 
                            Make sure you understand the implications before proceeding.
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Role Management
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-danger me-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i>
                                    Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="updateRoleBtn">
                                    <i class="fas fa-save me-1"></i>
                                    Update Role
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Check current permissions
    const currentPermissions = @json(json_decode($user->permissions ?? '[]'));
    if (currentPermissions && currentPermissions.length > 0) {
        currentPermissions.forEach(function(permission) {
            $(`input[name="permissions[]"][value="${permission}"]`).prop('checked', true);
        });
    }

    // Role change warning
    $('#role').change(function() {
        const currentRole = $(this).find('option[data-current="true"]').val();
        const newRole = $(this).val();
        
        if (newRole && newRole !== currentRole) {
            $('#updateRoleBtn').removeClass('btn-primary').addClass('btn-warning');
            $('#updateRoleBtn').html('<i class="fas fa-exclamation-triangle me-1"></i> Update Role (Changed)');
        } else {
            $('#updateRoleBtn').removeClass('btn-warning').addClass('btn-primary');
            $('#updateRoleBtn').html('<i class="fas fa-save me-1"></i> Update Role');
        }
    });

    // Form submission confirmation
    $('form').submit(function(e) {
        const currentRole = $('#role').find('option[data-current="true"]').text().replace(' (Current)', '');
        const newRole = $('#role option:selected').text();
        
        if (currentRole !== newRole) {
            if (!confirm(`Are you sure you want to change this user's role from "${currentRole}" to "${newRole}"?\n\nThis action will immediately affect their system access.`)) {
                e.preventDefault();
                return false;
            }
        }
    });
});

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.querySelector('form').reset();
        
        // Reset role to current
        const currentRoleOption = document.querySelector('option[data-current="true"]');
        if (currentRoleOption) {
            currentRoleOption.selected = true;
        }
        
        // Reset permissions
        const currentPermissions = @json(json_decode($user->permissions ?? '[]'));
        document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
            checkbox.checked = currentPermissions.includes(checkbox.value);
        });
        
        // Reset button
        $('#updateRoleBtn').removeClass('btn-warning').addClass('btn-primary');
        $('#updateRoleBtn').html('<i class="fas fa-save me-1"></i> Update Role');
    }
}
</script>
@endpush

@push('styles')
<style>
.role-descriptions {
    font-size: 0.875rem;
}

.role-descriptions div {
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.role-descriptions div:last-child {
    border-bottom: none;
}

.form-check {
    margin-bottom: 0.5rem;
}

.alert {
    border-left: 4px solid #ffc107;
}
</style>
@endpush
@endsection