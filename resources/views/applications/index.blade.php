@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">My Applications</h1>
    
    @if($applications->isEmpty())
        <div class="alert alert-info">
            <p>You haven't applied to any programs yet.</p>
            <a href="{{ route('programs.index') }}" class="btn btn-primary mt-2">Browse Programs</a>
        </div>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Your Applications</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('programs.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus-circle me-1"></i> Apply to New Program
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Program</th>
                                <th>Country</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Documents</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr>
                                    <td>
                                        <a href="{{ route('programs.show', $application->program->id) }}">
                                            {{ $application->program->name }}
                                        </a>
                                    </td>
                                    <td>{{ $application->program->country }}</td>
                                    <td>{{ $application->applied_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($application->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($application->status == 'in_review')
                                            <span class="badge bg-info">In Review</span>
                                        @elseif($application->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($application->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif($application->status == 'completed')
                                            <span class="badge bg-primary">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $application->documents->count() }}/{{ $application->program->required_documents_count ?? '?' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('applications.show', $application->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            {{ $applications->links() }}
        </div>
    @endif
    
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Application Process</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px">
                        <i class="fas fa-search fa-2x text-primary"></i>
                    </div>
                    <h5 class="mt-3">1. Find a Program</h5>
                    <p class="small text-muted">Browse through available programs</p>
                </div>
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px">
                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="mt-3">2. Apply</h5>
                    <p class="small text-muted">Submit your application</p>
                </div>
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px">
                        <i class="fas fa-clipboard-check fa-2x text-primary"></i>
                    </div>
                    <h5 class="mt-3">3. Review</h5>
                    <p class="small text-muted">We'll review your application</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px">
                        <i class="fas fa-plane-departure fa-2x text-primary"></i>
                    </div>
                    <h5 class="mt-3">4. Travel</h5>
                    <p class="small text-muted">Embark on your journey</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection