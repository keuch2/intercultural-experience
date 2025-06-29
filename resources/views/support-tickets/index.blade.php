@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1>Support Center</h1>
            <p class="lead">Get help with your applications, programs, or general inquiries</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('support-tickets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Create New Ticket
            </a>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-book fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Knowledge Base</h5>
                    <p class="card-text">Find answers to commonly asked questions in our knowledge base.</p>
                    <a href="#" class="btn btn-outline-primary">Browse Articles</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-comments fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Live Chat</h5>
                    <p class="card-text">Chat with our support team during business hours for quick help.</p>
                    <a href="#" class="btn btn-outline-primary">Start Chat</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-envelope fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Email Support</h5>
                    <p class="card-text">Send us an email and we'll get back to you within 24 hours.</p>
                    <a href="mailto:support@example.com" class="btn btn-outline-primary">Email Us</a>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mb-4">Your Support Tickets</h2>
    
    @if($tickets->isEmpty())
        <div class="alert alert-info">
            <p>You haven't created any support tickets yet.</p>
            <a href="{{ route('support-tickets.create') }}" class="btn btn-primary mt-2">Create Your First Ticket</a>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->id }}</td>
                                    <td>{{ $ticket->subject }}</td>
                                    <td>
                                        @if($ticket->status == 'open')
                                            <span class="badge bg-success">Open</span>
                                        @elseif($ticket->status == 'in_progress')
                                            <span class="badge bg-info">In Progress</span>
                                        @elseif($ticket->status == 'closed')
                                            <span class="badge bg-secondary">Closed</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                    <td>{{ $ticket->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('support-tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">
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
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection