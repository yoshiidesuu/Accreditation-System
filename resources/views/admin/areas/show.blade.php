@extends('layouts.admin')

@section('title', 'Area Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ $area->title }}</h3>
                    <div>
                        <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Code:</strong></td>
                                    <td><code class="bg-light px-2 py-1 rounded">{{ $area->code }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $area->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>College:</strong></td>
                                    <td>
                                        <span class="badge bg-primary">{{ $area->college->name }}</span>
                                        <small class="text-muted">({{ $area->college->code }})</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Academic Year:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $area->academicYear->label }}</span>
                                        @if($area->academicYear->active)
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Parent Area:</strong></td>
                                    <td>
                                        @if($area->parentArea)
                                            <a href="{{ route('admin.areas.show', $area->parentArea) }}" class="text-decoration-none">
                                                {{ $area->parentArea->title }}
                                                <small class="text-muted">({{ $area->parentArea->code }})</small>
                                            </a>
                                        @else
                                            <span class="badge bg-secondary">Root Area</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Hierarchy Level:</strong></td>
                                    <td>
                                        <span class="badge bg-dark">Level {{ $area->depth_level }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Full Path:</strong></td>
                                    <td><small class="text-muted">{{ $area->full_path }}</small></td>
                                </tr>
                                <tr>
                                    <td><strong>Child Areas:</strong></td>
                                    <td>
                                        <span class="badge bg-success">{{ $area->childAreas->count() }}</span>
                                        @if($area->hasChildren())
                                            <small class="text-muted">areas</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($area->description)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <div class="bg-light p-3 rounded mt-2">
                            {{ $area->description }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Child Areas -->
            @if($area->childAreas->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sitemap"></i> Child Areas ({{ $area->childAreas->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Children</th>
                                    <th>Parameters</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($area->childAreas as $child)
                                <tr>
                                    <td><code>{{ $child->code }}</code></td>
                                    <td>{{ $child->title }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $child->childAreas->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $child->parameters->count() }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.areas.show', $child) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.areas.edit', $child) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Parameters -->
            @if($area->parameters->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-ul"></i> Parameters ({{ $area->parameters->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($area->parameters as $parameter)
                                <tr>
                                    <td><code>{{ $parameter->code }}</code></td>
                                    <td>{{ $parameter->title }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($parameter->type) }}</span>
                                    </td>
                                    <td>
                                        @if($parameter->required)
                                            <span class="badge bg-danger">Required</span>
                                        @else
                                            <span class="badge bg-success">Optional</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.parameters.show', $parameter) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.areas.create', ['college_id' => $area->college_id, 'academic_year_id' => $area->academic_year_id, 'parent_area_id' => $area->id]) }}" 
                           class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Child Area
                        </a>
                        
                        <a href="{{ route('admin.parameters.create', ['area_id' => $area->id]) }}" 
                           class="btn btn-info">
                            <i class="fas fa-list-ul"></i> Add Parameter
                        </a>
                        
                        <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Area
                        </a>
                        
                        <hr>
                        
                        @if(!$area->hasChildren() && $area->parameters->count() == 0)
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete Area
                        </button>
                        @else
                        <button type="button" class="btn btn-danger" disabled title="Cannot delete area with child areas or parameters">
                            <i class="fas fa-trash"></i> Delete Area
                        </button>
                        <small class="text-muted">Cannot delete area with child areas or parameters</small>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> System Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>
                                <small>{{ $area->created_at->format('M d, Y H:i') }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td>
                                <small>{{ $area->updated_at->format('M d, Y H:i') }}</small>
                            </td>
                        </tr>
                        @if($area->deleted_at)
                        <tr>
                            <td><strong>Deleted:</strong></td>
                            <td>
                                <small class="text-danger">{{ $area->deleted_at->format('M d, Y H:i') }}</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="text-primary mb-0">{{ $area->childAreas->count() }}</h4>
                                <small class="text-muted">Child Areas</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="text-info mb-0">{{ $area->parameters->count() }}</h4>
                                <small class="text-muted">Parameters</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($area->hasChildren())
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0">{{ $area->descendants()->count() }}</h4>
                                    <small class="text-muted">Total Descendants</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if(!$area->hasChildren() && $area->parameters->count() == 0)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                
                <p>Are you sure you want to delete the area <strong>"{{ $area->title }}"</strong>?</p>
                
                <div class="bg-light p-3 rounded">
                    <strong>Area Details:</strong><br>
                    <strong>Code:</strong> {{ $area->code }}<br>
                    <strong>College:</strong> {{ $area->college->name }}<br>
                    <strong>Academic Year:</strong> {{ $area->academicYear->label }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Area
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection