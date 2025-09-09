@extends('user.layout')

@section('title', 'Colleges')

@section('page-header')
@endsection

@section('page-title', 'Colleges')
@section('page-description', 'Manage and view college information')

@section('page-actions')
@can('create', App\Models\College::class)
<a href="{{ route('admin.colleges.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i>Add College
</a>
@endcan
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-university me-2"></i>Colleges List
                </h5>
            </div>
            <div class="card-body">
                <!-- Search and Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search colleges..." 
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                            <a href="{{ route('user.colleges.index') }}" class="btn btn-outline-secondary ms-1">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if($colleges->count() > 0)
                <!-- Colleges Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Coordinator</th>
                                <th>Academic Year</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($colleges as $college)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ strtoupper(substr($college->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $college->name }}</h6>
                                            @if($college->address)
                                            <small class="text-muted">{{ Str::limit($college->address, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $college->code }}</span>
                                </td>
                                <td>
                                    @if($college->coordinator)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie me-1 text-primary"></i>
                                        {{ $college->coordinator->first_name }} {{ $college->coordinator->last_name }}
                                    </div>
                                    @else
                                    <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($college->academicYear)
                                    <span class="badge {{ $college->academicYear->active ? 'bg-success' : 'bg-warning' }}">
                                        {{ $college->academicYear->label }}
                                    </span>
                                    @else
                                    <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($college->contact)
                                    <small class="text-muted">{{ $college->contact }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @can('view', $college)
                                        <a href="{{ route('user.colleges.show', $college) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('update', $college)
                                        <a href="{{ route('user.colleges.edit', $college) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $colleges->firstItem() ?? 0 }} to {{ $colleges->lastItem() ?? 0 }} 
                        of {{ $colleges->total() }} results
                    </div>
                    {{ $colleges->links() }}
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-university fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Colleges Found</h5>
                    <p class="text-muted mb-4">
                        @if(request('search'))
                            No colleges match your search criteria.
                        @else
                            No colleges are available for your role.
                        @endif
                    </p>
                    @if(request('search'))
                    <a href="{{ route('user.colleges.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Clear Search
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: 600;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endpush