@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                <h1 class="card-title mb-0">{{ $program->name }}</h1>
                <div class="badge bg-{{ $program->is_active ? 'success' : 'secondary' }} mt-2 mt-sm-0">
                    {{ $program->is_active ? 'Active' : 'Inactive' }}
                </div>
            </div>
            <h5 class="text-muted">{{ $program->country }}</h5>
            <div class="mb-3">
                <span class="badge bg-info">{{ $program->category }}</span>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-8">
                    <h4>Description</h4>
                    <div class="mb-4">
                        <p>{{ $program->description }}</p>
                    </div>
                    
                    @auth
                        <div class="mt-4">
                            <h4>Ready to apply?</h4>
                            <p>Submit your application for this program and embark on a life-changing journey.</p>
                            <a href="{{ route('applications.create', ['program_id' => $program->id]) }}" 
                               class="btn btn-primary btn-lg">
                                Apply Now
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p><strong>Interested in this program?</strong></p>
                            <p>Please <a href="{{ route('login') }}">login</a> or <a href="{{ route('register') }}">register</a> to apply for this program.</p>
                        </div>
                    @endauth
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Program Details</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> {{ $program->country }}
                                </li>
                                <li class="mb-3">
                                    <strong><i class="fas fa-tag me-2"></i> Category:</strong> {{ $program->category }}
                                </li>
                                <li>
                                    <strong><i class="fas fa-calendar-alt me-2"></i> Created:</strong> {{ $program->created_at->format('M d, Y') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <h3 class="mb-3">Other Programs You Might Like</h3>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach($relatedPrograms as $relatedProgram)
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $relatedProgram->name }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">{{ $relatedProgram->country }}</h6>
                        <p class="card-text">{{ Str::limit($relatedProgram->description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="{{ route('programs.show', $relatedProgram->id) }}" class="btn btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection