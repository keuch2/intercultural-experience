@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to My Applications
        </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h3 class="mb-0">Apply for Program: {{ $program->name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('applications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="program_id" value="{{ $program->id }}">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card mb-4 mb-md-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Program Details</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Program:</strong> {{ $program->name }}</p>
                                <p><strong>Country:</strong> {{ $program->country }}</p>
                                <p><strong>Category:</strong> {{ $program->category }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Your Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                                
                                @if(empty(Auth::user()->nationality) || empty(Auth::user()->birth_date) || empty(Auth::user()->phone))
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Some of your profile information is incomplete. 
                                        <a href="{{ route('profile.edit') }}">Update your profile</a> 
                                        to ensure a smooth application process.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4 class="mb-4">Application Questions</h4>
                
                <div class="mb-3">
                    <label for="motivation" class="form-label">Why are you interested in this program? *</label>
                    <textarea class="form-control @error('motivation') is-invalid @enderror" 
                              id="motivation" 
                              name="motivation" 
                              rows="4" 
                              required>{{ old('motivation') }}</textarea>
                    @error('motivation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="experience" class="form-label">Describe any relevant experience you have *</label>
                    <textarea class="form-control @error('experience') is-invalid @enderror" 
                              id="experience" 
                              name="experience" 
                              rows="4" 
                              required>{{ old('experience') }}</textarea>
                    @error('experience')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="goals" class="form-label">What do you hope to achieve through this program? *</label>
                    <textarea class="form-control @error('goals') is-invalid @enderror" 
                              id="goals" 
                              name="goals" 
                              rows="4" 
                              required>{{ old('goals') }}</textarea>
                    @error('goals')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <h4 class="mb-4 mt-5">Required Documents</h4>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Upload all required documents. You can add more documents later if needed.
                </div>
                
                <div class="mb-3">
                    <label for="resume" class="form-label">Resume/CV (PDF) *</label>
                    <input class="form-control @error('documents.resume') is-invalid @enderror" 
                           type="file" 
                           id="resume" 
                           name="documents[resume]" 
                           accept=".pdf" 
                           required>
                    <small class="text-muted">Max file size: 2MB</small>
                    @error('documents.resume')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="id_proof" class="form-label">ID Proof/Passport (PDF or Image) *</label>
                    <input class="form-control @error('documents.id_proof') is-invalid @enderror" 
                           type="file" 
                           id="id_proof" 
                           name="documents[id_proof]" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           required>
                    <small class="text-muted">Max file size: 2MB</small>
                    @error('documents.id_proof')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="additional" class="form-label">Additional Documents (Optional)</label>
                    <input class="form-control @error('documents.additional') is-invalid @enderror" 
                           type="file" 
                           id="additional" 
                           name="documents[additional]" 
                           accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Max file size: 2MB</small>
                    @error('documents.additional')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input @error('agreement') is-invalid @enderror" id="agreement" name="agreement" required>
                    <label class="form-check-label" for="agreement">
                        I confirm that all information provided is accurate and I agree to the <a href="#">terms and conditions</a> *
                    </label>
                    @error('agreement')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="{{ route('programs.show', $program->id) }}" class="btn btn-outline-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection