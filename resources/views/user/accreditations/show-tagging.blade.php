@extends('layouts.user')

@section('title', 'Accreditation Tagging')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Accreditation Tagging</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    @hasrole('overall_coordinator')
                        <li class="breadcrumb-item"><a href="{{ route('user.accreditations.coordinatorTagging') }}">Coordinator Tagging</a></li>
                    @endhasrole
                    <li class="breadcrumb-item active">{{ $accreditation->title }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group" role="group">
            @hasrole('overall_coordinator')
                <a href="{{ route('user.accreditations.coordinatorTagging') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Tagging
                </a>
                <a href="{{ route('user.accreditations.assignAccreditors', $accreditation) }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Assign Accreditors
                </a>
            @endhasrole
        </div>
    </div>

    <!-- Accreditation Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Accreditation Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title:</label>
                        <p class="mb-0">{{ $accreditation->title }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">College:</label>
                        <p class="mb-0">{{ $accreditation->college->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Academic Year:</label>
                        <p class="mb-0">{{ $accreditation->academicYear->label ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status:</label>
                        <span class="badge bg-{{ 
                            $accreditation->status === 'approved' ? 'success' : 
                            ($accreditation->status === 'rejected' ? 'danger' : 
                            ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lead Accreditor:</label>
                        <p class="mb-0">
                            @if($accreditation->assigned_lead_id)
                                {{ $accreditation->assignedLead->name ?? 'Unknown' }}
                                <br><small class="text-muted">{{ $accreditation->assignedLead->email ?? 'N/A' }}</small>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Member Accreditors:</label>
                        @if($accreditation->assigned_members && count(json_decode($accreditation->assigned_members, true) ?? []) > 0)
                            @php
                                $memberIds = is_string($accreditation->assigned_members) ? 
                                    json_decode($accreditation->assigned_members, true) : 
                                    $accreditation->assigned_members;
                                $members = $users->whereIn('id', $memberIds ?? []);
                            @endphp
                            @foreach($members as $member)
                                <span class="badge bg-secondary me-1">{{ $member->name }}</span>
                            @endforeach
                        @else
                            <p class="mb-0"><span class="text-muted">No members assigned</span></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parameter Content Tagging -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Parameter Content Tags</h6>
            <div class="d-flex align-items-center">
                <span class="badge bg-info me-2">{{ $taggedContents->count() }} Tagged</span>
                <span class="badge bg-secondary">{{ $availableContents->count() }} Available</span>
            </div>
        </div>
        <div class="card-body">
            @if($availableContents->count() > 0)
                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="contentSearch" placeholder="Search parameter content...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="areaFilter">
                            <option value="">All Areas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="tagged">Tagged</option>
                            <option value="available">Available</option>
                        </select>
                    </div>
                </div>

                <!-- Content List -->
                <div class="row" id="contentList">
                    @foreach($availableContents as $content)
                        @php
                            $isTagged = $taggedContents->contains('parameter_content_id', $content->id);
                        @endphp
                        <div class="col-md-6 mb-3 content-item" 
                             data-area-id="{{ $content->parameter->area_id }}" 
                             data-status="{{ $isTagged ? 'tagged' : 'available' }}">
                            <div class="card h-100 {{ $isTagged ? 'border-success' : 'border-light' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">{{ $content->parameter->title }}</h6>
                                        @if($isTagged)
                                            <span class="badge bg-success"><i class="fas fa-tag"></i> Tagged</span>
                                        @endif
                                    </div>
                                    
                                    <p class="card-text text-muted small mb-2">
                                        <strong>Area:</strong> {{ $content->parameter->area->title ?? 'N/A' }}
                                    </p>
                                    
                                    @if($content->content)
                                        <p class="card-text">{{ Str::limit(strip_tags($content->content), 100) }}</p>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            By: {{ $content->user->name ?? 'Unknown' }}
                                        </small>
                                        
                                        @hasrole('overall_coordinator')
                                            @if($isTagged)
                                                <button type="button" class="btn btn-sm btn-outline-danger untag-btn" 
                                                        data-content-id="{{ $content->id }}">
                                                    <i class="fas fa-minus"></i> Untag
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-success tag-btn" 
                                                        data-content-id="{{ $content->id }}">
                                                    <i class="fas fa-plus"></i> Tag
                                                </button>
                                            @endif
                                        @endhasrole
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($availableContents->count() === 0)
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No parameter content available</h5>
                        <p class="text-muted">Parameter content will appear here once created by faculty members.</p>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No parameter content available</h5>
                    <p class="text-muted">Parameter content will appear here once created by faculty members.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#contentSearch').on('keyup', function() {
        filterContent();
    });
    
    $('#clearSearch').on('click', function() {
        $('#contentSearch').val('');
        filterContent();
    });
    
    // Filter functionality
    $('#areaFilter, #statusFilter').on('change', function() {
        filterContent();
    });
    
    function filterContent() {
        const searchTerm = $('#contentSearch').val().toLowerCase();
        const areaFilter = $('#areaFilter').val();
        const statusFilter = $('#statusFilter').val();
        
        $('.content-item').each(function() {
            const $item = $(this);
            const title = $item.find('.card-title').text().toLowerCase();
            const content = $item.find('.card-text').text().toLowerCase();
            const areaId = $item.data('area-id');
            const status = $item.data('status');
            
            let show = true;
            
            // Search filter
            if (searchTerm && !title.includes(searchTerm) && !content.includes(searchTerm)) {
                show = false;
            }
            
            // Area filter
            if (areaFilter && areaId != areaFilter) {
                show = false;
            }
            
            // Status filter
            if (statusFilter && status !== statusFilter) {
                show = false;
            }
            
            $item.toggle(show);
        });
    }
    
    // Tag/Untag functionality
    $('.tag-btn').on('click', function() {
        const contentId = $(this).data('content-id');
        const $button = $(this);
        const $card = $button.closest('.card');
        
        $.ajax({
            url: '{{ route("user.accreditations.tagContent", $accreditation) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                parameter_content_id: contentId
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    $card.addClass('border-success');
                    $card.find('.card-title').after('<span class="badge bg-success"><i class="fas fa-tag"></i> Tagged</span>');
                    $button.removeClass('btn-outline-success tag-btn')
                           .addClass('btn-outline-danger untag-btn')
                           .html('<i class="fas fa-minus"></i> Untag')
                           .data('content-id', contentId);
                    
                    // Update data attribute
                    $button.closest('.content-item').data('status', 'tagged');
                    
                    // Update counters
                    updateCounters();
                    
                    // Show success message
                    showAlert('success', 'Parameter content tagged successfully!');
                } else {
                    showAlert('danger', response.message || 'Failed to tag content.');
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred while tagging content.');
            }
        });
    });
    
    $(document).on('click', '.untag-btn', function() {
        const contentId = $(this).data('content-id');
        const $button = $(this);
        const $card = $button.closest('.card');
        
        $.ajax({
            url: '{{ route("user.accreditations.untagContent", $accreditation) }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                parameter_content_id: contentId
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    $card.removeClass('border-success').addClass('border-light');
                    $card.find('.badge.bg-success').remove();
                    $button.removeClass('btn-outline-danger untag-btn')
                           .addClass('btn-outline-success tag-btn')
                           .html('<i class="fas fa-plus"></i> Tag')
                           .data('content-id', contentId);
                    
                    // Update data attribute
                    $button.closest('.content-item').data('status', 'available');
                    
                    // Update counters
                    updateCounters();
                    
                    // Show success message
                    showAlert('success', 'Parameter content untagged successfully!');
                } else {
                    showAlert('danger', response.message || 'Failed to untag content.');
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred while untagging content.');
            }
        });
    });
    
    function updateCounters() {
        const taggedCount = $('.content-item[data-status="tagged"]').length;
        const availableCount = $('.content-item').length;
        
        $('.badge.bg-info').text(taggedCount + ' Tagged');
        $('.badge.bg-secondary').text(availableCount + ' Available');
    }
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top
        $('.container-fluid').prepend(alertHtml);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 3000);
    }
});
</script>
@endpush