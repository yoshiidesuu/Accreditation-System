@extends('layouts.accreditor')

@section('title', 'Review SWOT Entry')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">SWOT Entry Details</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">College</h6>
                            <p class="fw-bold">{{ $swotEntry->college->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Area</h6>
                            <p class="fw-bold">{{ $swotEntry->area->name }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Type</h6>
                            <span class="badge bg-{{ $swotEntry->type === 'S' ? 'success' : ($swotEntry->type === 'W' ? 'warning' : ($swotEntry->type === 'O' ? 'info' : 'danger')) }} fs-6">
                                {{ $swotEntry->getTypeName() }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <span class="badge bg-{{ $swotEntry->status === 'approved' ? 'success' : ($swotEntry->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                                {{ $swotEntry->getStatusName() }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Description</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $swotEntry->description }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Created By</h6>
                            <p>{{ $swotEntry->creator->name }}</p>
                            <small class="text-muted">{{ $swotEntry->created_at->format('M d, Y \\a\\t H:i') }}</small>
                        </div>
                        @if($swotEntry->reviewer)
                        <div class="col-md-6">
                            <h6 class="text-muted">Reviewed By</h6>
                            <p>{{ $swotEntry->reviewer->name }}</p>
                            <small class="text-muted">{{ $swotEntry->reviewed_at->format('M d, Y \\a\\t H:i') }}</small>
                        </div>
                        @endif
                    </div>

                    @if($swotEntry->notes)
                    <div class="mb-4">
                        <h6 class="text-muted">Review Notes</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $swotEntry->notes }}
                        </div>
                    </div>
                    @endif

                    @if($swotEntry->status === 'pending')
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject
                        </button>
                        <a href="{{ route('accreditor.swot-review.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Queue
                        </a>
                    </div>
                    @else
                    <div class="d-flex gap-2">
                        <a href="{{ route('accreditor.swot-review.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Queue
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- SWOT Guide -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">SWOT Analysis Guide</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="swotGuide">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#strengths">
                                    <span class="badge bg-success me-2">S</span> Strengths
                                </button>
                            </h2>
                            <div id="strengths" class="accordion-collapse collapse" data-bs-parent="#swotGuide">
                                <div class="accordion-body">
                                    <small>Internal positive factors that give advantages:</small>
                                    <ul class="small mt-2">
                                        <li>Strong faculty qualifications</li>
                                        <li>Modern facilities and equipment</li>
                                        <li>High student satisfaction</li>
                                        <li>Industry partnerships</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#weaknesses">
                                    <span class="badge bg-warning me-2">W</span> Weaknesses
                                </button>
                            </h2>
                            <div id="weaknesses" class="accordion-collapse collapse" data-bs-parent="#swotGuide">
                                <div class="accordion-body">
                                    <small>Internal negative factors that need improvement:</small>
                                    <ul class="small mt-2">
                                        <li>Limited research output</li>
                                        <li>Outdated curriculum</li>
                                        <li>Insufficient funding</li>
                                        <li>Low faculty-student ratio</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#opportunities">
                                    <span class="badge bg-info me-2">O</span> Opportunities
                                </button>
                            </h2>
                            <div id="opportunities" class="accordion-collapse collapse" data-bs-parent="#swotGuide">
                                <div class="accordion-body">
                                    <small>External positive factors to leverage:</small>
                                    <ul class="small mt-2">
                                        <li>Growing industry demand</li>
                                        <li>Government support programs</li>
                                        <li>Technology advancement</li>
                                        <li>International collaborations</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#threats">
                                    <span class="badge bg-danger me-2">T</span> Threats
                                </button>
                            </h2>
                            <div id="threats" class="accordion-collapse collapse" data-bs-parent="#swotGuide">
                                <div class="accordion-body">
                                    <small>External negative factors to address:</small>
                                    <ul class="small mt-2">
                                        <li>Increased competition</li>
                                        <li>Budget constraints</li>
                                        <li>Regulatory changes</li>
                                        <li>Economic downturns</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Created</h6>
                                <p class="mb-1">{{ $swotEntry->creator->name }}</p>
                                <small class="text-muted">{{ $swotEntry->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($swotEntry->reviewed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $swotEntry->status === 'approved' ? 'success' : 'danger' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ ucfirst($swotEntry->status) }}</h6>
                                <p class="mb-1">{{ $swotEntry->reviewer->name }}</p>
                                <small class="text-muted">{{ $swotEntry->reviewed_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve SWOT Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('accreditor.swot-review.approve', $swotEntry) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        You are about to approve this SWOT entry. This action will mark it as approved and notify the creator.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject SWOT Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('accreditor.swot-review.reject', $swotEntry) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        You are about to reject this SWOT entry. Please provide clear feedback to help the creator improve.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Please provide specific feedback on why this entry is being rejected..." required></textarea>
                        <div class="form-text">Be constructive and specific to help the creator understand what needs improvement.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-item:last-child .timeline-content {
    margin-bottom: 0;
}
</style>
@endpush