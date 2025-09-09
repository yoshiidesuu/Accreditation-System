@extends('layouts.admin')

@section('title', 'Edit College')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Edit College: {{ $college->name }}</h3>
                    <div>
                        <a href="{{ route('admin.colleges.show', $college) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.colleges.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Colleges
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.colleges.update', $college) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">College Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $college->name) }}" 
                                           placeholder="Enter college name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">College Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $college->code) }}" 
                                           placeholder="Enter college code (e.g., CCS, COE)" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Unique identifier for the college (max 10 characters)</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact" class="form-label">Contact Information</label>
                                    <input type="text" class="form-control @error('contact') is-invalid @enderror" 
                                           id="contact" name="contact" value="{{ old('contact', $college->contact) }}" 
                                           placeholder="Phone, email, or other contact info">
                                    @error('contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="coordinator_id" class="form-label">Overall Coordinator</label>
                                    <select class="form-select @error('coordinator_id') is-invalid @enderror" 
                                            id="coordinator_id" name="coordinator_id">
                                        <option value="">Select a coordinator (optional)</option>
                                        @foreach($coordinators as $coordinator)
                                            <option value="{{ $coordinator->id }}" 
                                                    {{ old('coordinator_id', $college->coordinator_id) == $coordinator->id ? 'selected' : '' }}>
                                                {{ $coordinator->name }} ({{ $coordinator->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('coordinator_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Assign an overall coordinator to manage this college</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" 
                                      placeholder="Enter college address">{{ old('address', $college->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Created</label>
                                    <div class="form-control-plaintext">{{ $college->created_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Updated</label>
                                    <div class="form-control-plaintext">{{ $college->updated_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.colleges.show', $college) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update College
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection