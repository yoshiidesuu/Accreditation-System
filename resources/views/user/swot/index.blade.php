@extends('layouts.user')

@section('title', 'SWOT Analysis')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">SWOT Analysis</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">SWOT Analysis</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('user.swot.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add SWOT Entry
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.swot.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="college_id" class="form-label">College</label>
                        <select name="college_id" id="college_id" class="form-select">
                            <option value="">All Colleges</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                    {{ $college->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="area_id" class="form-label">Area</label>
                        <select name="area_id" id="area_id" class="form-select">
                            <option value="">All Areas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="S" {{ request('type') == 'S' ? 'selected' : '' }}>Strengths</option>
                            <option value="W" {{ request('type') == 'W' ? 'selected' : '' }}>Weaknesses</option>
                            <option value="O" {{ request('type') == 'O' ? 'selected' : '' }}>Opportunities</option>
                            <option value="T" {{ request('type') == 'T' ? 'selected' : '' }}>Threats</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('user.swot.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SWOT Entries -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">SWOT Entries ({{ $swotEntries->total() }})</h6>
        </div>
        <div class="card-body">
            @if($swotEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>College</th>
                                <th>Area</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($swotEntries as $entry)
                                <tr>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'S' => 'success',
                                                'W' => 'warning', 
                                                'O' => 'info',
                                                'T' => 'danger'
                                            ];
                                            $typeNames = [
                                                'S' => 'Strength',
                                                'W' => 'Weakness',
                                                'O' => 'Opportunity', 
                                                'T' => 'Threat'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$entry->type] }}">
                                            {{ $typeNames[$entry->type] }}
                                        </span>
                                    </td>
                                    <td>{{ $entry->college->name }}</td>
                                    <td>{{ $entry->area->name }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="{{ $entry->description }}">
                                            {{ $entry->description }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$entry->status] }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $entry->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('user.swot.show', $entry) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $entry)
                                                <a href="{{ route('user.swot.edit', $entry) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $entry)
                                                <form method="POST" action="{{ route('user.swot.destroy', $entry) }}" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this SWOT entry?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $swotEntries->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No SWOT entries found</h5>
                    <p class="text-muted">Start by creating your first SWOT analysis entry.</p>
                    <a href="{{ route('user.swot.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create SWOT Entry
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection