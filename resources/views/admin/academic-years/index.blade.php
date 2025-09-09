@extends('layouts.admin')

@section('title', 'Academic Years Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Academic Years Management</h3>
                    <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Academic Year
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           value="{{ request('search') }}" placeholder="Search academic years...">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="current" {{ request('status') === 'current' ? 'selected' : '' }}>Current</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($academicYears->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Label</th>
                                        <th>Date Range</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($academicYears as $academicYear)
                                        <tr>
                                            <td>
                                                <strong>{{ $academicYear->label }}</strong>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ $academicYear->date_range }}</span>
                                                    <small class="text-muted">
                                                        {{ $academicYear->start_date->diffInDays($academicYear->end_date) }} days
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $academicYear->status_badge }}">
                                                    {{ $academicYear->status_text }}
                                                </span>
                                                @if($academicYear->isCurrent() && !$academicYear->active)
                                                    <br><small class="text-warning">Date-based current</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ $academicYear->created_at->format('M d, Y') }}</span>
                                                    <small class="text-muted">{{ $academicYear->created_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.academic-years.show', $academicYear) }}" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.academic-years.edit', $academicYear) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <!-- Toggle Active Status -->
                                                    <form action="{{ route('admin.academic-years.toggle-active', $academicYear) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $academicYear->active ? 'btn-secondary' : 'btn-success' }}" 
                                                                title="{{ $academicYear->active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas {{ $academicYear->active ? 'fa-pause' : 'fa-play' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    @unless($academicYear->active)
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal{{ $academicYear->id }}" 
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endunless
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing {{ $academicYears->firstItem() }} to {{ $academicYears->lastItem() }} 
                                    of {{ $academicYears->total() }} results
                                </p>
                            </div>
                            <div>
                                {{ $academicYears->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Academic Years Found</h5>
                            <p class="text-muted">{{ request('search') ? 'No academic years match your search criteria.' : 'Start by creating your first academic year.' }}</p>
                            <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Academic Year
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($academicYears as $academicYear)
    @unless($academicYear->active)
        <div class="modal fade" id="deleteModal{{ $academicYear->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the academic year <strong>{{ $academicYear->label }}</strong>?</p>
                        <p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.academic-years.destroy', $academicYear) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Academic Year</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endunless
@endforeach

<style>
.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style>
@endsection