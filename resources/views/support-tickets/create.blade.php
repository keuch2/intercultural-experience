@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('support-tickets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Support Center
        </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h3 class="mb-0">Create a New Support Ticket</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('support-tickets.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject *</label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                           id="subject" 
                           name="subject" 
                           value="{{ old('subject') }}" 
                           required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Message *</label>
                    <textarea class="form-control @error('message') is-invalid @enderror" 
                              id="message" 
                              name="message" 
                              rows="6" 
                              required>{{ old('message') }}</textarea>
                    <small class="text-muted">Please provide as much detail as possible so we can assist you better.</small>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                        <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General Inquiry</option>
                        <option value="application" {{ old('category') == 'application' ? 'selected' : '' }}>Application Help</option>
                        <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical Issue</option>
                        <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Billing Question</option>
                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }} selected>Medium</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input @error('notify_email') is-invalid @enderror" 
                           type="checkbox" 
                           id="notify_email" 
                           name="notify_email" 
                           value="1" 
                           {{ old('notify_email') ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_email">
                        Email me when there's an update to my ticket
                    </label>
                    @error('notify_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary me-md-2">Clear Form</button>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Before Submitting a Ticket</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-search me-2"></i> Search our Knowledge Base</h6>
                    <p class="small text-muted">
                        Check if your question has already been answered in our 
                        <a href="#">knowledge base articles</a>.
                    </p>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-clock me-2"></i> Response Time</h6>
                    <p class="small text-muted">
                        Our support team typically responds within 24 hours during business days.
                        For urgent matters, please mark your ticket as high priority.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection