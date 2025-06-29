@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Intercultural Programs</h1>
            <p class="lead">Explore our diverse range of cultural exchange programs around the world</p>
        </div>
        <div class="col-md-4 text-end">
            <form action="{{ route('programs.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search programs..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group">
                <a href="{{ route('programs.index') }}" class="btn {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                <a href="{{ route('programs.index', ['category' => 'academic']) }}" class="btn {{ request('category') == 'academic' ? 'btn-primary' : 'btn-outline-primary' }}">Academic</a>
                <a href="{{ route('programs.index', ['category' => 'volunteer']) }}" class="btn {{ request('category') == 'volunteer' ? 'btn-primary' : 'btn-outline-primary' }}">Volunteer</a>
                <a href="{{ route('programs.index', ['category' => 'internship']) }}" class="btn {{ request('category') == 'internship' ? 'btn-primary' : 'btn-outline-primary' }}">Internship</a>
                <a href="{{ route('programs.index', ['category' => 'language']) }}" class="btn {{ request('category') == 'language' ? 'btn-primary' : 'btn-outline-primary' }}">Language</a>
            </div>
        </div>
    </div>

    @if($programs->isEmpty())
        <div class="alert alert-info">
            No programs found. Please try a different search or category.
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($programs as $program)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge bg-{{ $program->is_active ? 'success' : 'secondary' }} mb-2">
                                {{ $program->is_active ? 'Active' : 'Inactive' }}
                            </div>
                            <h5 class="card-title">{{ $program->name }}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{ $program->country }}</h6>
                            <div class="mb-2">
                                <span class="badge bg-info">{{ $program->category }}</span>
                            </div>
                            <p class="card-text">{{ Str::limit($program->description, 150) }}</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="{{ route('programs.show', $program->id) }}" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $programs->links() }}
        </div>
    @endif
</div>
@endsection