@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to My Applications
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Application: {{ $application->program->name }}</h3>
                <span class="badge bg-{{ 
                    $application->status == 'pending' ? 'warning' : 
                    ($application->status == 'in_review' ? 'info' : 
                    ($application->status == 'approved' ? 'success' : 
                    ($application->status == 'rejected' ? 'danger' : 'primary'))) 
                }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h4>Program Details</h4>
                    <div class="card">
                        <div class="card-body">
                            <p><strong><i class="fas fa-graduation-cap me-2"></i>Program:</strong> {{ $application->program->name }}</p>
                            <p><strong><i class="fas fa-map-marker-alt me-2"></i>Country:</strong> {{ $application->program->country }}</p>
                            <p><strong><i class="fas fa-tag me-2"></i>Category:</strong> {{ $application->program->category }}</p>
                            <p><strong><i class="fas fa-calendar-alt me-2"></i>Applied On:</strong> {{ $application->applied_at ? $application->applied_at->format('M d, Y') : 'N/A' }}</p>
                            @if($application->completed_at)
                                <p><strong><i class="fas fa-check-circle me-2"></i>Completed On:</strong> {{ $application->completed_at ? $application->completed_at->format('M d, Y') : 'N/A' }}</p>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('programs.show', $application->program->id) }}" class="btn btn-sm btn-outline-primary">
                                    View Program
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <h4>Application Status</h4>
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column h-100">
                                <div class="mb-3">
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar {{ 
                                            $application->status == 'approved' || $application->status == 'completed' ? 'bg-success' : 
                                            ($application->status == 'rejected' ? 'bg-danger' : 'progress-bar-striped progress-bar-animated') }}"
                                            role="progressbar"
                                            style="width: {{ 
                                                $application->status == 'pending' ? '25%' : 
                                                ($application->status == 'in_review' ? '50%' : 
                                                ($application->status == 'approved' || $application->status == 'rejected' ? '75%' : 
                                                ($application->status == 'completed' ? '100%' : '0%'))) 
                                            }};"
                                            aria-valuenow="{{ 
                                                $application->status == 'pending' ? '25' : 
                                                ($application->status == 'in_review' ? '50' : 
                                                ($application->status == 'approved' || $application->status == 'rejected' ? '75' : 
                                                ($application->status == 'completed' ? '100' : '0'))) 
                                            }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ 
                                                $application->status == 'pending' ? 'Application Submitted' : 
                                                ($application->status == 'in_review' ? 'Under Review' : 
                                                ($application->status == 'approved' ? 'Approved' : 
                                                ($application->status == 'rejected' ? 'Rejected' : 
                                                ($application->status == 'completed' ? 'Completed' : '')))) 
                                            }}
                                        </div>
                                    </div>
                                </div>
                            
                                <ul class="list-group list-group-flush mb-3 flex-grow-1">
                                    <li class="list-group-item {{ $application->status != 'pending' ? 'bg-light' : '' }}">
                                        <i class="fas {{ $application->status != 'pending' ? 'fa-check-circle text-success' : 'fa-circle' }} me-2"></i> 
                                        <strong>Application Submitted</strong>
                                        <p class="small text-muted mb-0">{{ $application->applied_at->format('M d, Y') }}</p>
                                    </li>
                                    <li class="list-group-item {{ in_array($application->status, ['in_review', 'approved', 'rejected', 'completed']) ? 'bg-light' : '' }}">
                                        <i class="fas {{ in_array($application->status, ['in_review', 'approved', 'rejected', 'completed']) ? 'fa-check-circle text-success' : 'fa-circle' }} me-2"></i> 
                                        <strong>Under Review</strong>
                                        @if(in_array($application->status, ['in_review', 'approved', 'rejected', 'completed']))
                                            <p class="small text-muted mb-0">Documents being verified</p>
                                        @endif
                                    </li>
                                    <li class="list-group-item {{ in_array($application->status, ['approved', 'completed']) ? 'bg-light' : ($application->status == 'rejected' ? 'bg-danger bg-opacity-10' : '') }}">
                                        <i class="fas {{ in_array($application->status, ['approved', 'completed']) ? 'fa-check-circle text-success' : ($application->status == 'rejected' ? 'fa-times-circle text-danger' : 'fa-circle') }} me-2"></i> 
                                        <strong>{{ $application->status == 'rejected' ? 'Application Rejected' : 'Application Decision' }}</strong>
                                        @if($application->status == 'rejected')
                                            <p class="small text-muted mb-0">Thank you for your interest</p>
                                        @endif
                                    </li>
                                    <li class="list-group-item {{ $application->status == 'completed' ? 'bg-light' : '' }}">
                                        <i class="fas {{ $application->status == 'completed' ? 'fa-check-circle text-success' : 'fa-circle' }} me-2"></i> 
                                        <strong>Program Completed</strong>
                                        @if($application->status == 'completed' && $application->completed_at)
                                            <p class="small text-muted mb-0">{{ $application->completed_at->format('M d, Y') }}</p>
                                        @endif
                                    </li>
                                </ul>
                                
                                @if($application->status == 'approved')
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i> 
                                        <strong>Congratulations!</strong> Your application has been approved. 
                                        Please check your email for next steps.
                                    </div>
                                @elseif($application->status == 'rejected')
                                    <div class="alert alert-danger">
                                        <i class="fas fa-times-circle me-2"></i> 
                                        <strong>We're sorry.</strong> Your application was not selected for this program.
                                        Please check other available programs that might be a better fit.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Submitted Documents</h4>
                        @if($application->status == 'pending')
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                <i class="fas fa-upload me-1"></i> Upload Document
                            </button>
                        @endif
                    </div>
                    
                    @if($application->documents->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> No documents have been uploaded yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Document Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($application->documents as $document)
                                        <tr>
                                            <td>{{ $document->name }}</td>
                                            <td>{{ $document->type }}</td>
                                            <td>
                                                @if($document->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($document->status == 'verified')
                                                    <span class="badge bg-success">Verified</span>
                                                @elseif($document->status == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('application-documents.show', $document->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($application->status == 'pending' && $document->status != 'verified')
                                                    <form action="{{ route('application-documents.destroy', $document->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this document?');">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h4>Application Responses</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <h5>Why are you interested in this program?</h5>
                                <p>{{ $application->motivation ?? 'No response provided' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h5>Relevant Experience</h5>
                                <p>{{ $application->experience ?? 'No response provided' }}</p>
                            </div>
                            
                            <div>
                                <h5>Goals for the Program</h5>
                                <p>{{ $application->goals ?? 'No response provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($application->status == 'pending')
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    @if($application->documents->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> Please upload required documents to complete your application.
                        </div>
                    @endif
                    <form action="{{ route('applications.destroy', $application->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to withdraw this application? This action cannot be undone.');">
                            Withdraw Application
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('application-documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="application_id" value="{{ $application->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Document Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Document Type *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select document type</option>
                            <option value="resume">Resume/CV</option>
                            <option value="id_proof">ID Proof/Passport</option>
                            <option value="academic">Academic Records</option>
                            <option value="recommendation">Recommendation Letter</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File *</label>
                        <input class="form-control" type="file" id="file" name="file" required>
                        <small class="text-muted">Accepted formats: PDF, JPG, PNG. Max size: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection