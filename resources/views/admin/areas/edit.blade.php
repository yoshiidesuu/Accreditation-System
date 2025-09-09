@extends('layouts.admin')

@section('title', 'Edit Area')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Edit Area: {{ $area->title }}</h3>
                    <div>
                        <a href="{{ route('admin.areas.show', $area) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Current Status -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Current Status:</strong><br>
                                <span class="badge bg-primary">{{ $area->code }}</span>
                                <span class="badge bg-secondary">{{ $area->college->name }}</span>
                                <span class="badge bg-info">{{ $area->academicYear->label }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Hierarchy:</strong><br>
                                <span class="text-muted">{{ $area->full_path }}</span><br>
                                <small class="text-muted">Depth Level: {{ $area->depth_level }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('admin.areas.update', $area) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                                    <select name="college_id" id="college_id" class="form-select @error('college_id') is-invalid @enderror" required>
                                        <option value="">Select College</option>
                                        @foreach($colleges as $college)
                                            <option value="{{ $college->id }}" {{ (old('college_id', $area->college_id) == $college->id) ? 'selected' : '' }}>
                                                {{ $college->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('college_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ (old('academic_year_id', $area->academic_year_id) == $year->id) ? 'selected' : '' }}>
                                                {{ $year->label }}
                                                @if($year->active)
                                                    <span class="badge bg-success">Active</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Area Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code', $area->code) }}" placeholder="e.g., AREA001" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Unique identifier for the area</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="parent_area_id" class="form-label">Parent Area</label>
                                    <select name="parent_area_id" id="parent_area_id" class="form-select @error('parent_area_id') is-invalid @enderror">
                                        <option value="">No Parent (Root Area)</option>
                                        @foreach($parentAreas as $parentArea)
                                            <option value="{{ $parentArea->id }}" {{ (old('parent_area_id', $area->parent_area_id) == $parentArea->id) ? 'selected' : '' }}>
                                                {{ $parentArea->title }} ({{ $parentArea->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_area_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty to make this a root area</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Area Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $area->title) }}" placeholder="Enter area title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Enter area description (optional)">{{ old('description', $area->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Child Areas Warning -->
                        @if($area->hasChildren())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This area has {{ $area->childAreas->count() }} child area(s). 
                            Changing the college or academic year will affect all child areas.
                        </div>
                        @endif
                        
                        <!-- Parameters Warning -->
                        @if($area->parameters->count() > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> This area has {{ $area->parameters->count() }} parameter(s) associated with it.
                        </div>
                        @endif
                        
                        <!-- Preview Section -->
                        <div id="preview-section" class="mb-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-eye"></i> Preview Changes</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>College:</strong> <span id="preview-college">{{ $area->college->name }}</span><br>
                                            <strong>Academic Year:</strong> <span id="preview-year">{{ $area->academicYear->label }}</span><br>
                                            <strong>Parent Area:</strong> <span id="preview-parent">{{ $area->parentArea ? $area->parentArea->title : 'Root Area' }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Code:</strong> <code id="preview-code">{{ $area->code }}</code><br>
                                            <strong>Title:</strong> <span id="preview-title">{{ $area->title }}</span><br>
                                            <strong>New Hierarchy:</strong> <span id="preview-hierarchy">{{ $area->full_path }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <a href="{{ route('admin.areas.show', $area) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> View Area
                                </a>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Area
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const collegeSelect = document.getElementById('college_id');
    const yearSelect = document.getElementById('academic_year_id');
    const parentSelect = document.getElementById('parent_area_id');
    const codeInput = document.getElementById('code');
    const titleInput = document.getElementById('title');
    
    const originalCollegeId = '{{ $area->college_id }}';
    const originalYearId = '{{ $area->academic_year_id }}';
    const originalParentId = '{{ $area->parent_area_id ?? "" }}';
    const currentAreaId = '{{ $area->id }}';
    
    // Load parent areas when college or academic year changes
    function loadParentAreas() {
        const collegeId = collegeSelect.value;
        const yearId = yearSelect.value;
        
        if (collegeId && yearId) {
            fetch(`/admin/areas/get-by-college-year?college_id=${collegeId}&academic_year_id=${yearId}&exclude_id=${currentAreaId}`)
                .then(response => response.json())
                .then(data => {
                    const currentParentId = parentSelect.value;
                    parentSelect.innerHTML = '<option value="">No Parent (Root Area)</option>';
                    
                    data.forEach(area => {
                        // Exclude current area and its descendants to prevent circular references
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = `${area.title} (${area.code})`;
                        if (area.id == currentParentId || area.id == originalParentId) {
                            option.selected = true;
                        }
                        parentSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading parent areas:', error);
                    parentSelect.innerHTML = '<option value="">No Parent (Root Area)</option>';
                });
        } else {
            parentSelect.innerHTML = '<option value="">No Parent (Root Area)</option>';
        }
        
        updatePreview();
    }
    
    // Update preview
    function updatePreview() {
        const collegeText = collegeSelect.options[collegeSelect.selectedIndex]?.text || '{{ $area->college->name }}';
        const yearText = yearSelect.options[yearSelect.selectedIndex]?.text || '{{ $area->academicYear->label }}';
        const parentText = parentSelect.options[parentSelect.selectedIndex]?.text || 'Root Area';
        const code = codeInput.value || '{{ $area->code }}';
        const title = titleInput.value || '{{ $area->title }}';
        
        document.getElementById('preview-college').textContent = collegeText;
        document.getElementById('preview-year').textContent = yearText;
        document.getElementById('preview-parent').textContent = parentText;
        document.getElementById('preview-code').textContent = code;
        document.getElementById('preview-title').textContent = title;
        
        // Build hierarchy preview
        let hierarchy = title;
        if (parentText !== 'Root Area' && parentText !== 'No Parent (Root Area)') {
            const parentTitle = parentText.split(' (')[0];
            hierarchy = `${parentTitle} > ${title}`;
        }
        document.getElementById('preview-hierarchy').textContent = hierarchy;
    }
    
    // Event listeners
    collegeSelect.addEventListener('change', loadParentAreas);
    yearSelect.addEventListener('change', loadParentAreas);
    parentSelect.addEventListener('change', updatePreview);
    titleInput.addEventListener('input', updatePreview);
    codeInput.addEventListener('input', updatePreview);
    
    // Initial load - only if college/year changed from original
    if (collegeSelect.value !== originalCollegeId || yearSelect.value !== originalYearId) {
        loadParentAreas();
    }
    updatePreview();
});
</script>
@endsection