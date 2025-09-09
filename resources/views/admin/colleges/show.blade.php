@extends('layouts.admin')

@section('title', 'College Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">College Details: {{ $college->name }}</h3>
                    <div>
                        <a href="{{ route('admin.colleges.edit', $college) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.colleges.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Colleges
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Basic Information</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Name:</strong></div>
                                            <div class="col-sm-8">{{ $college->name }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Code:</strong></div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-primary">{{ $college->code }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Contact:</strong></div>
                                            <div class="col-sm-8">{{ $college->contact ?: 'Not specified' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Address:</strong></div>
                                            <div class="col-sm-8">{{ $college->address ?: 'Not specified' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Coordinator Information</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        @if($college->coordinator)
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar-circle me-3">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $college->coordinator->name }}</h6>
                                                    <small class="text-muted">{{ $college->coordinator->email }}</small>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Role:</strong></div>
                                                <div class="col-sm-8">
                                                    @foreach($college->coordinator->roles as $role)
                                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-user-slash fa-2x mb-2"></i>
                                                <p class="mb-0">No coordinator assigned</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">System Information</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Created:</strong></div>
                                            <div class="col-sm-8">{{ $college->created_at->format('M d, Y h:i A') }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Last Updated:</strong></div>
                                            <div class="col-sm-8">{{ $college->updated_at->format('M d, Y h:i A') }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>ID:</strong></div>
                                            <div class="col-sm-8"><code>{{ $college->id }}</code></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Associated Users</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        @if($college->users->count() > 0)
                                            <div class="list-group list-group-flush">
                                                @foreach($college->users->take(5) as $user)
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <div>
                                                            <h6 class="mb-1">{{ $user->name }}</h6>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                        </div>
                                                        <div>
                                                            @foreach($user->roles as $role)
                                                                <span class="badge bg-secondary">{{ $role->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($college->users->count() > 5)
                                                <div class="text-center mt-2">
                                                    <small class="text-muted">And {{ $college->users->count() - 5 }} more users...</small>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-users-slash fa-2x mb-2"></i>
                                                <p class="mb-0">No users associated</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.colleges.edit', $college) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit College
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete College
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the college <strong>{{ $college->name }}</strong>?</p>
                <p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.colleges.destroy', $college) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete College</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background-color: #6c757d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
</style>
@endsection