@extends('layouts.admin')

@section('title', 'Activity Log Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Activity Log #{{ $activityLog->id }}</h3>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Logs
                    </a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">ID:</dt>
                                        <dd class="col-sm-8">{{ $activityLog->id }}</dd>

                                        <dt class="col-sm-4">User:</dt>
                                        <dd class="col-sm-8">
                                            @if($activityLog->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-primary">
                                                            {{ strtoupper(substr($activityLog->user->first_name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $activityLog->user->name }}</div>
                                                        <small class="text-muted">{{ $activityLog->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Description:</dt>
                                        <dd class="col-sm-8">{{ $activityLog->description }}</dd>

                                        <dt class="col-sm-4">Action:</dt>
                                        <dd class="col-sm-8">
                                            @if($activityLog->action)
                                                <span class="badge badge-{{ $activityLog->getActionBadgeColor() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $activityLog->action)) }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Category:</dt>
                                        <dd class="col-sm-8">
                                            @if($activityLog->category)
                                                <span class="badge badge-outline-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $activityLog->category)) }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Date/Time:</dt>
                                        <dd class="col-sm-8">
                                            {{ $activityLog->created_at->format('F d, Y \\a\\t H:i:s') }}<br>
                                            <small class="text-muted">({{ $activityLog->created_at->diffForHumans() }})</small>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Request Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Request Information</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">IP Address:</dt>
                                        <dd class="col-sm-8">
                                            <code>{{ $activityLog->ip_address ?? 'N/A' }}</code>
                                        </dd>

                                        <dt class="col-sm-4">User Agent:</dt>
                                        <dd class="col-sm-8">
                                            @if($activityLog->user_agent)
                                                <div class="text-break small" style="max-width: 300px;">
                                                    {{ $activityLog->user_agent }}
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        @if($activityLog->model_type && $activityLog->model_id)
                                            <dt class="col-sm-4">Related Model:</dt>
                                            <dd class="col-sm-8">
                                                <div>
                                                    <strong>Type:</strong> {{ class_basename($activityLog->model_type) }}<br>
                                                    <strong>ID:</strong> {{ $activityLog->model_id }}
                                                </div>
                                            </dd>
                                        @endif

                                        @if($activityLog->batch_uuid)
                                            <dt class="col-sm-4">Batch UUID:</dt>
                                            <dd class="col-sm-8">
                                                <code class="small">{{ $activityLog->batch_uuid }}</code>
                                            </dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    @if($activityLog->meta && count($activityLog->meta) > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Metadata</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 200px;">Key</th>
                                                        <th>Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($activityLog->meta as $key => $value)
                                                        <tr>
                                                            <td><code>{{ $key }}</code></td>
                                                            <td>
                                                                @if(is_array($value) || is_object($value))
                                                                    <pre class="bg-light p-2 rounded small">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                @elseif(is_bool($value))
                                                                    <span class="badge badge-{{ $value ? 'success' : 'danger' }}">
                                                                        {{ $value ? 'true' : 'false' }}
                                                                    </span>
                                                                @elseif(is_null($value))
                                                                    <span class="text-muted">null</span>
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Related Activities -->
                    @if($activityLog->batch_uuid)
                        @php
                            $relatedLogs = \App\Models\ActivityLog::where('batch_uuid', $activityLog->batch_uuid)
                                ->where('id', '!=', $activityLog->id)
                                ->with('user')
                                ->orderBy('created_at')
                                ->get();
                        @endphp

                        @if($relatedLogs->count() > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Related Activities (Same Batch)</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>User</th>
                                                            <th>Description</th>
                                                            <th>Action</th>
                                                            <th>Time</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($relatedLogs as $relatedLog)
                                                            <tr>
                                                                <td>{{ $relatedLog->id }}</td>
                                                                <td>
                                                                    @if($relatedLog->user)
                                                                        {{ $relatedLog->user->name }}
                                                                    @else
                                                                        <span class="text-muted">System</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ Str::limit($relatedLog->description, 50) }}</td>
                                                                <td>
                                                                    @if($relatedLog->action)
                                                                        <span class="badge badge-{{ $relatedLog->getActionBadgeColor() }} badge-sm">
                                                                            {{ ucfirst(str_replace('_', ' ', $relatedLog->action)) }}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $relatedLog->created_at->format('H:i:s') }}</td>
                                                                <td>
                                                                    <a href="{{ route('admin.activity-logs.show', $relatedLog) }}" 
                                                                       class="btn btn-sm btn-outline-primary">
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
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar {
    width: 32px;
    height: 32px;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.badge-outline-secondary {
    color: #6c757d;
    border: 1px solid #6c757d;
    background: transparent;
}

.badge-sm {
    font-size: 0.75em;
}

pre {
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endpush