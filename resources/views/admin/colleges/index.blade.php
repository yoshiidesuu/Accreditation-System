@extends('layouts.admin')

@section('title', 'Colleges Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Colleges Management</h3>
                    <a href="{{ route('admin.colleges.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New College
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.colleges.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name, code, address, or coordinator..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        @if(request('search'))
                            <div class="mt-2">
                                <a href="{{ route('admin.colleges.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear Search
                                </a>
                            </div>
                        @endif
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($colleges->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Coordinator</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($colleges as $college)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $college->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $college->name }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ Str::limit($college->address, 50) ?: 'N/A' }}
                                                </small>
                                            </td>
                                            <td>
                                                {{ $college->contact ?: 'N/A' }}
                                            </td>
                                            <td>
                                                @if($college->coordinator)
                                                    <span class="badge bg-success">
                                                        {{ $college->coordinator->name }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">Not Assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $college->created_at->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.colleges.show', $college) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.colleges.edit', $college) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" onclick="confirmDelete({{ $college->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Hidden delete form -->
                                                <form id="delete-form-{{ $college->id }}" 
                                                      action="{{ route('admin.colleges.destroy', $college) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $colleges->firstItem() }} to {{ $colleges->lastItem() }} 
                                    of {{ $colleges->total() }} results
                                </small>
                            </div>
                            <div>
                                {{ $colleges->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-university fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No colleges found</h5>
                            <p class="text-muted">Start by creating your first college.</p>
                            <a href="{{ route('admin.colleges.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New College
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(collegeId) {
    if (confirm('Are you sure you want to delete this college? This action cannot be undone.')) {
        document.getElementById('delete-form-' + collegeId).submit();
    }
}
</script>
@endsection