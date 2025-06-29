@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('support-tickets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Support Tickets
        </a>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Ticket #{{ $ticket->id }}: {{ $ticket->subject }}</h3>
                <span class="badge bg-{{ 
                    $ticket->status == 'open' ? 'success' : 
                    ($ticket->status == 'in_progress' ? 'info' : 'secondary') 
                }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="rounded-circle" width="60" height="60" alt="User Avatar">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px">
                                    <span class="fs-4">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                                <span class="text-muted small">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="mb-3">
                                @if(isset($ticket->category))
                                    <span class="badge bg-light text-dark me-1">{{ ucfirst($ticket->category) }}</span>
                                @endif
                                @if(isset($ticket->priority))
                                    <span class="badge bg-{{ 
                                        $ticket->priority == 'high' ? 'danger' : 
                                        ($ticket->priority == 'medium' ? 'warning' : 'info') 
                                    }} me-1">
                                        {{ ucfirst($ticket->priority) }} Priority
                                    </span>
                                @endif
                            </div>
                            <div class="ticket-message">
                                <p>{{ $ticket->message }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Ticket Responses -->
                    @if(isset($ticket->responses) && $ticket->responses->count() > 0)
                        <h5 class="mb-3">Responses</h5>
                        @foreach($ticket->responses as $response)
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    @if($response->user->avatar)
                                        <img src="{{ asset('storage/' . $response->user->avatar) }}" class="rounded-circle" width="50" height="50" alt="User Avatar">
                                    @else
                                        <div class="bg-{{ $response->user->role == 'admin' ? 'danger' : 'primary' }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px">
                                            <span class="fs-5">{{ substr($response->user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1">
                                            {{ $response->user->name }}
                                            @if($response->user->role == 'admin')
                                                <span class="badge bg-danger ms-2">Staff</span>
                                            @endif
                                        </h6>
                                        <span class="text-muted small">{{ $response->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="response-message">
                                        <p>{{ $response->message }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No responses yet. Our support team will respond shortly.
                        </div>
                    @endif
                    
                    <!-- Reply Form -->
                    @if($ticket->status != 'closed')
                        <div class="mt-4">
                            <h5>Add a Reply</h5>
                            <form action="{{ route('support-tickets.responses.store', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control @error('response') is-invalid @enderror" 
                                              name="response" 
                                              rows="4" 
                                              placeholder="Type your response here..." 
                                              required>{{ old('response') }}</textarea>
                                    @error('response')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-reply me-1"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Ticket Details</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Status
                                    <span class="badge bg-{{ 
                                        $ticket->status == 'open' ? 'success' : 
                                        ($ticket->status == 'in_progress' ? 'info' : 'secondary') 
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Ticket ID
                                    <span>#{{ $ticket->id }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Created
                                    <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Last Updated
                                    <span>{{ $ticket->updated_at->format('M d, Y') }}</span>
                                </li>
                                @if(isset($ticket->category))
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Category
                                        <span>{{ ucfirst($ticket->category) }}</span>
                                    </li>
                                @endif
                                @if(isset($ticket->priority))
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Priority
                                        <span class="badge bg-{{ 
                                            $ticket->priority == 'high' ? 'danger' : 
                                            ($ticket->priority == 'medium' ? 'warning' : 'info') 
                                        }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </li>
                                @endif
                            </ul>
                            
                            @if($ticket->status != 'closed')
                                <div class="mt-3">
                                    <form action="{{ route('support-tickets.close', $ticket->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary w-100" onclick="return confirm('Are you sure you want to close this ticket?');">
                                            <i class="fas fa-check-circle me-1"></i> Close Ticket
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="mt-3">
                                    <form action="{{ route('support-tickets.reopen', $ticket->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-redo-alt me-1"></i> Reopen Ticket
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Related Articles</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none">
                                        <i class="fas fa-file-alt me-2 text-primary"></i> How to Complete Your Application
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none">
                                        <i class="fas fa-file-alt me-2 text-primary"></i> Document Upload Guidelines
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none">
                                        <i class="fas fa-file-alt me-2 text-primary"></i> Frequently Asked Questions
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection